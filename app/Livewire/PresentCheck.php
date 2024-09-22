<?php

namespace App\Livewire;

use App\Models\ManagementSDM\Employee;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\ManagementSDM\Schedule;

class PresentCheck extends Component
{
    public function render()
    {
        $employee = Employee::where('user_id', Auth::user()->id)->first();
        $schedules = $employee ? Schedule::where('employee_id', $employee->id)->first() : null;
        return view(
            'livewire.present-check',
            [
                'schedules' => $schedules,
                'employee' => $employee,
            ]
        );
    }
}
