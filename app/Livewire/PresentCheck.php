<?php

namespace App\Livewire;

use App\Models\Company;
use Livewire\Component;
use App\Models\CompanyUser;
use Filament\Facades\Filament;
use Illuminate\Support\Carbon;
use App\Models\ManagementSDM\Leave;
use Illuminate\Support\Facades\Auth;
use App\Models\ManagementSDM\Employee;
use App\Models\ManagementSDM\Schedule;
use App\Models\ManagementSDM\Attendance;

class PresentCheck extends Component
{
    public $latitude;
    public $longitude;
    public $insideRadius = false;
    protected $company;

    public function mount()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $this->company = Company::withoutGlobalScopes()
            ->whereHas('members', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->first();

        if ($this->company) {
            Filament::setTenant($this->company);
        }
    }

    public function setLocation($lat, $lng)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $company = Company::withoutGlobalScopes()
            ->whereHas('members', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->first();

        if ($company) {
            Filament::setTenant($company);
            $this->latitude = $lat;
            $this->longitude = $lng;
            $this->insideRadius = true;
        }
    }

    public function render()
    {
        if ($this->company) {
            Filament::setTenant($this->company);
        }

        $schedule = Schedule::where('user_id', Auth::user()->id)->first();
        $attendance = Attendance::where('user_id', Auth::user()->id)
            ->whereDate('created_at', date('Y-m-d'))->first();

        return view('livewire.present-check', [
            'schedule' => $schedule,
            'insideRadius' => $this->insideRadius,
            'attendance' => $attendance
        ]);
    }

    public function store()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $company = Company::withoutGlobalScopes()
            ->whereHas('members', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->first();

        if ($company) {
            Filament::setTenant($company);

            $this->validate([
                'latitude' => 'required',
                'longitude' => 'required'
            ]);

            $schedule = Schedule::where('user_id', $user->id)->first();

            if ($schedule) {
                $attendance = Attendance::where('user_id', $user->id)
                    ->whereDate('created_at', date('Y-m-d'))->first();

                if (!$attendance) {
                    Attendance::create([
                        'company_id' => $company->id,
                        'user_id' => $user->id,
                        'branch_id' => $schedule->branch_id,
                        'employee_id' => $schedule->employee_id,
                        'schedule_id' => $schedule->id,
                        'date' => date('Y-m-d'),
                        'schedules_latitude' => $schedule->branch->latitude,
                        'schedules_longitude' => $schedule->branch->longitude,
                        'schedules_check_in' => $schedule->shift->start_time,
                        'schedules_check_out' => $schedule->shift->end_time,
                        'latitude_check_in' => $this->latitude,
                        'longitude_check_in' => $this->longitude,
                        'check_in' => Carbon::now()->toTimeString(),
                        'status' => 'present',
                        'note' => 'Presensi Masuk ' . Carbon::now()->toTimeString(),
                    ]);
                } else {
                    $attendance->update([
                        'latitude_check_out' => $this->latitude,
                        'longitude_check_out' => $this->longitude,
                        'check_out' => Carbon::now()->toTimeString(),
                        'status' => 'present',
                        'note' => 'Presensi Pulang ' . Carbon::now()->toTimeString(),
                    ]);
                }
            }
        }
    }
}
