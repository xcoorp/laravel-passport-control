<?php

namespace XCoorp\PassportControl\Models\Traits;

use XCoorp\PassportControl\Contracts\Token;

trait HasApiTokens
{
    /**
     * The current access token for the authentication user.
     */
    protected ?Token $accessToken = null;

    public function token(): Token
    {
        return $this->accessToken;
    }

    /**
     * Determine if the current API token has a given scope.
     */
    public function tokenCan(string $scope): bool
    {
        return $this->accessToken && $this->accessToken->can($scope);
    }

    /**
     * Set the current access token for the user.
     */
    public function withAccessToken(Token $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }
}
