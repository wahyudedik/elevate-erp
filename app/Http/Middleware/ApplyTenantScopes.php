<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Models\Company;
use App\Models\CompanyUser;
use Illuminate\Http\Request;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\Response;

class ApplyTenantScopes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        CompanyUser::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );
        // dd($request);
        return $next($request);
    }
}
