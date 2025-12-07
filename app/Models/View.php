<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class View extends Model
{
    protected $fillable = [
        'id_user',
        'id_konten',
    ];

    public function userView(){
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
    public function kontenView() {
        return $this->belongsTo(Konten::class, 'id_konten', 'id');
    }
}
