<?php

namespace App\Models\ManagementFinancial;

use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JournalEntry extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }

    protected $table = 'journal_entries';

    protected $fillable = [
        'company_id',
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

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
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
