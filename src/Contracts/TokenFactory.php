<?php

declare(strict_types=1);

namespace XCoorp\PassportControl\Contracts;

interface TokenFactory
{
    /**
     * Create a new token instance from introspection data.
     *
     * @param  mixed  $introspectionResult  The introspection data result as returned by the TokenRepository
     */
    public function createToken(mixed $introspectionResult): Token;
}
