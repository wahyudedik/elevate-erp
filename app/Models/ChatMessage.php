<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ChatMessage extends Model
{
    use HasFactory, Notifiable;

    protected  $table = 'chat_messages';

    protected $fillable = [
        'company_id',
        'chat_room_id',
        'user_id',
        'message',
        'file_path',
        'file_type',
        'is_system_message'
    ];

    protected $casts = [
        'is_system_message' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class, 'chat_room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function chatMessageRead()
    {
        return $this->hasMany(ChatMessageRead::class, 'chat_message_id');
    }
}
