<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Komen extends Model
{
    protected $fillable = [
        'komen',
        'id_konten',
        'id_user',
        'tanggal'
    ];

    public function konten()
    {
        return $this->belongsTo(Konten::class, 'id_konten', 'id');
    }
    public function userKomen()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
