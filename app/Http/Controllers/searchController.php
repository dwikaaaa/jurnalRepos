<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Komen;
use App\Models\Konfigurasi;
use App\Models\Konten;
use App\Models\Suka;
use App\Models\View;
use Illuminate\Http\Request;

class searchController extends Controller
{
    public function cari(Request $request)
    {
        if (empty($request->cari)) {
            return response()->json([]);
        }

        $getKonten = Konten::with(['kategori', 'user', 'topiks'])
            ->where(function ($query) use ($request) {
                $query->where('judul', 'like', '%' . $request->cari . '%')
                    ->orWhere('keterangan', 'like', '%' . $request->cari . '%')
                    ->orWhereHas('user', function ($subQuery) use ($request) {
                        $subQuery->where('username', 'like', '%' . $request->cari . '%');
                    })
                    ->orWhereHas('kategori', function ($subQuery) use ($request) {
                        $subQuery->where('nama_kategori', 'like', '%' . $request->cari . '%');
                    })
                    ->orWhereHas('topiks', function ($subQuery) use ($request) {
                        $subQuery->where('nama_topik', 'like', '%' . $request->cari . '%');
                    });
            })->where('id_kategori', '!=', 4)
            ->get();

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
                        return $t->nama_topik;
                    }),
                    "nama" => $konten->user->nama ?? null,
                    "id_user" => $konten->user->id ?? null,
                ];
            })
        ];

        return response()->json($data);
    }

    public function kategori(Request $request)
    {
        $getKonten = Konten::with(['kategori', 'user', 'topiks'])
            ->whereHas('kategori', function ($query) use ($request) {
                $query->where('nama_kategori', $request->kategori);
            })
            ->orWhereHas('topiks', function ($subQuery) use ($request) {
                $subQuery->where('nama_topik', 'like', '%' . $request->kategori . '%');
            })
            ->where('id_kategori', '!=', 4)
            ->get();
                            
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
                        return $t->nama_topik;
                    }),
                    "nama" => $konten->user->nama ?? null,
                    "id_user" => $konten->user->id ?? null,
                ];
            })
        ];

        return response()->json($data);
    }
}
