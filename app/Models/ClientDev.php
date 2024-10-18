<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class ClientDev extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'client_devs';

    protected $fillable = [
        'name',
        'client_logo',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'name' => 'string',
        'client_logo' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
