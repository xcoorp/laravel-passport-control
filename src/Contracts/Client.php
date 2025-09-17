<?php

declare(strict_types=1);

namespace XCoorp\PassportControl\Contracts;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;

interface Client
{
    /**
     * Get a Request Object with the baseUrl set and the access token attached to it
     * If this returns null, it means that it was not able to create a request object
     * (for example, access token could not be retrieved)
     */
    public function request(): PendingRequest|Factory|null;
}
