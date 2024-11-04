<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ManagementSDM\Attendance;

class Map extends Component
{

    public function render()
    {
        $attendances = Attendance::with('user')->get();
        return view('livewire.map',[
            'attendances' => $attendances
        ]);
    }
}