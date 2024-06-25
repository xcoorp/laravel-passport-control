<?php

namespace XCoorp\PassportControl;

class PassportControl
{
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
     * Set the cache prefix.
     */
    public static function withCachePrefix(string $prefix): void
    {
        static::$cachePrefix = $prefix;
    }

    /**
     * Get the cache prefix.
     */
    public static function cachePrefix(): string
    {
        return static::$cachePrefix ?? config('passport_control.cache.prefix');
    }

    /**
     * Set the cache store.
     */
    public static function withCacheStore(string $store): void
    {
        static::$cacheStore = $store;
    }

    /**
     * Get the cache store.
     */
    public static function cacheStore(): string
    {
        return static::$cacheStore ?? config('passport_control.cache.store');
    }

    /**
     * Set the access token endpoint.
     */
    public static function withAccessTokenEndpoint(string $endpoint): void
    {
        static::$accessTokenEndpoint = $endpoint;
    }

    /**
     * Get the access token endpoint.
     */
    public static function accessTokenEndpoint(): string
    {
        return static::$accessTokenEndpoint ?? config('passport_control.access_token_endpoint');
    }

    /**
     * Set the client ID
     */
    public static function withClientID(string $clientId): void
    {
        static::$clientId = $clientId;
    }

    /**
     * Get the client ID
     */
    public static function clientID(): string
    {
        return static::$clientId ?? config('passport_control.access_token_client_id');
    }

    /**
     * Set the client secret
     */
    public static function withClientSecret(string $clientSecret): void
    {
        static::$clientSecret = $clientSecret;
    }

    /**
     * Get the client secret
     */
    public static function clientSecret(): string
    {
        return static::$clientSecret ?? config('passport_control.access_token_client_secret');
    }

    /**
     * Set the storage location of the public key.
     */
    public static function loadKeyFrom(string $path): void
    {
        static::$publicKeyPath = $path;
    }

    /**
     * The location of the encryption keys.
     */
    public static function keyPath(string $file): string
    {
        $file = ltrim($file, '/\\');

        return static::$publicKeyPath
            ? rtrim(static::$publicKeyPath, '/\\').DIRECTORY_SEPARATOR.$file
            : config('passport_control.public_key_path').DIRECTORY_SEPARATOR.$file;
    }

    /**
     * Set the introspect endpoint.
     */
    public static function useIntrospectEndpoint(string $endpoint): void
    {
        static::$introspectEndpoint = $endpoint;
    }

    /**
     * Get the introspect endpoint.
     */
    public static function introspectEndpoint(): string
    {
        return static::$introspectEndpoint ?? config('passport_control.introspection_endpoint');
    }

    /**
     * Set if the scope should inherit its parent scope.
     */
    public static function inheritScopes(bool $withInheritedScopes = true): void
    {
        static::$withInheritedScopes = $withInheritedScopes;
    }

    /**
     * Get if the scope should inherit its parent scope.
     */
    public static function withInheritedScopes(): bool
    {
        return static::$withInheritedScopes ?? config('passport_control.inherit_scopes');
    }
}
