<?php

namespace App\Models\ManagementFinancial;

use App\Models\BaseModel;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ManagementFinancial\Accounting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Ledger extends BaseModel
{
    use HasFactory, SoftDeletes, Notifiable, LogsActivity;

    protected $table = 'ledgers';

    protected $fillable = [
        'company_id',
        'branch_id',
        'account_id',
        'transaction_date',
        'transaction_type',
        'amount',
        'transaction_description',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'company_id',
                'branch_id',
                'account_id',
                'transaction_date',
                'transaction_type',
                'amount',
                'transaction_description',
            ]);
    }

    protected $casts = [
        'company_id' => 'integer',
        'branch_id' => 'integer',
        'account_id' => 'integer',
        'transaction_type' => 'string',
        'transaction_description' => 'string',
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function account()
    {
        return $this->belongsTo(Accounting::class, 'account_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'ledger_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::saving(function (Model $record) {
    //         $accounting = Accounting::find($record->account_id);

    //         if ($record->transaction_type === 'debit') {
    //             $accounting->current_balance += $record->amount;
    //         } elseif ($record->transaction_type === 'credit') {
    //             $accounting->current_balance -= $record->amount;
    //         }

    //         $accounting->save();
    //     });

    //     static::deleting(function (Model $record) {
    //         $accounting = Accounting::find($record->account_id);

    //         if ($record->transaction_type === 'debit') {
    //             $accounting->current_balance -= $record->amount;
    //         } elseif ($record->transaction_type === 'credit') {
    //             $accounting->current_balance += $record->amount;
    //         }

    //         $accounting->save();
    //     });

    //     static::restoring(function (Model $record) {
    //         $accounting = Accounting::withTrashed()->find($record->account_id);

    //         if ($accounting) {
    //             if ($record->transaction_type === 'debit') {
    //                 $accounting->current_balance += $record->amount;
    //             } elseif ($record->transaction_type === 'credit') {
    //                 $accounting->current_balance -= $record->amount;
    //             }

    //             $accounting->save();
    //         }
    //     });

    //     static::updating(function (Model $record) {
    //         $oldRecord = $record->getOriginal();
    //         $accounting = Accounting::find($record->account_id);

    //         // Reverse the old entry
    //         if ($oldRecord['transaction_type'] === 'debit') {
    //             $accounting->current_balance -= $oldRecord['amount'];
    //         } elseif ($oldRecord['transaction_type'] === 'credit') {
    //             $accounting->current_balance += $oldRecord['amount'];
    //         }

    //         // Apply the new entry
    //         if ($record->transaction_type === 'debit') {
    //             $accounting->current_balance += $record->amount;
    //         } elseif ($record->transaction_type === 'credit') {
    //             $accounting->current_balance -= $record->amount;
    //         }

    //         $accounting->save();
    //     });
    // }
}
