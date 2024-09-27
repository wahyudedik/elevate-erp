<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CompanyUser extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'company_user';

    protected $fillable = [
        'company_id',
        'user_id',
    ];

    public function company()
    {
        return $this->belongsToMany(Company::class, 'company_id');
    }

    public function user()
    {
        return $this->belongsToMany(User::class, 'user_id');
    }
}
