<?php

namespace App\Http\Controllers;

use App\Models\Komen;
use App\Models\Konten;
use App\Models\Suka;
use App\Models\User;
use App\Models\View;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class profilController extends Controller
{
    public function index(Request $request)
    {
        try {
            $users = $request->user();

            if (!$users) {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            $getUser = User::where('username', $users->username)->with('anggota')->get();

            // ✅ PERBAIKAN: Inisialisasi $getKonten dengan array kosong
            $getKonten = [];

            if ($users->level == 'kontributor') {
                $getKonten = Konten::where('id_user', $users->id)->with(['kategori', 'user'])->orderBy('id', 'desc')->get();
            }

            $dataUser = $getUser->map(function ($users) {
                return [
                    'id' => $users->id,
                    'username' => $users->username,
                    'nama' => $users->nama,
                    'level' => $users->level,
                    'divisi' => $users->anggota->divisi ?? null,
                    'jabatan' => $users->anggota->jabatan ?? null,
                    'status' => $users->anggota->status ?? null,
                    'foto' => $users->foto, // ✅ UBAH: 'upload/profil/' bukan 'storage/'
                    'nama_foto' => $users->nama_foto
                ];
            });

            $totalLike = Suka::selectRaw('COUNT(id) as jumlah_like')->where('id_user', $users->id)->first();
            $totalview = View::selectRaw('COUNT(id) as jumlah_view')->where('id_user', $users->id)->first();
            $totalKonten = Konten::selectRaw('COUNT(id) as jumlah_konten')->where('id_user', $users->id)->first();

            $dataStatistik = [
                'views' => $totalview->jumlah_view,
                'likes' => $totalLike->jumlah_like,
                'kontens' => $totalKonten->jumlah_konten,
            ];

            // ✅ PERBAIKAN: Gunakan collect() untuk handle array
            $dataKonten = collect($getKonten)->map(function ($konten) {
                $jumlahLike = Suka::selectRaw('COUNT(id) as jumlah_like')->where('id_konten', $konten->id)->first();
                $jumlahkomen = Komen::selectRaw('COUNT(id) as jumlah_komen')->where('id_konten', $konten->id)->first();
                $jumlahview = View::selectRaw('COUNT(id) as jumlah_view')->where('id_konten', $konten->id)->first();
                return [
                    "id" => $konten->id ?? null,
                    "judul" => $konten->judul,
                    "keterangan" => $konten->keterangan,
                    "foto" => $konten->foto,
                    "nama_foto" => $konten->nama_foto,
                    "slug" => $konten->slug,
                    "tanggal" => $konten->tanggal,
                    "views" => $jumlahview->jumlah_view,
                    "likes" => $jumlahLike->jumlah_like,
                    "komentar" => $jumlahkomen->jumlah_komen,
                    "nama_kategori" => $konten->kategori->nama_kategori,
                    "nama" => $konten->user->nama ?? null,
                ];
            });

            return response()->json([
                'data user' => $dataUser,
                'data statistik' => $dataStatistik,
                'data konten' => $dataKonten
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateProfil(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            $users = User::where('username', $user->username)->first();

            // Handle file upload
            $foto = null;
            $nama_foto = null;

            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $nama_foto = time() . '_' . $file->getClientOriginalName();

                // ✅ UBAH: Simpan di public/upload/profil bukan storage
                $uploadPath = public_path('upload/profil');

                // Buat folder jika belum ada
                if (!File::exists($uploadPath)) {
                    File::makeDirectory($uploadPath, 0755, true);
                }

                // Hapus foto lama jika ada
                if ($users->foto && File::exists(public_path('upload/profil/' . $users->nama_foto))) {
                    File::delete(public_path('upload/profil/' . $users->nama_foto));
                }

                // Pindahkan file ke folder baru
                $file->move($uploadPath, $nama_foto);
                $foto =  url('upload/profil/' . $nama_foto); // Simpan hanya nama file, bukan full path
            }

            // Check username availability
            $usernameExists = User::where('username', $request->username)
                ->where('id', '!=', $user->id)
                ->first();

            if ($usernameExists) {
                return response()->json([
                    'message' => 'username sudah ada'
                ]);
            }

            // Update user data
            $updateData = [
                'username' => $request->username,
                'nama' => $request->nama,
            ];

            // Add photo data if file was uploaded
            if ($foto) {
                $updateData['foto'] = $foto;
                $updateData['nama_foto'] = $nama_foto;
            }

            $users->update($updateData);

            return response()->json([
                'message' => 'data berhasil diubah',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function destProfil(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->level != 'publik') {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            $users = User::where('id', $user->id)->first();
            if ($users) {
                Konten::where('id_user', $user->id)->delete();
                Komen::where('id_user', $user->id)->delete();
                Suka::where('id_user', $user->id)->delete();
                View::where('id_user', $user->id)->delete();

                $path = public_path('upload/profil/' . basename($users->nama_foto));

                if (File::exists($path)) {
                    File::delete($path);
                }

                $users->delete();

                return response()->json([
                    'message' => 'data berhasil dihapus!'
                ]);
            } else {
                return response()->json([
                    'message' => 'data gagal dihapus!'
                ]);
            }

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function historiLike(Request $request)
    {
        try {
            $users = $request->user();

            if (!$users) {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            $getKontenLiked = Konten::with('user', 'kategori', 'user')
                ->join('sukas', 'sukas.id_konten', '=', 'kontens.id')
                ->where('sukas.id_user', $users->id)
                ->select('kontens.*')
                ->get();

            $kontenLiked = $getKontenLiked->map(function ($liked) {
                $jumlahLike = Suka::selectRaw('COUNT(id) as jumlah_like')->where('id_konten', $liked->id)->first();
                $jumlahkomen = Komen::selectRaw('COUNT(id) as jumlah_komen')->where('id_konten', $liked->id)->first();
                $jumlahview = View::selectRaw('COUNT(id) as jumlah_view')->where('id_konten', $liked->id)->first();
                return [
                    "id" => $liked->id ?? null,
                    "judul" => $liked->judul,
                    "keterangan" => $liked->keterangan,
                    "foto" => $liked->foto,
                    "nama_foto" => $liked->nama_foto,
                    "slug" => $liked->slug,
                    "tanggal" => $liked->tanggal,
                    "views" => $jumlahview->jumlah_view,
                    "likes" => $jumlahLike->jumlah_like,
                    "komentar" => $jumlahkomen->jumlah_komen,
                    "nama_kategori" => $liked->kategori->nama_kategori,
                    "id_kategori" => $liked->kategori->id,
                    'topik' => $liked->topiks->map(function ($t) {
                        return [
                            'nama_topik' => $t->nama_topik,
                            'id_topik' => $t->id,
                        ];
                    }),
                    "nama" => $liked->user->nama ?? null,
                    "id_user" => $liked->user->id ?? null,
                ];
            });

            return response()->json($kontenLiked);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function historiKomen(Request $request)
    {
        try {
            $users = $request->user();

            if (!$users) {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            $getKomen = Komen::with('konten')->where('id_user', $users->id)->get();

            $komen = $getKomen->map(function ($kmn) {
                return [
                    'id_konten' => $kmn->konten->id,
                    'judul_konten' => $kmn->konten->judul,
                    'slug_konten' => $kmn->konten->slug,
                    'komen' => $kmn->komen,
                    'user' => $kmn->userKomen->nama,
                    'tanggal' => $kmn->tanggal
                ];
            });

            return response()->json($komen);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }
}
