<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topik extends Model
{
    protected $fillable = [
        'nama_topik',
        'id_kategori'
    ];

    public function kontens()
    {
        return $this->belongsToMany(Konten::class, 'konten_topiks', 'topik_id', 'konten_id');
    }

    public function kategoriTopik()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id');
    }
}
