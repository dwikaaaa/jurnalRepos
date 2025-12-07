<?php

namespace App\Http\Controllers;

use App\Models\Carousel;
use App\Models\Kategori;
use App\Models\Komen;
use App\Models\Konten;
use App\Models\Suka;
use App\Models\User;
use App\Models\View;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class dashboardController extends Controller
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
                $jumlah_user = User::selectRaw('COUNT(id) as jumlah_user')->first();
                $jumlah_konten = Konten::selectRaw('COUNT(id) as jumlah_konten')->first();
                $jumlah_kategori = Kategori::selectRaw('COUNT(id) as jumlah_kategori')->first();
                $jumlah_carousel = Carousel::selectRaw('COUNT(id) as jumlah_carousel')->first();

                $kontenGrouped = Konten::selectRaw("
                    kategoris.nama_kategori,
                    kontens.id_kategori,
                    COUNT(kontens.id) AS jumlah_konten
                ")
                    ->join('kategoris', 'kategoris.id', '=', 'kontens.id_kategori')
                    ->groupBy('kontens.id_kategori', 'kategoris.nama_kategori')
                    ->get();

                $userGrouped = User::from('users as u')
                    ->selectRaw("
                        u.level,
                        (SELECT COUNT(*) FROM users uu WHERE uu.level = u.level) as jumlah_users
                    ")
                    ->groupBy('u.level')
                    ->get();

                return response()->json([
                    'jumlah_user' => $jumlah_user->jumlah_user,
                    'jumlah_konten' => $jumlah_konten->jumlah_konten,
                    'jumlah_kategori' => $jumlah_kategori->jumlah_kategori,
                    'jumlah_carousel' => $jumlah_carousel->jumlah_carousel,
                    'jumlah_konten_kategori' => $kontenGrouped,
                    'jumlah_user_level' => $userGrouped,
                ]);

            } else if ($user->level == 'kontributor') {
                $totalLike = Suka::selectRaw('COUNT(id) as jumlah_like')->where('id_user', $user->id)->first();
                $totalview = View::selectRaw('COUNT(id) as jumlah_view')->where('id_user', $user->id)->first();
                $totalKonten = Konten::selectRaw('COUNT(id) as jumlah_konten')->where('id_user', $user->id)->first();
                $kontenGrouped = Konten::selectRaw("
                        kategoris.nama_kategori,
                        kontens.id_kategori,
                        COUNT(kontens.id) AS jumlah_konten
                    ")
                    ->where('kontens.id_user', $user->id)
                    ->join('kategoris', 'kategoris.id', '=', 'kontens.id_kategori')
                    ->groupBy('kontens.id_kategori', 'kategoris.nama_kategori')
                    ->get();


                $getMostViewed = Konten::with('user', 'kategori', 'topiks')
                    ->where('id_user', $user->id)
                    ->where('id_kategori', '!=', 4)
                    ->selectRaw("
                        kontens.*,
                        (SELECT COUNT(*) FROM views WHERE views.id_konten = kontens.id) AS total_view
                    ")
                    ->orderByRaw('(total_view) DESC')->orderBy('id', 'desc')
                    ->limit(3)
                    ->get();

                $getMostLiked = Konten::with('user', 'kategori', 'topiks')
                    ->where('id_user', $user->id)
                    ->where('id_kategori', '!=', 4)
                    ->selectRaw("
                        kontens.*,
                        (SELECT COUNT(*) FROM sukas WHERE sukas.id_konten = kontens.id) AS total_like
                    ")
                    ->orderByRaw('(total_like) DESC')->orderBy('id', 'desc')
                    ->limit(3)
                    ->get();

                $mostLiked = $getMostLiked->map(function ($like) {
                    $jumlahLike = Suka::selectRaw('COUNT(id) as jumlah_like')->where('id_konten', $like->id)->first();
                    $jumlahkomen = Komen::selectRaw('COUNT(id) as jumlah_komen')->where('id_konten', $like->id)->first();
                    $jumlahview = View::selectRaw('COUNT(id) as jumlah_view')->where('id_konten', $like->id)->first();
                    return [
                        "id" => $like->id ?? null,
                        "judul" => $like->judul,
                        "keterangan" => $like->keterangan,
                        "foto" => $like->foto,
                        "nama_foto" => $like->nama_foto,
                        "slug" => $like->slug,
                        "tanggal" => $like->tanggal,
                        "views" => $jumlahview->jumlah_view,
                        "likes" => $jumlahLike->jumlah_like,
                        "komentar" => $jumlahkomen->jumlah_komen,
                        "nama_kategori" => $like->kategori->nama_kategori,
                        "id_kategori" => $like->kategori->id,
                        'topik' => $like->topiks->map(function ($t) {
                            return [
                                'nama_topik' => $t->nama_topik,
                                'id_topik' => $t->id,
                            ];
                        }),
                        "nama" => $konten->user->nama ?? null,
                        "id_user" => $konten->user->id ?? null,
                    ];
                });

                $mostViewed = $getMostViewed->map(function ($view) {
                    $jumlahLike = Suka::selectRaw('COUNT(id) as jumlah_like')->where('id_konten', $view->id)->first();
                    $jumlahkomen = Komen::selectRaw('COUNT(id) as jumlah_komen')->where('id_konten', $view->id)->first();
                    $jumlahview = View::selectRaw('COUNT(id) as jumlah_view')->where('id_konten', $view->id)->first();
                    return [
                        "id" => $view->id ?? null,
                        "judul" => $view->judul,
                        "keterangan" => $view->keterangan,
                        "foto" => $view->foto,
                        "nama_foto" => $view->nama_foto,
                        "slug" => $view->slug,
                        "tanggal" => $view->tanggal,
                        "views" => $jumlahview->jumlah_view,
                        "likes" => $jumlahLike->jumlah_like,
                        "komentar" => $jumlahkomen->jumlah_komen,
                        "nama_kategori" => $view->kategori->nama_kategori,
                        "id_kategori" => $view->kategori->id,
                        'topik' => $view->topiks->map(function ($t) {
                            return [
                                'nama_topik' => $t->nama_topik,
                                'id_topik' => $t->id,
                            ];
                        }),
                        "nama" => $konten->user->nama ?? null,
                        "id_user" => $konten->user->id ?? null,
                    ];
                });

                return response()->json([
                    'jumlah_like' => $totalLike->jumlah_like,
                    'jumlah_view' => $totalview->jumlah_view,
                    'jumlah_konten' => $totalKonten->jumlah_konten,
                    'jumlah_konten_kategori' => $kontenGrouped,
                    'most_viewed' => $mostViewed,
                    'most_liked' => $mostLiked,
                ]);

            } else {
                return response()->json([]);
            }

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }
}
