<?php

namespace App\Http\Controllers;

use App\Models\Anggota_jurnal;
use App\Models\Komen;
use App\Models\Konten;
use App\Models\Konten_topik;
use App\Models\Suka;
use App\Models\User;
use App\Models\View;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class userController extends Controller
{
    public function index(Request $request)
    {
        try {
            $users = $request->user();

            if (!$users || $users->level != 'admin') {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            $getUser = User::with('anggota')->get();
            $getTotal = User::with('anggota')
                ->selectRaw("
                (SELECT COUNT(id) FROM users) AS total_akun,
                (SELECT COUNT(id) FROM users WHERE level = 'admin') AS total_admin,
                (SELECT COUNT(id) FROM users WHERE level = 'kontributor') AS total_kontributor,
                (SELECT COUNT(id) FROM users WHERE level = 'anggota_jurnal') AS total_anggota_jurnal,
                (SELECT COUNT(id) FROM users WHERE level = 'publik') AS total_publik
            ")->first();

            $data = [
                'daftar_user' => $getUser->map(function ($users) {
                    return [
                        'id' => $users->id,
                        'username' => $users->username,
                        'nama' => $users->nama,
                        'level' => $users->level,
                        'divisi' => $users->anggota->divisi ?? null,
                        'jabatan' => $users->anggota->jabatan ?? null,
                        'status' => $users->anggota->status ?? null
                    ];
                }),
                'jumlah' => [
                    'total_akun' => $getTotal->total_akun,
                    'total_admin' => $getTotal->total_admin,
                    'total_kontributor' => $getTotal->total_kontributor,
                    'total_anggota_jurnal' => $getTotal->total_anggota_jurnal,
                    'total_publik' => $getTotal->total_publik,
                ]
            ];

            return response()->json([
                $data
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }
    public function postUser(Request $request)
    {
        try {
            $users = $request->user();

            if ($users->level != 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'unautorized'
                ], 401);
            }

            $validate = $request->validate([
                'username' => 'required',
                'nama' => 'required',
                'password' => 'required',
                'level' => 'required',
                'divisi',
                'jabatan'
            ]);

            $validate['password'] = bcrypt($validate['password']);

            $user = User::where('username', $request->username)->first();

            if ($request->level == 'anggota_jurnal' && !$user) {
                User::create($validate);

                $userJurnal = User::orderBy('id', 'desc')->first();

                Anggota_jurnal::create([
                    'divisi' => $request->divisi,
                    'jabatan' => $request->jabatan,
                    'status' => 'aktiv',
                    'id_user' => $userJurnal->id,
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'data berhasil ditambahkan'
                ]);
            }
            if (!$user) {
                User::create($validate);

                return response()->json([
                    'status' => 'success',
                    'message' => 'data berhasil ditambahkan'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'username telah digunakan'
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function register(Request $request)
    {
        $validate = $request->validate([
            'username' => 'required',
            'nama' => 'required',
            'password' => 'required',
        ]);

        $validate['password'] = bcrypt($validate['password']);

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            User::create([
                'username' => $request->username,
                'nama' => $request->nama,
                'password' => $request->password,
                'level' => 'publik'
            ]);

            return response()->json([
                'message' => 'data berhasil ditambahkan'
            ]);
        } else {
            return response()->json([
                'message' => 'username telah digunakan'
            ]);
        }
    }

    public function destUser(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user || $user->level != 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'unautorized'
                ], 401);
            }

            $id = $request->id;

            $users = User::where('id', $id)->first();

            if ($users) {
                if ($users->level == 'anggota_jurnal') {
                    Anggota_jurnal::where('id_user', $id)->delete();
                }
                $getKonten = Konten::where('id_user', $id)->get();

                foreach ($getKonten as $konten) {
                    Konten_topik::where('konten_id', $konten->id)->delete();

                    $path = public_path('upload/konten/' . basename($konten->nama_foto));

                    if (File::exists($path)) {
                        File::delete($path);
                    }
                }

                Konten::where('id_user', $id)->delete();

                Komen::where('id_user', $id)->delete();
                Suka::where('id_user', $id)->delete();
                View::where('id_user', $id)->delete();

                $pathUser = public_path('upload/profil/' . basename($users->nama_foto));

                if (File::exists($pathUser)) {
                    File::delete($pathUser);
                }

                $users->delete();

                return response()->json([
                    'status' => 'success',
                    'message' => 'data berhasil dihapus!'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'data gagal dihapus!'
                ]);
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);             
        }
    }

    public function userEditPage(Request $request, $id)
    {
        try {
            $users = $request->user();

            if ($users->level != 'admin') {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            $getUser = User::with('anggota')->find($id);

            $data = [
                'id' => $getUser->id,
                'username' => $getUser->username,
                'nama' => $getUser->nama,
                'level' => $getUser->level,
                'divisi' => $getUser->anggota->divisi ?? null,
                'jabatan' => $getUser->anggota->jabatan ?? null,
                'status' => $getUser->anggota->status ?? null
            ];

            return response()->json(
                $data
            );

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function editUser(Request $request)
    {
        try {
            $admin = $request->user();

            if (!$admin || $admin->level != 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Validasi dasar
            $request->validate([
                'id' => 'required|numeric',
                'level' => 'required'
            ]);

            // Ambil user
            $user = User::with('anggota')->find($request->id);

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            // Update level
            $user->level = $request->level;
            $user->save();

            // Jika jadi non anggota_jurnal → hapus tabel anggota_jurnal
            if ($request->level !== 'anggota_jurnal') {
                $user->anggota()->delete();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Data berhasil diubah!'
                ]);
            }

            // Jika level = anggota_jurnal → update atau create data anggota
            $request->validate([
                'divisi' => 'required',
                'jabatan' => 'nullable',
            ]);

            Anggota_jurnal::updateOrCreate(
                ['id_user' => $user->id],
                [
                    'divisi' => $request->divisi,
                    'jabatan' => $request->jabatan,
                    'status' => $request->status,
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil diubah!'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}