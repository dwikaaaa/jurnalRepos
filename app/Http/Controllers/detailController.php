<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Komen;
use App\Models\Konfigurasi;
use App\Models\Konten;
use App\Models\Suka;
use App\Models\View;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class detailController extends Controller
{
    public function artikel(Request $request)
    {
        $getKonten = Konten::with(['user', 'kategori', 'topiks'])->where('slug', $request->slug)->where('id_kategori', '!=', 4)->first();
        $getKontenTerkait = Konten::with(['user', 'kategori', 'topiks'])->where('id_kategori', $getKonten->id_kategori)->where('id_kategori', '!=', 4)->orderBy('id', 'desc')->get();

        $kontenTerkait = $getKontenTerkait->map(function ($terkait) {
            $jumlahLike = Suka::selectRaw('COUNT(id) as jumlah_like')->where('id_konten', $terkait->id)->first();
            $jumlahkomen = Suka::selectRaw('COUNT(id) as jumlah_komen')->where('id_konten', $terkait->id)->first();
            $jumlahview = View::selectRaw('COUNT(id) as jumlah_view')->where('id_konten', $terkait->id)->first();
            return [
                "id" => $terkait->id ?? null,
                "judul" => $terkait->judul,
                "keterangan" => $terkait->keterangan,
                "foto" => $terkait->foto,
                "nama_foto" => $terkait->nama_foto,
                "slug" => $terkait->slug,
                "tanggal" => $terkait->tanggal,
                "views" => $jumlahview->jumlah_view,
                "likes" => $jumlahLike->jumlah_like,
                "komentar" => $jumlahkomen->jumlah_komen,
                "nama_kategori" => $terkait->kategori->nama_kategori,
                "id_kategori" => $terkait->kategori->id,
                'topik' => $terkait->topiks->map(function ($t) {
                    return $t->nama_topik;
                }),
                "nama" => $terkait->user->nama ?? null,
                "id_user" => $terkait->user->id ?? null,
            ];
        });

        if ($getKonten) {
            $getKomen = Komen::with(relations: 'userKomen')->where('id_konten', $getKonten->id)->get();

            $jumlahLike = Suka::selectRaw('COUNT(id) as jumlah_like')->where('id_konten', $getKonten->id)->first();
            $jumlahkomen = Suka::selectRaw('COUNT(id) as jumlah_komen')->where('id_konten', $getKonten->id)->first();
            $jumlahview = View::selectRaw('COUNT(id) as jumlah_view')->where('id_konten', $getKonten->id)->first();

            return response()->json([
                'detail' => [
                    "id" => $getKonten->id ?? null,
                    "judul" => $getKonten->judul,
                    "keterangan" => $getKonten->keterangan,
                    "foto" => $getKonten->foto,
                    "nama_foto" => $getKonten->nama_foto,
                    "slug" => $getKonten->slug,
                    "tanggal" => $getKonten->tanggal,
                    "views" => $jumlahview->jumlah_view,
                    "likes" => $jumlahLike->jumlah_like,
                    "komentar" => $jumlahkomen->jumlah_komen,
                    "nama_kategori" => $getKonten->kategori->nama_kategori,
                    "id_kategori" => $getKonten->kategori->id,
                    'topik' => $getKonten->topiks->map(function ($t) {
                        return [
                            'nama_topik' => $t->nama_topik,
                            'id_topik' => $t->id
                        ];
                    }),
                    "nama" => $getKonten->user->nama ?? null,
                    "id_user" => $getKonten->user->id ?? null,
                    'daftar komentar' => $getKomen->map(function ($komen) {
                        return [
                            'komen' => $komen->komen,
                            'user' => $komen->userKomen->nama,
                            'tanggal' => $komen->tanggal
                        ];
                    })
                ],
                'konten_terkait' => $kontenTerkait
            ]);
        }
    }

    public function view(Request $request)
    {
        $user = $request->user();

        if ($user->level == 'anggota_jurnal') {
            $getKonten = Konten::with(['user', 'kategori'])->where('slug', $request->slug)->first();
        }

        $getKonten = Konten::with(['user', 'kategori'])->where('slug', $request->slug)->where('id_kategori', '!=', 4)->first();

        if ($getKonten) {
            $getKomen = Komen::with('userKomen')->where('id_konten', $getKonten->id)->get();

            if ($user) {
                $isView = View::where('id_user', $user->id)->where('id_konten', $getKonten->id)->exists();

                if (!$isView) {

                    View::create([
                        'id_user' => $user->id,
                        'id_konten' => $getKonten->id
                    ]);
                }
            }

            $jumlahLike = Suka::selectRaw('COUNT(id) as jumlah_like')->where('id_konten', $getKonten->id)->first();
            $jumlahkomen = Komen::selectRaw('COUNT(id) as jumlah_komen')->where('id_konten', $getKonten->id)->first();
            $jumlahview = View::selectRaw('COUNT(id) as jumlah_view')->where('id_konten', $getKonten->id)->first();

            $getKontenTerkait = Konten::with(['user', 'kategori', 'topiks'])->where('id_kategori', $getKonten->id_kategori)->orderBy('id', 'desc')->get();

            $isLike = Suka::where('id_user', $user->id)->where('id_konten', $getKonten->id)->exists();

            $kontenTerkait = $getKontenTerkait->map(function ($terkait) {
                $likesKontens = Suka::selectRaw('COUNT(id) as jumlah_like')->where('id_konten', $terkait->id)->first();
                $komensKontens = Suka::selectRaw('COUNT(id) as jumlah_komen')->where('id_konten', $terkait->id)->first();
                $viewsKontens = View::selectRaw('COUNT(id) as jumlah_view')->where('id_konten', $terkait->id)->first();
                return [
                    "id" => $terkait->id ?? null,
                    "judul" => $terkait->judul,
                    "keterangan" => $terkait->keterangan,
                    "foto" => $terkait->foto,
                    "nama_foto" => $terkait->nama_foto,
                    "slug" => $terkait->slug,
                    "tanggal" => $terkait->tanggal,
                    "views" => $viewsKontens->jumlah_view,
                    "likes" => $likesKontens->jumlah_like,
                    "komentar" => $komensKontens->jumlah_komen,
                    "nama_kategori" => $terkait->kategori->nama_kategori,
                    "id_kategori" => $terkait->kategori->id,
                    'topik' => $terkait->topiks->map(function ($t) {
                        return [
                            'nama_topik' => $t->nama_topik,
                            'id_topik' => $t->id
                        ];
                    }),
                    "nama" => $terkait->user->nama ?? null,
                    "id_user" => $terkait->user->id ?? null,
                ];
            });

            return response()->json([
                'detail' => [
                    "id" => $getKonten->id ?? null,
                    "judul" => $getKonten->judul,
                    "keterangan" => $getKonten->keterangan,
                    "foto" => $getKonten->foto,
                    "nama_foto" => $getKonten->nama_foto,
                    "slug" => $getKonten->slug,
                    "tanggal" => $getKonten->tanggal,
                    "views" => $jumlahview->jumlah_view,
                    "likes" => $jumlahLike->jumlah_like,
                    "komentar" => $jumlahkomen->jumlah_komen,
                    "nama_kategori" => $getKonten->kategori->nama_kategori,
                    "id_kategori" => $getKonten->kategori->id,
                    'topik' => $getKonten->topiks->map(function ($t) {
                        return [
                            'nama_topik' => $t->nama_topik,
                            'id_topik' => $t->id
                        ];
                    }),
                    "nama" => $getKonten->user->nama ?? null,
                    "id_user" => $getKonten->user->id ?? null,
                    'daftar komentar' => $getKomen->map(function ($komen) {
                        return [
                            'komen' => $komen->komen,
                            'user' => $komen->userKomen->nama,
                            'tanggal' => $komen->tanggal
                        ];
                    })
                ],
                'konten_terkait' => $kontenTerkait,
                'isLiked'  => $isLike
            ]);
        }
    }

    public function komen(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            $request->validate([
                'komen' => 'required',
                'id_konten' => 'required',
            ]);

            $getKonten = Konten::with(['user', 'kategori'])->where('id', $request->id_konten)->first();

            Komen::create([
                'komen' => $request->komen,
                'id_user' => $user->id,
                'id_konten' => $request->id_konten,
                'tanggal' => Carbon::now('Asia/Jakarta'),
            ]);


            return response()->json([
                'message' => 'data berhasil ditambahkan!'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function selectedKomen(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            $getKomen = Komen::with('userkomen')->where('id_user', $user->id)->where('id', $request->id_komen)->first();

            return response()->json([
                'id' => $getKomen->id,
                'komen' => $getKomen->komen,
                'user' => $getKomen->userKomen->nama,
                'user_id' => $getKomen->userKomen->id,
                'tanggal' => $getKomen->tanggal
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function editKomen(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            $request->validate([
                'komen' => 'required',
                'id_konten' => 'required',
            ]);

            $getKomen = Komen::where('id_user', $user->id)->where('id', $request->id_komen)->first();

            $getKomen->update([
                'komen' => $request->komen,
            ]);

            return response()->json([
                'message' => 'data berhasil diubah!'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function destKomen(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            $getKomen = Komen::where('id_user', $user->id)->where('id', $request->id_komen)->first();

            $getKomen->delete();

            return response()->json([
                'message' => 'data berhasil dihapus!'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function like(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            $getKonten = Konten::with(['user', 'kategori'])->where('id', $request->id_konten)->first();

            if ($user) {
                $isLike = Suka::where('id_user', $user->id)->where('id_konten', $request->id_konten)->first();

                if (!$isLike) {
                    $request->validate([
                        'id_konten' => 'required',
                    ]);

                    $getKonten = Konten::where('id', $request->id_konten)->first();

                    Suka::create([
                        'id_user' => $user->id,
                        'id_kategori' => $getKonten->id_kategori,
                        'id_konten' => $request->id_konten,
                        'tanggal' => Carbon::now('Asia/Jakarta'),
                    ]);

                } else {
                    $suka = Suka::where('id_user', $user->id)->where('id_konten', $request->id_konten)->delete();

                    if (!$suka) {
                        return response()->json([
                            'message' => 'gagal unlike'
                        ]);
                    }
                    return response()->json([
                        'message' => 'unliked'
                    ]);
                }
            }

            return response()->json([
                'message' => 'liked'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }
}
