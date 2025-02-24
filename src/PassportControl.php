<?php

namespace XCoorp\PassportControl;

use Closure;

class PassportControl
{
    /**
     * The User model class to use, when creating a new user
     */
    public static ?string $userModel = null;

    /**
     * The function that maps a Token to the user model attributes.
     */
    public static ?Closure $userModelMapper = null;

    /**
     * Create user if not present
     */
    public static ?bool $withUserCreationIfNotPresent = null;

    /**
     * The storage location of the passport public key
     */
    public static ?string $publicKeyPath = null;

    /**
     * Indicates the scope should inherit its parent scope.
     */
    public static ?bool $withInheritedScopes = null;

    /**
     * The introspect endpoint.
     */
    public static ?string $introspectEndpoint = null;

    /**
     * The access token endpoint, used for creating an access token for the introspection endpoint.
     */
    public static ?string $accessTokenEndpoint = null;

    /**
     * The client ID used for creating an access token (via token endpoint) for the introspection endpoint.
     */
    public static ?string $clientId = null;

    /**
     * The client secret used for creating an access token (via token endpoint) for the introspection endpoint.
     */
    public static ?string $clientSecret = null;

    /**
     * The cache store to use for caching access tokens and other cacheable data.
     */
    public static ?string $cacheStore = null;

    /**
     * The cache prefix to use for caching access tokens and other cacheable data.
     */
    public static ?string $cachePrefix = null;

    /**
     * How long to cache introspection results in seconds, null means never
     */
    public static ?int $cacheIntrospectionResults = null;

    public static function withUserModel(string $model): void {
        static::$userModel = $model;
    }

    public static function userModel(): string {
        return static::$userModel ?? config('passport_control.user_model');
    }

    public static function withUserModelMapper(Closure $mappingFn): void {
        static::$userModelMapper = $mappingFn;
    }

    public static function userModelMapper(): Closure {
        return static::$userModelMapper ?? config('passport_control.user_model_mapping');
    }

    public static function withUserCreationIfNotPresent(bool $enabled): void {
        static::$withUserCreationIfNotPresent = $enabled;
    }

    public static function userCreationIfNotPresent(): bool {
        return static::$withUserCreationIfNotPresent ?? config('passport_control.user_creation_if_not_present');
    }

    public static function withCachePrefix(string $prefix): void
    {
        static::$cachePrefix = $prefix;
    }

    public static function cachePrefix(): string
    {
        return static::$cachePrefix ?? config('passport_control.cache.prefix');
    }

    public static function withCacheStore(string $store): void
    {
        static::$cacheStore = $store;
    }

    public static function cacheStore(): string
    {
        return static::$cacheStore ?? config('passport_control.cache.store');
    }

    public static function withCacheIntrospectionResult(?int $timeInSeconds): void
    {
        static::$cacheIntrospectionResults = $timeInSeconds;
    }

    public static function cacheIntrospectionResult(): ?int
    {
        return static::$cacheIntrospectionResults ?? config('passport_control.cache.cache_introspection_result');
    }

    public static function withAccessTokenEndpoint(string $endpoint): void
    {
        static::$accessTokenEndpoint = $endpoint;
    }

    public static function accessTokenEndpoint(): string
    {
        return static::$accessTokenEndpoint ?? config('passport_control.access_token_endpoint');
    }

    public static function withClientID(string $clientId): void
    {
        static::$clientId = $clientId;
    }

    public static function clientID(): string
    {
        return static::$clientId ?? config('passport_control.access_token_client_id');
    }

    public static function withClientSecret(string $clientSecret): void
    {
        static::$clientSecret = $clientSecret;
    }

    public static function clientSecret(): string
    {
        return static::$clientSecret ?? config('passport_control.access_token_client_secret');
    }

    public static function loadKeyFrom(string $path): void
    {
        static::$publicKeyPath = $path;
    }

    public static function keyPath(string $file): string
    {
        $file = ltrim($file, '/\\');

        return static::$publicKeyPath
            ? rtrim(static::$publicKeyPath, '/\\').DIRECTORY_SEPARATOR.$file
            : config('passport_control.public_key_path').DIRECTORY_SEPARATOR.$file;
    }

    public static function useIntrospectEndpoint(string $endpoint): void
    {
        static::$introspectEndpoint = $endpoint;
    }

    public static function introspectEndpoint(): string
    {
        return static::$introspectEndpoint ?? config('passport_control.introspection_endpoint');
    }

    public static function inheritScopes(bool $withInheritedScopes = true): void
    {
        static::$withInheritedScopes = $withInheritedScopes;
    }

    public static function withInheritedScopes(): bool
    {
        return static::$withInheritedScopes ?? config('passport_control.inherit_scopes');
    }
}
