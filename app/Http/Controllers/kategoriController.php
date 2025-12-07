<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Konten;
use App\Models\Topik;
use Exception;
use Illuminate\Http\Request;

class kategoriController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user || $user->level != 'admin') {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            $getKategori = Kategori::all();

            $data = $getKategori->map(function ($kategori) {
                return [
                    'id' => $kategori->id,
                    'nama_kategori' => $kategori->nama_kategori,
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

    public function postKategori(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user || $user->level != 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'unautorized'
                ], 401);
            }

            $validate = $request->validate([
                'nama_kategori' => 'required',
            ]);

            $kategori = Kategori::where('nama_kategori', $request->nama_kategori)->first();

            if (!$kategori) {
                Kategori::create($validate);

                return response()->json([
                    'status' => 'success',
                    'message' => 'data berhasil ditambahkan'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'nama kategori telah digunakan'
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function destKategori(Request $request)
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

            $kategori = Kategori::where('id', $id)->first();
            if ($kategori->id != 4) {
                $isKonten = Konten::where('id_kategori', $id)->exists();
                if ($isKonten) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'data yang memiliki konten tidak bisa dihapus!'
                    ]);
                }

                Topik::where('id_kategori', $id)->delete();
                $kategori->delete();

                return response()->json([
                    'status' => 'success',
                    'message' => 'data berhasil dihapus!'
                ]);

            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'kategori jurnalistik tidak boleh dihapus!'
                ]);
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function kategoriEditPage(Request $request, $id)
    {
        try {
            $users = $request->user();

            if (!$users || $users->level != 'admin') {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            $getKategori = Kategori::find($id);

            return response()->json([
                'id' => $getKategori->id,
                'nama_kategori' => $getKategori->nama_kategori,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateKategori(Request $request)
    {
        try {
            $users = $request->user();

            if (!$users || $users->level != 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'unautorized'
                ], 401);
            }

            $validate = $request->validate([
                'nama_kategori' => 'required',
            ]);

            if ($request->nama_kategori == 'jurnalistik') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'data tidak boleh diubah!'
                ]);
            }

            $kategoriUpdate = Kategori::where('id', $request->id);
            $kategoriSekarang = $kategoriUpdate->first();

            $kategori = Kategori::where('nama_kategori', $request->nama_kategori)->first();

            $kategoriLama = $kategori ? $kategori->nama_kategori : null;

            if (!$kategoriLama) {
                $kategoriUpdate->update([
                    'nama_kategori' => $request->nama_kategori,
                ]);
                return response()->json([
                    'status' => 'success',
                    'message' => 'data berhasil diubah!'
                ]);
            }
            if ($request->nama_kategori == $kategoriSekarang->nama_kategori) {
                return response()->json([
                    'message' => ''
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'nama kategori telah dipakai!'
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
