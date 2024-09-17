<?php

namespace App\Models;

use App\Models\ManagementSDM\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'logo',
        'description',
        'address',
        'phone',
        'email',
        'website',
        'slogan',
        'mission',
        'vision',
        'qna',
    ];


    protected $casts = [
        'qna' => 'array',
        'logo' => 'array',
    ];

    public function members()
    {
        return $this->belongsToMany(User::class);
    }

    public function employee()
    {
        return $this->hasMany(Employee::class, 'company_id');
    }
}
