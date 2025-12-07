<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carousel extends Model
{
    protected $fillable = [
        'judul',
        'foto',
        'nama_foto'
    ];
}
