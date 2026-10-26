<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'nama',
        'kategori',
        'ketebalan',
        'ukuran',
        'deskripsi',
        'warna',
        'harga',
        'gambar',
        'stock',
        'weight_gram',
        'gambar2',
        'gambar3',
    ];

    public $timestamps = false;
}
