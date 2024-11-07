<?php

namespace App\Models\ManagementSDM;

use App\Models\User;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Position;
use App\Models\Department;
use Filament\Facades\Filament;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Models\ManagementProject\Project;
use App\Models\ManagementCRM\TicketResponse;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ManagementCRM\CustomerInteraction;
use App\Models\ManagementStock\PurchaseTransaction;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'branch_id',
        'first_name',
        'last_name',
        'employee_code',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'national_id_number',
        'position_id',
        'department_id',
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
        'user_id' => 'integer',
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'first_name' => 'string',
        'last_name' => 'string',
        'employee_code' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'date_of_birth' => 'date',
        'gender' => 'string',
        'national_id_number' => 'string',
        'position_id' => 'integer',
        'department_id' => 'integer',
        'date_of_joining' => 'date',
        'salary' => 'decimal:2',
        'employment_status' => 'string',
        'manager_id' => 'integer',
        'address' => 'string',
        'city' => 'string',
        'state' => 'string',
        'postal_code' => 'string',
        'country' => 'string',
        'status' => 'string',
        'profile_picture' => 'string',
        'contract' => 'string',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

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

    public function ticketResponses(): HasMany
    {
        return $this->hasMany(TicketResponse::class);
    }

    public function project(): HasMany
    {
        return $this->hasMany(Project::class, 'manager_id');
    }

    public function purchaseTransaction(): HasMany
    {
        return $this->hasMany(PurchaseTransaction::class, 'purchasing_agent_id');
    }
}
