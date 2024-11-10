<?php

namespace App\Models;

use Filament\Notifications\Concerns\CanBeInline;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Newsletter extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'newsletters';

    protected $fillable = [
        'email',
        'status'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
}
