<?php

namespace XCoorp\PassportControl\Resolver;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use XCoorp\PassportControl\Contracts\Token;
use XCoorp\PassportControl\Contracts\UserResolver as UserResolverContract;

class UserResolver implements UserResolverContract
{
    public function resolveUserByToken(Token $token, UserProvider $provider): ?Authenticatable
    {
        return $provider->retrieveById($token->user());
    }
}
