<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suka extends Model
{
    protected $fillable = [
        'id_konten',
        'id_kategori',
        'id_user',
        'tanggal'
    ];  

    public function konten() {
        return $this->belongsTo(Konten::class, 'id_konten', 'id');
    }
    public function kategoriLike() {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id');
    }
    public function userLike() {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
