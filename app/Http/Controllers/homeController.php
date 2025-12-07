<?php

namespace App\Http\Controllers;

use App\Models\Carousel;
use App\Models\Kategori;
use App\Models\Komen;
use App\Models\Konfigurasi;
use App\Models\Konten;
use App\Models\Suka;
use App\Models\Topik;
use App\Models\View;
use Carbon\Carbon;
use Illuminate\Http\Request;

class homeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $getKonten = Konten::with('user', 'kategori', 'topiks')->orderBy('id', 'desc')->where('id_kategori', '!=', 4)->get();
        $getKontenGrouped = Konten::with(['user', 'kategori', 'topiks'])->where('id_kategori', '!=', 4)->orderBy('id', 'desc')->get()->groupBy('id_kategori');
        $trendingKonten = Konten::with('user', 'kategori', 'topiks')
            ->where('tanggal', '>=', Carbon::now('Asia/Jakarta')->subDays(2))
            ->where('id_kategori', '!=', 4)
            ->selectRaw("
                kontens.*,
                (SELECT COUNT(*) FROM sukas WHERE sukas.id_konten = kontens.id) AS total_like,
                (SELECT COUNT(*) FROM komens WHERE komens.id_konten = kontens.id) AS total_komen,
                (SELECT COUNT(*) FROM views WHERE views.id_konten = kontens.id) AS total_view
            ")
            ->orderByRaw('(total_like + total_komen + total_view) DESC')->orderBy('id', 'desc')
            ->get();
        $getCarousel = Carousel::all();
        $getTopik = Topik::where('id_kategori', '!=', 4)->get();

        $data = [
            'konten' => $getKonten->map(function ($konten) {
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
                    "id_kategori" => $konten->kategori->id,
                    'topik' => $konten->topiks->map(function ($t) {
                        return [
                            'nama_topik' => $t->nama_topik,
                            'id_topik' => $t->id,
                        ];
                    }),
                    "nama" => $konten->user->nama ?? null,
                    "id_user" => $konten->user->id ?? null,
                ];
            }),
            'trending' => $trendingKonten->map(function ($konten) {
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
                    "id_kategori" => $konten->kategori->id,
                    'topik' => $konten->topiks->map(function ($t) {
                        return [
                            'nama_topik' => $t->nama_topik,
                            'id_topik' => $t->id,
                        ];
                    }),
                    "nama" => $konten->user->nama ?? null,
                    "id_user" => $konten->user->id ?? null,
                ];
            }),
            'kontenGrouped' => $getKontenGrouped->map(function ($group, $kategori) {
                $namaKategori = $group->first()->kategori;
                return [
                    'nama_kategori' => $namaKategori->nama_kategori,
                    'id_kategori' => $namaKategori->id,
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
                            "id_kategori" => $konten->kategori->id,
                            'topik' => $konten->topiks->map(function ($t) {
                                return [
                                    'nama_topik' => $t->nama_topik,
                                    'id_topik' => $t->id,
                                ];
                            }),
                            "nama" => $konten->user->nama ?? null,
                            "id_user" => $konten->user->id ?? null,
                        ];
                    })
                ];
            }),
            'carousel' => $getCarousel->map(function ($carousel) {
                return [
                    'id' => $carousel->id,
                    'judul' => $carousel->judul,
                    'foto' => $carousel->foto,
                    'nama_foto' => $carousel->nama_foto
                ];
            }),
            'topikList' => $getTopik->map(function ($tpk) {
                return [
                    'id' => $tpk->id,
                    'nama_topik' => $tpk->nama_topik,
                ];
            })
        ];

        return response()->json($data);
    }

    public function navbar(Request $request)
    {
        $getKategori = Kategori::with('topikKategori')->where('id', '!=', 4)->get();

        $getKonfigurasi = Konfigurasi::first();

        $getKonten = Konten::with('user', 'kategori', 'topiks')->orderBy('id', 'desc')->where('id_kategori', '!=', 4)->get();

        

        $data = [
            'kategoriGrouped' => $getKategori->map(function ($kategori) {
                return [
                    'id_kategori' => $kategori->id,
                    'nama_kategori' => $kategori->nama_kategori ?? null,
                    'topik' => $kategori->topikKategori->map(function ($topik) {
                        return [
                            "id" => $topik->id,
                            "nama_topik" => $topik->nama_topik,
                        ];
                    })
                ];
            }),
            'konfigurasi' => [
                'judul_website' => $getKonfigurasi->judul_website,
                'profil_website' => $getKonfigurasi->profil_website,
                'instagram' => $getKonfigurasi->instagram,
                'facebook' => $getKonfigurasi->facebook,
                'email' => $getKonfigurasi->email,
                'alamat' => $getKonfigurasi->alamat,
                'no_wa' => $getKonfigurasi->no_wa,
            ],
            'konten' => $getKonten->map(function ($konten) {
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
                    "id_kategori" => $konten->kategori->id,
                    'topik' => $konten->topiks->map(function ($t) {
                        return [
                            'nama_topik' => $t->nama_topik,
                            'id_topik' => $t->id,
                        ];
                    }),
                    "nama" => $konten->user->nama ?? null,
                    "id_user" => $konten->user->id ?? null,
                ];
            }),
        ];

        return response()->json($data);
    }

    public function footer()
    {
        $getKonfigurasi = Konfigurasi::first();

        return response()->json([
            'judul_website' => $getKonfigurasi->judul_website,
            'profil_website' => $getKonfigurasi->profil_website,
            'instagram' => $getKonfigurasi->instagram,
            'facebook' => $getKonfigurasi->facebook,
            'email' => $getKonfigurasi->email,
            'alamat' => $getKonfigurasi->alamat,
            'no_wa' => $getKonfigurasi->no_wa,
        ]);
    }
}
