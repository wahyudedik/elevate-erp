<?php

namespace App\Models\ManagementCRM;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Models\ManagementProject\Project;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ManagementCRM\CustomerInteraction;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ManagementSalesAndPurchasing\OrderProcessing;
use App\Models\ManagementSalesAndPurchasing\SalesTransaction;

class Customer extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'customers';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'email',
        'phone',
        'address',
        'company',
        'status',
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'name' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'address' => 'string',
        'company' => 'string',
        'status' => 'string',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function interactions()
    {
        return $this->hasMany(CustomerInteraction::class);
    }

    public function sale(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function customerSupport(): HasMany
    {
        return $this->hasMany(CustomerSupport::class);
    }

    public function project(): HasMany
    {
        return $this->hasMany(Project::class, 'client_id');
    }

    public function salesTransaction(): HasMany
    {
        return $this->hasMany(SalesTransaction::class);
    }

    public function orderProccessing(): HasMany
    {
        return $this->hasMany(OrderProcessing::class);
    }
}
