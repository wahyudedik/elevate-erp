<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use Filament\Resources\Pages\Page;

class CreatePayslip extends Page
{
    protected static string $resource = PayrollResource::class;

    protected static string $view = 'filament.resources.payroll-resource.pages.create-payslip';

    public function mount(): void
    {
        // $payrollId = request()->query('payroll_id');
        // $this->record = PayrollResource::getModel()::findOrFail($payrollId);
    }
}
