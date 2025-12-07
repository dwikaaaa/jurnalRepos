<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Konten_topik extends Model
{
    protected $fillable = [
        'konten_id',
        'topik_id'
    ];

    public function topixes() {
        return $this->belongsTo(Topik::class, 'topik_id', 'id');
    }
}
