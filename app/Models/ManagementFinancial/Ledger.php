<?php

namespace App\Models\ManagementFinancial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ManagementFinancial\Accounting;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ledger extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'ledgers';

    protected $fillable = [
        'account_id',
        'transaction_date',
        'transaction_type',
        'amount',
        'transaction_description',
    ];

    public function account()
    {
        return $this->belongsTo(Accounting::class, 'account_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'ledger_id');
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
