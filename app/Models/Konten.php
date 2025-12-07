<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Konten extends Model
{
    protected $fillable = [
        'judul',
        'keterangan',
        'foto',
        'nama_foto',
        'slug',
        'id_kategori',
        'tanggal',
        'id_user',
        'views',
        'likes',
        'komentar',
    ];

    public function topiks()
    {
        return $this->belongsToMany(Topik::class, 'konten_topiks', 'konten_id', 'topik_id');
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
    // public function topik()
    // {
    //     return $this->belongsTo(Topik::class, 'id_topik', 'id');
    // }
}
