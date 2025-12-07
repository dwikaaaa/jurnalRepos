<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Komen;
use App\Models\Konten;
use App\Models\Konten_topik;
use App\Models\Suka;
use App\Models\Topik;
use App\Models\View;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Str;

class kontenController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->level != 'admin' && $user->level != 'kontributor') {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            if ($user->level == 'admin') {
                $getKonten = Konten::with(['kategori', 'user', 'topiks'])->orderBy('id', 'desc')->get();
                $getTotal = Konten::selectRaw("
                        (SELECT COUNT(id) FROM kontens) AS total_konten,
                        (SELECT COUNT(id) FROM kontens WHERE id_kategori != 4) AS total_berita,
                        (SELECT COUNT(id) FROM kontens WHERE id_kategori = 4 ) AS total_konten_jurnal
            ")->first();
                $total = [
                    'total_konten' => $getTotal->total_konten,
                    'total_berita' => $getTotal->total_berita,
                    'total_konten_jurnal' => $getTotal->total_konten_jurnal
                ];
            }

            if ($user->level == 'kontributor') {
                $getKonten = Konten::where('id_user', $user->id)->with(['kategori', 'user', 'topiks'])->orderBy('id', 'desc')->get();
                $getTotal = Konten::where('id_user', $user->id)->count();

                $totalLike = Suka::selectRaw('COUNT(id) as jumlah_like')->where('id_user', $user->id)->first();
                $totalview = View::selectRaw('COUNT(id) as jumlah_view')->where('id_user', $user->id)->first();

                $total = [
                    'total_konten' => $getTotal,
                    'total_view' => $totalview->jumlah_view,
                    'total_like' => $totalLike->jumlah_like
                ];
            }

            $getKategori = Kategori::where('id', '!=', 4)->get();

            $kategori = $getKategori->map(function ($ktgr) {
                return [
                    'id' => $ktgr->id,
                    'nama_kategori' => $ktgr->nama_kategori
                ];
            });

            $konten = $getKonten->map(function ($konten) {
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
                    'topik' => $konten->topiks->map(function ($t) {
                        return $t->nama_topik;
                    }),
                    "views" => $jumlahview->jumlah_view,
                    "likes" => $jumlahLike->jumlah_like,
                    "komentar" => $jumlahkomen->jumlah_komen,
                    "nama_kategori" => $konten->kategori->nama_kategori,
                    "nama" => $konten->user->nama ?? null,
                ];
            });

