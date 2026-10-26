<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $table = 'sales';

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'amount',
        'payment_method',
        'status',
        'delivery_status',
        'resi',
        'created_at',
        'color',
        'nama',
        'alamat',
        'no_telp',
        'catatan',
        'ongkir',
        'shipping_courier',
        'shipping_service',
        'shipped_at',
        'delivered_at',
        'shipping_etd',
    ];

    public $timestamps = false;
}
