<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenLog extends Model
{
    protected $table = 'chatbot_logs';
    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'user_message',
        'bot_reply',
        'warning',
        'source',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}

