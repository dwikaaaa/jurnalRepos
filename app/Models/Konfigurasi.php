<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Konfigurasi extends Model
{
    protected $fillable = [
        'judul_website',
        'profil_website',
        'nama_foto',
        'instagram',
        'facebook',
        'email',
        'alamat',
        'no_wa',
        'deskripsi',
    ];
}
