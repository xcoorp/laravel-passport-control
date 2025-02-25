<?php

namespace XCoorp\PassportControl\Factories;

use Illuminate\Support\Carbon;
use XCoorp\PassportControl\Contracts\TokenFactory as TokenFactoryContract;
use XCoorp\PassportControl\Contracts\Token as TokenContract;
use XCoorp\PassportControl\Enums\CredentialType;
use XCoorp\PassportControl\Token;

class TokenFactory implements TokenFactoryContract
{
    /**
     * {@inheritDoc}
     */
    public function createToken(mixed $introspectionResult): TokenContract
    {
        return new Token(
            $introspectionResult['active'] ?? false,
            isset($introspectionResult['scope']) ? explode(' ', $introspectionResult['scope']) : [],
                $introspectionResult['client_id'],
                $introspectionResult['sub'],
            CredentialType::from(in_array($json['credential_type'] ?? 'unknown', array_column(CredentialType::cases(), 'value')) ? $introspectionResult['credential_type'] : 'unknown'),
            Carbon::createFromTimestamp($introspectionResult['exp']),
                $introspectionResult['username'] ?? null,
            isset($introspectionResult['iat']) ? Carbon::createFromTimestamp($introspectionResult['iat']) : null,
            isset($introspectionResult['nbf']) ? Carbon::createFromTimestamp($introspectionResult['nbf']) : null,
        );
    }
}
