<?php

namespace App\Models\ManagementFinancial;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use App\Models\Scopes\CompanyScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Transaction extends Model
{
    use HasFactory, Notifiable, SoftDeletes, LogsActivity;

    // protected static function booted()
    // {
    //     static::addGlobalScope(new CompanyScope);
    // }

    protected $table = 'transactions';

    protected $fillable = [
        'company_id',
        'branch_id',
        'ledger_id',
        'transaction_number',
        'status',
        'amount',
        'notes',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'company_id',
                'branch_id',
                'ledger_id',
                'transaction_number',
                'status',
                'amount',
                'notes',
            ]);
    }

    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'ledger_id' => 'integer',
        'transaction_number' => 'string',
        'status' => 'string',
        'amount' => 'decimal:2',
        'notes' => 'string',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function ledger()
    {
        return $this->belongsTo(Ledger::class, 'ledger_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }


    // protected static function boot()
    // {
    //     parent::boot();

    //     static::saving(function (Model $record) {
    //         $ledger = Ledger::find($record->ledger_id);

    //         if ($record->status === 'completed') {
    //             $ledger->amount += $record->amount;
    //         } elseif ($record->status === 'failed') {
    //             $ledger->amount -= $record->amount;
    //         } elseif ($record->status === 'pending') {
    //             // No action needed for pending status
    //         }

    //         $ledger->save();
    //     });


    //     static::updating(function (Model $record) {
    //         $originalRecord = $record->getOriginal();
    //         $ledger = Ledger::find($record->ledger_id);

    //         if ($originalRecord['status'] !== $record->status) {
    //             if ($originalRecord['status'] === 'completed' && $record->status !== 'completed') {
    //                 $ledger->amount -= $record->amount;
    //             } elseif ($originalRecord['status'] !== 'completed' && $record->status === 'completed') {
    //                 $ledger->amount += $record->amount;
    //             } elseif ($originalRecord['status'] === 'failed' && $record->status !== 'failed') {
    //                 $ledger->amount += $record->amount;
    //             } elseif ($originalRecord['status'] !== 'failed' && $record->status === 'failed') {
    //                 $ledger->amount -= $record->amount;
    //             }
    //         } elseif ($originalRecord['amount'] !== $record->amount && $record->status === 'completed') {
    //             $ledger->amount += ($record->amount - $originalRecord['amount']);
    //         } elseif ($originalRecord['amount'] !== $record->amount && $record->status === 'failed') {
    //             $ledger->amount -= ($record->amount - $originalRecord['amount']);
    //         }

    //         if ($record->status === 'pending') {
    //             // No changes to ledger amount for pending status
    //         }

    //         $ledger->save();
    //     });

    //     static::restoring(function (Model $record) {
    //         $ledger = Ledger::find($record->ledger_id);

    //         if ($record->status === 'completed') {
    //             $ledger->amount += $record->amount;
    //         } elseif ($record->status === 'failed') {
    //             $ledger->amount -= $record->amount;
    //         } elseif ($record->status === 'pending') {
    //             // No action needed for pending status
    //         }

    //         $ledger->save();
    //     });

    //     static::deleting(function (Model $record) {
    //         $ledger = Ledger::find($record->ledger_id);

    //         if ($record->status === 'completed') {
    //             $ledger->amount -= $record->amount;
    //         } elseif ($record->status === 'failed') {
    //             $ledger->amount += $record->amount;
    //         } elseif ($record->status === 'pending') {
    //             // No action needed for pending status
    //         }

    //         $ledger->save();
    //     });
    // }
}
