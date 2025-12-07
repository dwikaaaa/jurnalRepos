<?php

namespace App\Http\Controllers;

use App\Models\Anggota_jurnal;
use App\Models\Konfigurasi;
use Illuminate\Http\Request;

class aboutController extends Controller
{
    public function index(Request $request)
    {
        $getKonfigurasi = Konfigurasi::first();
        $getJabatan = Anggota_jurnal::with('userJurnal')->where('jabatan', '!=', null)->where('status', 'aktiv')->get()->groupBy('jabatan');
        $getDivisi = Anggota_jurnal::with('userJurnal')->where('status', 'aktiv')->get()->groupBy('divisi');

        $konfigurasi = [
            'judul_website' => $getKonfigurasi->judul_website,
            'deskripsi' => $getKonfigurasi->deskripsi,
            'profil_website' => $getKonfigurasi->profil_website,
            'nama_foto' => $getKonfigurasi->nama_foto,
            'instagram' => $getKonfigurasi->instagram,
            'facebook' => $getKonfigurasi->facebook,
            'email' => $getKonfigurasi->email,
            'alamat' => $getKonfigurasi->alamat,
            'no_wa' => $getKonfigurasi->no_wa,
        ];
        $jabatan = $getJabatan->map(function ($group) {
            return $group->map(function ($jbtn) {
                return [
                    'id' => $jbtn->userJurnal->id,
                    'username' => $jbtn->userJurnal->username,
                    'nama' => $jbtn->userJurnal->nama,
                    'level' => $jbtn->userJurnal->level,
                    'divisi' => $jbtn->divisi ?? null,
                    'jabatan' => $jbtn->jabatan ?? null,
                    'status' => $jbtn->status ?? null
                ];
            });
        });
        $divisi = $getDivisi->map(function ($group) {
            return $group->map(function ($item) {
                return [
                    'id' => $item->userJurnal->id,
                    'username' => $item->userJurnal->username,
                    'nama' => $item->userJurnal->nama,
                    'level' => $item->userJurnal->level,
                    'divisi' => $item->divisi,
                    'jabatan' => $item->jabatan,
                    'status' => $item->status
                ];
            });
        });

        return response()->json([
            'identitas jurnal' => $konfigurasi,
            'jabatan' => $jabatan,
            'divisi' => $divisi,
        ]);
    }
}
