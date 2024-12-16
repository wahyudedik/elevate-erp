<?php

namespace App\Observers;

use App\Models\ManagementSDM\Employee;
use App\Models\ManagementSDM\EmployeePosition;

class EmployeeObserver
{
    /**
     * Handle the Employee "created" event.
     */
    public function created(Employee $employee): void
    {
        // Buat record pertama di employee_positions
        EmployeePosition::create([
            'company_id' => $employee->company_id,
            'branch_id' => $employee->branch_id,
            'employee_id' => $employee->id,
            'department' => json_encode([
                'id' => $employee->department_id,
                'name' => $employee->department->name
            ]),
            'position' => json_encode([
                'id' => $employee->position_id,
                'name' => $employee->position->name
            ]),
            'start_date' => $employee->date_of_joining,
        ]);
    }

    /**
     * Handle the Employee "updated" event.
     */
    public function updated(Employee $employee): void
    {
        // Cek perubahan branch, position, atau department
        if ($employee->wasChanged(['branch_id', 'position_id', 'department_id'])) {
            // Update end_date record terakhir
            $lastPosition = $employee->employeePosition()->whereNull('end_date')->first();
            if ($lastPosition) {
                $lastPosition->update(['end_date' => now()]);
            }

            // Buat record baru
            EmployeePosition::create([
                'company_id' => $employee->company_id,
                'branch_id' => $employee->branch_id,
                'employee_id' => $employee->id,
                'department' => json_encode([
                    'id' => $employee->department_id,
                    'name' => $employee->department->name
                ]),
                'position' => json_encode([
                    'id' => $employee->position_id,
                    'name' => $employee->position->name
                ]),
                'start_date' => now(),
            ]);
        }

        // Logika untuk perubahan status
        if ($employee->wasChanged('status')) {
            if (in_array($employee->status, ['inactive', 'terminated', 'resigned'])) {
                // Jika status berubah menjadi non-aktif
                $lastPosition = $employee->employeePosition()->whereNull('end_date')->first();
                if ($lastPosition) {
                    $lastPosition->update(['end_date' => now()]);
                }
            } elseif ($employee->status === 'active') {
                // Jika status berubah menjadi aktif
                EmployeePosition::create([
                    'company_id' => $employee->company_id,
                    'branch_id' => $employee->branch_id,
                    'employee_id' => $employee->id,
                    'department' => json_encode([
                        'id' => $employee->department_id,
                        'name' => $employee->department->name
                    ]),
                    'position' => json_encode([
                        'id' => $employee->position_id,
                        'name' => $employee->position->name
                    ]),
                    'start_date' => now(),
                ]);
            }
        }
    }

    /**
     * Handle the Employee "deleted" event.
     */
    public function deleted(Employee $employee): void
    {
        //
    }

    /**
     * Handle the Employee "restored" event.
     */
    public function restored(Employee $employee): void
    {
        //
    }

    /**
     * Handle the Employee "force deleted" event.
     */
    public function forceDeleted(Employee $employee): void
    {
        //
    }
}
