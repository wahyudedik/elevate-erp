<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class ContactDev extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'contact_devs';

    protected $fillable = [
        'name',
        'address',
        'call',
        'email',
        'location'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
}
