<?php

namespace XCoorp\PassportControl\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use XCoorp\PassportControl\Models\Traits\HasApiTokens;

class Client implements Authenticatable
{
    use HasApiTokens;

    public function __construct(
        public string $id
    ) {
    }

    public function getAuthIdentifierName(): string
    {
        return 'client_id';
    }

    public function getAuthIdentifier(): string
    {
        return $this->id;
    }

    /**
     * THE METHODS BELOW ARE REQUIRED BY THE AUTHENTICATABLE INTERFACE
     * BUT ARE NOT APPLICABLE TO THIS CLASS.
     */
    public function getAuthPassword(): ?string
    {
        return null;
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void
    {
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function getAuthPasswordName(): string
    {
        return '';
    }
}
