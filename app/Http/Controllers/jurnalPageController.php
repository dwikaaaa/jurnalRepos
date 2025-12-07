<?php

namespace App\Http\Controllers;

use App\Models\Anggota_jurnal;
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

class jurnalPageController extends Controller
{
    public function index(Request $request)
    {
        try {
            $users = $request->user();

            if ($users->level != 'anggota_jurnal' && $users->level != 'admin') {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            $getKontenGrouped = Konten::with(['user', 'kategori', 'topik'])->where('id_kategori', 4)->get()->groupBy(function ($item) {
                return $item->topik->nama_topik ?? null;
            });

            $getTopik = Topik::where('id_kategori', 4)->get();

            $data = [
                'kontenGrouped' => $getKontenGrouped->map(function ($group, $topik) {
                    return [
                        'nama_topik' => $topik,
                        "data" => $group->map(function ($konten) {
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
                        })
                    ];
                }),
                'daftar topik' => $getTopik->map(function ($topik) {
                    return [
                        'id' => $topik->id,
                        'nama_topik' => $topik->nama_topik,
                    ];
                })
            ];

            return response()->json($data);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function postJurnal(Request $request)
    {
        try {
            $users = $request->user();

            $pejabat = Anggota_jurnal::with('userJurnal')->where('id_user', $users->id)->first();

            if ($pejabat->jabatan == null) {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            $validate = $request->validate([
                'judul' => 'required',
                'keterangan' => 'required',
                'foto' => 'image|mimes:jpeg,png,jpg|max:2048',
                'id_topik' => 'required',
            ]);

            // judul
            $konten = Konten::where('judul', $request->judul)->first();

            $create = [
                'judul' => $request->judul,
                'keterangan' => $request->keterangan,
                'id_kategori' => 4,
                'tanggal' => Carbon::now('Asia/Jakarta'),
                'id_user' => $users->id,
                'slug' => Str::slug($request->judul),
            ];

            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $namaFile = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('upload/carousel'), $namaFile);

                $create['foto'] = url('upload/konten/' . $namaFile);
                $create['nama_foto'] = $namaFile;
            }

            if (!$konten) {
                $createKonten = Konten::create($create);

                $topik = json_decode($request->id_topik, true);

                if ($topik != null) {
                    foreach ($topik as $id_topik) {
                        Konten_topik::create([
                            'konten_id' => $createKonten->id,
                            'topik_id' => $id_topik
                        ]);
                    }
                }

                return response()->json([
                    'message' => 'data berhasil ditambahkan',
                ]);
            } else {
                return response()->json([
                    'message' => 'judul sudah ada'
                ]);
            }

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }
}
