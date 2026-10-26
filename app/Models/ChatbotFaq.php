<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotFaq extends Model
{
    protected $table = 'chatbot_faqs';

    protected $fillable = [
        'question',
        'answer',
        'is_active',
        'created_at',
        'updated_at',
    ];

    public $timestamps = false;
}
