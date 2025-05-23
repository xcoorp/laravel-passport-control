<?php

namespace XCoorp\PassportControl\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use XCoorp\PassportControl\Exceptions\UnauthorizedException;

class CheckScopes
{
    public function handle($request, Closure $next, $scope, $guard = null)
    {
        $authGuard = Auth::guard($guard);

        $authenticatable = $authGuard->user();

        if (! $authenticatable) {
            throw UnauthorizedException::notLoggedIn();
        }

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        if (! $authenticatable->tokenCan($scope)) {
            throw UnauthorizedException::forScopes($scope);
        }

        return $next($request);
    }

    /**
     * Specify the scope and guard for the middleware.
     */
    public static function using(string $scope, ?string $guard = null): string
    {
        $args = is_null($guard) ? $scope : "$scope,$guard";

        return static::class.':'.$args;
    }
}
