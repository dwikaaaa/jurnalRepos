<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anggota_jurnal extends Model
{
    protected $fillable = [
        'jabatan',
        'divisi',
        'status',
        'id_user'
    ];

    public function userJurnal() {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
