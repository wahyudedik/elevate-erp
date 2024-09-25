<?php

namespace App\Livewire;

use App\Models\CompanyUser;
use Livewire\Component;
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
    public function render()
    {
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
        $this->validate([
            'latitude' => 'required',
            'longitude' => 'required'
        ]);
        $schedule = Schedule::where('user_id', Auth::user()->id)->first();

        $company = CompanyUser::where('user_id', Auth::user()->id)->first();

        $today = Carbon::today()->format('Y-m-d');
        $approvedLeave = Leave::where('user_id', Auth::user()->id)
                            ->where('status', 'approved')
                            ->whereDate('start_date', '<=', $today)
                            ->whereDate('end_date', '>=', $today)
                            ->exists();

        if ($approvedLeave) {
            session()->flash('error', 'Anda tidak dapat melakukan presensi karena sedang cuti');
            return;
        }
 
        if ($schedule) {
            $attedance = Attendance::where('user_id', Auth::user()->id)
                            ->whereDate('created_at', date('Y-m-d'))->first();
            if (!$attedance) {
                $attedance = Attendance::create([
                    'company_id' => $company->company_id,
                    'user_id' => Auth::user()->id,
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
                    'latitude_check_out' => $this->latitude,
                    'longitude_check_out' => $this->longitude,
                    'check_in' => Carbon::now()->toTimeString(),
                    'check_out' => Carbon::now()->toTimeString(),
                    'status' => 'present',
                    'note' => 'Presensi Masuk' . ' ' . Carbon::now()->toTimeString(),
                ]);
            } else {
                $attedance->update([
                    'latitude_check_out' => $this->latitude,
                    'longitude_check_out' => $this->longitude,
                    'check_out' => Carbon::now()->toTimeString(),
                    'status' => 'present',
                    'note' => 'Presensi Pulang' . ' ' . Carbon::now()->toTimeString(),
                ]);
            }

            return redirect()->route('present-check', [
                'schedule' => $schedule,
                'insideRadius' => false
            ]);

            // return redirect()->route('presensi', [
            //     'schedule' => $schedule,
            //     'insideRadius' => false
            // ]);
            
        }
    }
}
