<?php

namespace App\Http\Controllers;

use App\Models\Konfigurasi;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class konfigurasiController extends Controller
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

            $getKonfigurasi = Konfigurasi::first();

            return response()->json([
                'judul_website' => $getKonfigurasi->judul_website,
                'deskripsi' => $getKonfigurasi->deskripsi,
                'foto' => $getKonfigurasi->profil_website,
                'nama_foto' => $getKonfigurasi->nama_foto,
                'instagram' => $getKonfigurasi->instagram,
                'facebook' => $getKonfigurasi->facebook,
                'email' => $getKonfigurasi->email,
                'alamat' => $getKonfigurasi->alamat,
                'no_wa' => $getKonfigurasi->no_wa,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateKonfigurasi(Request $request)
    {
        try {
            $users = $request->user();

            if (!$users || $users->level != 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'unautorized'
                ], 401);
            }

            $konfigurasiUpdate = Konfigurasi::first();

            $validate = $request->validate([
                'judul_website' => 'required',
                'profil_website' => 'image|mimes:jpeg,png,jpg|max:2048',
                'instagram' => 'required',
                'facebook' => 'required',
                'email' => 'required',
                'alamat' => 'required',
                'no_wa' => 'required',
                'deskripsi' => 'required',
            ]);

            $updateData = [
                'judul_website' => $request->judul_website,
                'instagram' => $request->instagram,
                'facebook' => $request->facebook,
                'email' => $request->email,
                'alamat' => $request->alamat,
                'no_wa' => $request->no_wa,
                'deskripsi' => $request->deskripsi,
            ];

            if ($request->hasFile('profil_website')) {
                $path = public_path('upload/ppJurnal/' . $konfigurasiUpdate->nama_foto);
                if (File::exists($path)) {
                    File::delete($path);
                }

                $file = $request->file('profil_website');
                $namaFile = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('upload/ppJurnal'), $namaFile);

                $updateData['profil_website'] = url('upload/ppJurnal/' . $namaFile);
                $updateData['nama_foto'] = $namaFile;
            }

            $konfigurasiUpdate->update($updateData);

            return response()->json([
                'status' => 'success',
                'message' => 'data berhasil diubah!'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