            return response()->json([
                'kategori' => $kategori,
                'total' => $total,
                'konten' => $konten
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function halamanPost(Request $request)
    {
        try {
            $user = $request->user()->load('anggota');

            if ($user->level == 'anggota_jurnal') {
                if ($user->anggota->jabatan == null) {
                    return response()->json([
                        'message' => 'unautorized'
                    ], 401);
                }

                if ($user->anggota->jabatan != null) {
                    $getTopik = Topik::where('id_kategori', 4)->get();

                    $data = $getTopik->map(function ($topik) {
                        return [
                            'id' => $topik->id,
                            'nama_topik' => $topik->nama_topik,
                        ];
                    });

                    return response()->json($data);
                }
            }

            if ($user->level != 'kontributor' && $user->level != "admin") {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            $getTopik = Topik::where('id_kategori', $request->id_kategori)->get();

            $data = $getTopik->map(function ($topik) {
                return [
                    'id' => $topik->id,
                    'nama_topik' => $topik->nama_topik,
                ];
            });

            return response()->json($data);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function halamanEdit(Request $request)
    {
        try {
            $user = $request->user()->load('anggota');
            if ($user->level == 'anggota_jurnal') {
                if ($user->anggota->jabatan == null) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'unautorized'
                    ], 401);
                }

                $getKonten = Konten::with(['kategori', 'topiks'])->where('slug', $request->slug)->first();
                $data = [

                    "id" => $getKonten->id ?? null,
                    "judul" => $getKonten->judul,
                    "keterangan" => $getKonten->keterangan,
                    "foto" => $getKonten->foto,
                    "nama_foto" => $getKonten->nama_foto,
                    "nama_kategori" => $getKonten->kategori->nama_kategori,
                    "id_kategori" => $getKonten->id_kategori,
                    "id_topik" => $getKonten->id_topik,
                    'topik' => $getKonten->topiks->map(function ($t) {
                        return [
                            'nama_topik' => $t->nama_topik,
                            'id_topik' => $t->id,
                        ];
                    }),

                ];

                return response()->json($data);
            }

            if ($user->level != 'kontributor' && $user->level != 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'unautorized'
                ], 401);
            }

            $getKonten = Konten::with(['kategori', 'topiks'])->where('slug', $request->slug)->first();
            $data = [

                "id" => $getKonten->id ?? null,
                "judul" => $getKonten->judul,
                "keterangan" => $getKonten->keterangan,
                "foto" => $getKonten->foto,
                "nama_foto" => $getKonten->nama_foto,
                "nama_kategori" => $getKonten->kategori->nama_kategori,
                "id_kategori" => $getKonten->id_kategori,
                'topik' => $getKonten->topiks->map(function ($t) {
                    return [
                        'nama_topik' => $t->nama_topik,
                        'id_topik' => $t->id,
                    ];
                }),

            ];

            return response()->json($data);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function postKonten(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->level != 'kontributor' && $user->level !== "admin") {
                return response()->json([
                    'status' => 'error',
                    'message' => 'unautorized'
                ], 401);
            }

            $validate = $request->validate([
                'judul' => 'required',
                'keterangan' => 'required',
                'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'id_kategori' => 'required',
            ]);

            // foto
            $file = $request->file('foto');
            $namaFile = time() . '_' . $file->getClientOriginalName();

            // judul
            $konten = Konten::where('judul', $request->judul)->first();

            if (!$konten) {
                $path = $file->move(public_path('upload/konten'), $namaFile);
                $konten = Konten::create([
                    'judul' => $request->judul,
                    'keterangan' => $request->keterangan,
                    'foto' => url('upload/konten/' . $namaFile),
                    'nama_foto' => $namaFile,
                    'id_kategori' => $request->id_kategori,
                    'tanggal' => Carbon::now('Asia/Jakarta'),
                    'id_user' => $user->id,
                    'slug' => Str::slug($request->judul),
                ]);

                $topik = json_decode($request->topik_id, true);

                if ($topik != null) {
                    foreach ($topik as $id_topik) {
                        Konten_topik::create([
                            'konten_id' => $konten->id,
                            'topik_id' => $id_topik
                        ]);
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'data berhasil ditambahkan',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'judul sudah ada'
                ]);
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function destKonten(Request $request)
    {
        try {
            $user = $request->user()->load('anggota');

            if ($user->level != 'kontributor' && $user->level != 'admin' && $user->anggota->jabatan == null) {
                return response()->json([
                    'status' => "error",
                    'message' => 'unautorized'
                ], 401);
            }

            $foto = $request->nama_foto;

            $konten = Konten::where('nama_foto', $foto)->first();
            if ($konten) {
                $path = public_path('upload/konten/' . basename($foto));

                if (File::exists($path)) {
                    File::delete($path);
                }

                $getKomen = Komen::where('id_konten', $konten->id)->delete();
                $getLike = Suka::where('id_konten', $konten->id)->delete();
                $getView = View::where('id_konten', $konten->id)->delete();

                Konten_topik::where('konten_id', $konten->id)->delete();

                $konten->delete();

                return response()->json([
                    'status' => "success",
                    'message' => 'data berhasil dihapus!'
                ]);
            } else {
                return response()->json([
                    'status' => "error",
                    'message' => 'data gagal dihapus!'
                ]);
            }


        } catch (Exception $e) {
            return response()->json([
                'status' => "error",
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateKonten(Request $request)
    {
        try {
            $user = $request->user()->load('anggota');

            if ($user->level != 'kontributor') {
                if (empty($user->anggota) || empty($user->anggota->jabatan)) {
                    return response()->json([
                        'message' => 'unautorized'
                    ], 401);
                }
            }


            $validate = $request->validate([
                'judul' => 'required',
                'keterangan' => 'required',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'id_kategori' => 'required',
            ]);


            $konten = Konten::where('nama_foto', $request->nama_foto)->where('id_user', $user->id)->first();

            $judul = Konten::where('judul', $request->judul)->first();

            $judulExists = Konten::where('judul', $request->judul)
                ->where('id', '!=', $konten->id)
                ->exists();

            if ($judulExists) {
                return response()->json(['message' => 'judul sudah ada'], 400);
            }

            $getTopiks = Konten_topik::with('topixes')->where('konten_id', $konten->id)->get();
            $daftarTopik = $getTopiks->map(function ($t) {
                return $t->topik_id;
            });


            $topikLama = $request->input('topik_id') ;
            if (!is_array($topikLama)) {
                $topikLama = [$topikLama]; // pastikan selalu array
            }

            $isTopikEqual = collect($daftarTopik)->sort()->values()->toArray() === collect($topikLama)->sort()->values()->toArray();

            $updateData = [];

            if ($konten->judul !== $request->judul) {
                $updateData['judul'] = $request->judul;
                $updateData['slug'] = Str::slug($request->judul);
            }

            if ($konten->keterangan !== $request->keterangan) {
                $updateData['keterangan'] = $request->keterangan;
            }

            if ($konten->id_kategori !== $request->id_kategori) {
                $updateData['id_kategori'] = $request->id_kategori;
            }

            if ($isTopikEqual != true) {
                Konten_topik::where('konten_id', $konten->id)->delete();

                if ($topikLama != null) {
                    if (!is_array($topikLama)) {
                        $topikLama = [$topikLama]; // jadikan array meskipun satu item
                    }
                    foreach ($topikLama as $id_topik) {
                        Konten_topik::create([
                            'konten_id' => $konten->id,
                            'topik_id' => $id_topik
                        ]);
                    }
                }

                $updateData = [
                    'judul' => $request->judul,
                    'keterangan' => $request->keterangan,
                    'id_kategori' => $request->id_kategori,
                    'slug' => Str::slug($request->judul),
                ];
            }


            if ($request->hasFile('foto')) {
                $path = public_path('upload/konten/' . $konten->nama_foto);
                if (File::exists($path)) {
                    File::delete($path);
                }

                $file = $request->file('foto');
                $namaFile = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('upload/konten'), $namaFile);

                $updateData['foto'] = url('upload/konten/' . $namaFile);
                $updateData['nama_foto'] = $namaFile;
            }

            $konten->update($updateData);

            if ($konten) {
                return response()->json([
                    'message' => 'data berhasil diubaha!'
                ]);
            } else {
                return response()->json([
                    'message' => 'data gagal diubah!'
                ]);
            }


        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

}