<?php

namespace App\Models;

use Filament\Panel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\ManagementSDM\Leave;
use App\Models\ManagementSDM\Employee;
use App\Models\ManagementSDM\Schedule;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use App\Models\ManagementSDM\Attendance;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\HasTenants;
use Filament\Models\Contracts\FilamentUser;
use App\Models\ManagementFinancial\Accounting;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail, HasTenants
{
    use HasFactory, Notifiable, HasRoles, HasApiTokens, LogsActivity;

    public function canAccessPanel(Panel $panel): bool
    {
        //add validasi berdasarkan usertype
        if ($panel->getId() === 'admin') {
            return in_array($this->usertype, ['member', 'staff', 'dev']) && str_ends_with($this->email, '@gmail.com') && $this->is_active;
        }

        if ($panel->getId() === 'dev') {
            return $this->usertype === 'dev' && str_ends_with($this->email, '@gmail.com') && $this->is_active;
        }

        return false;
    }
    public function company(): BelongsToMany
    {
        return $this->belongsToMany(Company::class);
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->company;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->company()->whereKey($tenant)->exists();
    }

    public function companyUsers()
    {
        return $this->hasMany(CompanyUser::class, 'user_id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'image',
        'name',
        'email',
        'password',
        'usertype',
        'email_verified_at',
        'is_active',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'image',
                'name',
                'email',
                'password',
                'usertype',
                'email_verified_at',
                'is_active'
            ]);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean'
        ];
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? url('storage/' . $this->image) : null;
    }

    //relasi employee
    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    //relasi attendance
    public function schedule()
    {
        return $this->hasMany(Schedule::class, 'user_id');
    }

    public function leave()
    {
        return $this->hasMany(Leave::class, 'user_id');
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }

    public function chatRoomUser()
    {
        return $this->hasMany(ChatRoomUser::class, 'user_id');
    }

    public function chatMessage()
    {
        return $this->hasMany(ChatMessage::class, 'user_id');
    }

    public function chatMessageRead()
    {
        return $this->hasMany(ChatMessageRead::class, 'user_id');
    }
}
