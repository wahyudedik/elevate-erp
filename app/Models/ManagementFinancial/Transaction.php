<?php

namespace App\Models\ManagementFinancial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Transaction extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'transactions';

    protected $fillable = [
        'ledger_id',
        'transaction_number',
        'status',
        'amount',
        'notes',
    ];

    public function ledger()
    {
        return $this->belongsTo(Ledger::class, 'ledger_id');
    }


    protected static function boot()
    {
        parent::boot();

        static::saving(function (Model $record) {
            $ledger = Ledger::find($record->ledger_id);

            if ($record->status === 'completed') {
                $ledger->amount += $record->amount;
            } elseif ($record->status === 'failed') {
                $ledger->amount -= $record->amount;
            } elseif ($record->status === 'pending') {
                // No action needed for pending status
            }

            $ledger->save();
        });


        static::updating(function (Model $record) {
            $originalRecord = $record->getOriginal();
            $ledger = Ledger::find($record->ledger_id);

            if ($originalRecord['status'] !== $record->status) {
                if ($originalRecord['status'] === 'completed' && $record->status !== 'completed') {
                    $ledger->amount -= $record->amount;
                } elseif ($originalRecord['status'] !== 'completed' && $record->status === 'completed') {
                    $ledger->amount += $record->amount;
                } elseif ($originalRecord['status'] === 'failed' && $record->status !== 'failed') {
                    $ledger->amount += $record->amount;
                } elseif ($originalRecord['status'] !== 'failed' && $record->status === 'failed') {
                    $ledger->amount -= $record->amount;
                }
            } elseif ($originalRecord['amount'] !== $record->amount && $record->status === 'completed') {
                $ledger->amount += ($record->amount - $originalRecord['amount']);
            } elseif ($originalRecord['amount'] !== $record->amount && $record->status === 'failed') {
                $ledger->amount -= ($record->amount - $originalRecord['amount']);
            }

            if ($record->status === 'pending') {
                // No changes to ledger amount for pending status
            }

            $ledger->save();
        });

        static::restoring(function (Model $record) {
            $ledger = Ledger::find($record->ledger_id);

            if ($record->status === 'completed') {
                $ledger->amount += $record->amount;
            } elseif ($record->status === 'failed') {
                $ledger->amount -= $record->amount;
            } elseif ($record->status === 'pending') {
                // No action needed for pending status
            }

            $ledger->save();
        });

        static::deleting(function (Model $record) {
            $ledger = Ledger::find($record->ledger_id);

            if ($record->status === 'completed') {
                $ledger->amount -= $record->amount;
            } elseif ($record->status === 'failed') {
                $ledger->amount += $record->amount;
            } elseif ($record->status === 'pending') {
                // No action needed for pending status
            }

            $ledger->save();
        });
    }
}
