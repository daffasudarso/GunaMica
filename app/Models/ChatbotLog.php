<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotLog extends Model
{
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
