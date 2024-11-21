<?php

namespace App\Models;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyUser extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'company_user';

    protected $fillable = [
        'company_id',
        'user_id',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'user_id' => 'integer',
    ];

    // public function company()
    // {
    //     return $this->belongsToMany(Company::class, 'company_id');
    // }

    // public function user()
    // {
    //     return $this->belongsToMany(User::class, 'user_id');
    // }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
