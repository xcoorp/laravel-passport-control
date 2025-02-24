<?php

namespace XCoorp\PassportControl\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use XCoorp\PassportControl\Enums\CredentialType;
use XCoorp\PassportControl\Exceptions\UnauthorizedException;

class CredentialTypeMiddleware
{
    public function handle($request, Closure $next, $credential_type, $guard = null)
    {
        $authGuard = Auth::guard($guard);

        $user = $authGuard->user();

        if (! $user ) {
            throw UnauthorizedException::notLoggedIn();
        }

        /** @noinspection PhpUndefinedMethodInspection */
        if ($credential_type && (!in_array($credential_type, array_column(CredentialType::cases(), "value")) || ! CredentialType::from($credential_type) === $user->token()->type())) {
            throw UnauthorizedException::invalidTokenType();
        }

        return $next($request);
    }

    /**
     * Specify the credential_type and guard for the middleware.
     */
    public static function using(string $credential_type, ?string $guard = null): string
    {
        $args = is_null($guard) ? $credential_type : "$credential_type,$guard";

        return static::class.':'.$args;
    }
}
