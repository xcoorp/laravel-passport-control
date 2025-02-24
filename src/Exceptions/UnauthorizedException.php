<?php

namespace XCoorp\PassportControl\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedException extends HttpException
{
    private string $requiredScope = '';

    public static function forScopes(string $scope): self
    {
        $message = 'Access Token does not have the right scopes. Necessary scope is: '.$scope;

        $exception = new static(403, $message, null, [
            'WWW-Authenticate' => 'Bearer, scope="'.$scope.'", error="insufficient_scope"',
        ]);
        $exception->requiredScope = $scope;

        return $exception;
    }

    public static function invalidTokenType(): self
    {
        return new static(401, 'Access Token does not have the right type.', null, [
            'WWW-Authenticate' => 'Bearer, error="invalid_token_type"',
        ]);
    }

    public static function notLoggedIn(): self
    {
        return new static(401, 'Access Token missing or not valid.', null, [
            'WWW-Authenticate' => 'Bearer, error="invalid_token"',
        ]);
    }

    public function getRequiredScopes(): string
    {
        return $this->requiredScope;
    }
}
