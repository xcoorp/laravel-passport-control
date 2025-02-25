<?php

namespace XCoorp\PassportControl\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

interface UserResolver
{
    public function resolveUserByToken(Token $token, UserProvider $provider): ?Authenticatable;
}
