<?php

namespace App\Models\Scopes;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class CompanyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Pastikan hanya data yang berhubungan dengan company user yang sedang login
        if (Auth::check()) {
            // Dapatkan daftar company_id yang dihubungkan dengan user yang sedang login
            $userCompanyIds = DB::table('company_user')
                ->where('user_id', Auth::user()->id)
                ->pluck('company_id')
                ->toArray();
            // dd($userCompanyIds);
            // Filter data berdasarkan company_id yang terhubung
            $builder->whereIn('company_id', $userCompanyIds);
        }
    }
}
