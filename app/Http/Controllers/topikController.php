<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Konten;
use App\Models\Konten_topik;
use App\Models\Topik;
use Exception;
use Illuminate\Http\Request;

class topikController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->level != 'admin') {
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

            return response()->json(
                $data
            );

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function postTopik(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->level != 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'unautorized'
                ], 401);
            }

            $validate = $request->validate([
                'nama_topik' => 'required',
                'id_kategori' => 'required'
            ]);

            $topik = Topik::where('nama_topik', $request->nama_topik)->where('id_kategori', $request->id_kategori)->first();

            if (!$topik) {
                Topik::create([
                    'nama_topik' => $request->nama_topik,
                    'id_kategori' => $request->id_kategori
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'data berhasil ditambahkan'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'nama topik telah digunakan'
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function destTopik(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->level != 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'unautorized'
                ], 401);
            }

            $id = $request->idtopik;


            $isTopik = Konten_topik::where('topik_id', $id)->exists();
            if ($isTopik) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'data yang memiliki konten tidak bisa dihapus!'
                ]);
            }
            $topik = Topik::where('id', $id)->first();
            if ($topik) {
                $topik->delete();

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

    public function topikEditPage(Request $request, $id_topik)
    {
        try {
            $users = $request->user();

            if (!$users || $users->level != 'admin') {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            $getTopik = Topik::where('id', $id_topik)->first();

            return response()->json([
                'id_topik' => $getTopik->id,
                'nama_topik' => $getTopik->nama_topik,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateTopik(Request $request)
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
                'nama_topik' => 'required',
                'id_kategori' => 'required',
                'id_topik' => 'required',
            ]);

            $topikUpdate = Topik::where('id', $request->id_topik);
            $topikSekarang = $topikUpdate->first();

            $topik = Topik::where('nama_topik', $request->nama_topik)->where('id_kategori', $request->id_kategori)->first();

            $topikLama = $topik ? $topik->nama_topik : null;

            if (!$topikLama) {
                $topikUpdate->update([
                    'nama_topik' => $request->nama_topik,
                ]);
                return response()->json([
                    'status' => 'success',
                    'message' => 'data berhasil diubah!'
                ]);
            }
            if ($request->nama_topik == $topikSekarang->nama_topik) {
                return response()->json([
                    'message' => ''
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'nama topik telah dipakai!'
                ]);
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
