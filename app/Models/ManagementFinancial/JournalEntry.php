<?php

namespace App\Models\ManagementFinancial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class JournalEntry extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'journal_entries';

    protected $fillable = [
        'entry_date',
        'description',
        'entry_type',
        'amount',
        'account_id',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function account()
    {
        return $this->belongsTo(Accounting::class, 'account_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function (Model $record) {
            $accounting = Accounting::find($record->account_id);

            if ($record->entry_type === 'debit') {
                $accounting->current_balance += $record->amount;
            } elseif ($record->entry_type === 'credit') {
                $accounting->current_balance -= $record->amount;
            }

            $accounting->save();
        });

        static::deleting(function (Model $record) {
            $accounting = Accounting::find($record->account_id);

            if ($record->entry_type === 'debit') {
                $accounting->current_balance -= $record->amount;
            } elseif ($record->entry_type === 'credit') {
                $accounting->current_balance += $record->amount;
            }

            $accounting->save();
        });

        static::restoring(function (Model $record) {
            $accounting = Accounting::withTrashed()->find($record->account_id);

            if ($accounting) {
                if ($record->entry_type === 'debit') {
                    $accounting->current_balance += $record->amount;
                } elseif ($record->entry_type === 'credit') {
                    $accounting->current_balance -= $record->amount;
                }

                $accounting->save();
            }
        });

        static::updating(function (Model $record) {
            $oldRecord = $record->getOriginal();
            $accounting = Accounting::find($record->account_id);

            // Reverse the old entry
            if ($oldRecord['entry_type'] === 'debit') {
                $accounting->current_balance -= $oldRecord['amount'];
            } elseif ($oldRecord['entry_type'] === 'credit') {
                $accounting->current_balance += $oldRecord['amount'];
            }

            // Apply the new entry
            if ($record->entry_type === 'debit') {
                $accounting->current_balance += $record->amount;
            } elseif ($record->entry_type === 'credit') {
                $accounting->current_balance -= $record->amount;
            }

            $accounting->save();
        });
    }
}
