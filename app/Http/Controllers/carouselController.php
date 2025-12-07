<?php

namespace App\Http\Controllers;

use App\Models\Carousel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class carouselController extends Controller
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

            $getCarousel = Carousel::all();

            $data = $getCarousel->map(function ($carousel) {
                return [
                    'id' => $carousel->id,
                    'judul' => $carousel->judul,
                    'foto' => $carousel->foto,
                    'nama_foto' => $carousel->nama_foto
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

    public function postCarousel(Request $request)
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
                'judul' => 'required',
                'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // foto
            $file = $request->file('foto');
            $namaFile = time() . '_' . $file->getClientOriginalName();

            // judul
            $carousel = Carousel::where('judul', $request->judul)->first();

            if (!$carousel) {
                $path = $file->move(public_path('upload/carousel'), $namaFile);
                Carousel::create([
                    'judul' => $request->judul,
                    'foto' => url('upload/carousel/' . $namaFile),
                    'nama_foto' => $namaFile
                ]);

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

    public function destCarousel(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->level != 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'unautorized'
                ], 401);
            }

            $foto = $request->namaFoto;

            $carousel = Carousel::where('nama_foto', $foto)->first();
            if ($carousel) {
                $path = public_path('upload/carousel/' . basename($foto));

                if (File::exists($path)) {
                    File::delete($path);
                }

                $carousel->delete();

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

    public function carouselEditPage(Request $request, $nama_foto)
    {
        try {
            $users = $request->user();

            if (!$users || $users->level != 'admin') {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            $getCarousel = Carousel::where('nama_foto', $nama_foto)->first();

            return response()->json([
                'id' => $getCarousel->id,
                'judul' => $getCarousel->judul,
                'foto' => $getCarousel->foto,
                'nama_foto' => $getCarousel->nama_foto
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateCarousel(Request $request)
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
                'judul' => 'required',
                'foto' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048', 
            ]);

            $carousel = Carousel::where('nama_foto', $request->namaFoto)->first();

            if (!$carousel) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Carousel tidak ditemukan'
                ], 404);
            }

            $existingCarousel = Carousel::where('judul', $request->judul)
                ->where('id', '!=', $carousel->id)
                ->first();

            if ($existingCarousel) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Judul sudah digunakan oleh carousel lain'
                ]);
            }

            $updateData = [
                'judul' => $request->judul,
            ];

            if ($request->hasFile('foto')) {
                $path = public_path('upload/carousel/' . $carousel->nama_foto);
                if (File::exists($path)) {
                    File::delete($path);
                }

                $file = $request->file('foto');
                $namaFile = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('upload/carousel'), $namaFile);

                $updateData['foto'] = url('upload/carousel/' . $namaFile);
                $updateData['nama_foto'] = $namaFile;
            }

            $carousel->update($updateData);

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil diubah!',
                'nama_foto' => $carousel->nama_foto
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
