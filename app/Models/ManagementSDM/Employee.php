<?php

namespace App\Models\ManagementSDM;

use App\Models\User;
use App\Models\Company;
use Filament\Facades\Filament;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Models\ManagementProject\Project;
use App\Models\ManagementCRM\TicketResponse;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ManagementCRM\CustomerInteraction;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ManagementSalesAndPurchasing\SalesTransaction;
use App\Models\ManagementSalesAndPurchasing\PurchaseTransaction;

class Employee extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    // protected static function booted()
    // {
    //     static::addGlobalScope(new CompanyScope);
    // }

    protected $table = 'employees';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'user_id',
        'company_id',
        'first_name',
        'last_name',
        'employee_code',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'national_id_number',
        'position',
        'department',
        'date_of_joining',
        'salary',
        'employment_status',
        'manager_id',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'status',
        'profile_picture',
        'contract'
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_joining' => 'date',
        'salary' => 'decimal:2',
        'profile_picture' => 'string',
        'contract' => 'string',
    ];

    //relasi user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function schedule()
    {
        return $this->hasMany(Schedule::class, 'employee_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // Relasi dengan tabel employees untuk manager
    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    // Relasi dengan tabel employee_positions
    public function employeePosition()
    {
        return $this->hasMany(EmployeePosition::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($employee) {
            EmployeePosition::create([
                'company_id' => Filament::getTenant()->id,
                'employee_id' => $employee->id,
                'position' => $employee->position,
                'start_date' => $employee->date_of_joining,
            ]);
        });

        static::updated(function ($employee) {
            if ($employee->isDirty('position') || $employee->isDirty('department')) {

                EmployeePosition::where('employee_id', $employee->id)
                    ->whereNull('end_date')
                    ->update(['end_date' => now()]);

                EmployeePosition::create([
                    'company_id' => Filament::getTenant()->id,
                    'employee_id' => $employee->id,
                    'position' => $employee->position,
                    'start_date' => now(),
                ]);
            }
        });
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function payroll(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function candidate(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function ticketResponse(): HasMany
    {
        return $this->hasMany(TicketResponse::class);
    }

    public function project(): HasMany
    {
        return $this->hasMany(Project::class, 'manager_id');
    }

    public function salesTransaction(): HasMany
    {
        return $this->hasMany(SalesTransaction::class, 'sales_rep_id');
    }

    public function purchaseTransaction(): HasMany
    {
        return $this->hasMany(PurchaseTransaction::class, 'purchasing_agent_id');
    }
}
