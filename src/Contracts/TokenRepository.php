<?php

namespace XCoorp\PassportControl\Contracts;

interface TokenRepository
{
    /**
     * Call to the Introspection API with the given token ID.
     * Should return a Token object or null if errored / no response / not found.
     */
    public function introspect(string $token_id): ?Token;
}
