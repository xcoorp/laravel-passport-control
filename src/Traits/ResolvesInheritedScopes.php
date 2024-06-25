<?php

namespace XCoorp\PassportControl\Traits;

trait ResolvesInheritedScopes
{
    /**
     * Resolve all possible scopes.
     */
    protected function resolveInheritedScopes(string $scope): array
    {
        $parts = explode(':', $scope);

        $partsCount = count($parts);

        $scopes = [];

        for ($i = 1; $i <= $partsCount; $i++) {
            $scopes[] = implode(':', array_slice($parts, 0, $i));
        }

        return $scopes;
    }
}
