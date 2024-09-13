<?php

namespace App\Models\ManagementSDM;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Models\ManagementProject\Project;
use App\Models\ManagementCRM\TicketResponse;
use App\Models\ManagementCRM\CustomerInteraction;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ManagementSalesAndPurchasing\SalesTransaction;
use App\Models\ManagementSalesAndPurchasing\PurchaseTransaction;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Employee extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'employees';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
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
                'employee_id' => $employee->id,
                'position' => $employee->position,
                'start_date' => $employee->date_of_joining,
            ]);

            // User::create([
            //     'name' => $employee->first_name . ' ' . $employee->last_name,
            //     'email' => $employee->email,
            //     'password' => bcrypt('123456789'),
            //     'employee_id' => $employee->id,
            //     'usertype' => 'staff',
            //     'status' => 'active',
            //     'email_verified_at' => now(),
            // ]);
        });

        static::updated(function ($employee) {
            if ($employee->isDirty('position') || $employee->isDirty('department')) {
                
                EmployeePosition::where('employee_id', $employee->id)
                ->whereNull('end_date')
                ->update(['end_date' => now()]);

                EmployeePosition::create([
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
