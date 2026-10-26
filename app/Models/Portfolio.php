<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $table = 'portfolio';

    protected $fillable = [
        'judul',
        'kategori',
        'deskripsi',
        'gambar',
        'created_at',
    ];

    public $timestamps = false;
}
