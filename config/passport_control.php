<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Configuration
    |--------------------------------------------------------------------------
    |
    | This package, can create a user on your resource server, once the request
    | is authenticated and in case there is not a user record yet. For this
    | case you can set a custom User model here and decide weather you want to implement
    | this feature or not.
    */
    'user_model' => \App\Models\User::class,
    'user_creation_if_not_present' => env('PASSCONTROL_CREATE_USER', false),
    'user_model_mapping' => function (\XCoorp\PassportControl\Token $token) {
        return [
            'id' => $token->user(),
            'email' => $token->username(),
        ];
    },

    /*
    |--------------------------------------------------------------------------
    | Introspection URL of your Laravel Passport server
    |--------------------------------------------------------------------------
    |
    | This value is the URL of the introspection endpoint of your Laravel Passport
    | server. This value is used to validate the access token of the incoming
    | request. You may change this value to the URL of your Laravel Passport
    | server's introspection endpoint.
    */
    'introspection_endpoint' => env('PASSCONTROL_INTROSPECTION_ENDPOINT', 'http://localhost/oauth/introspect'),

    /*
    |--------------------------------------------------------------------------
    | Token Configuration
    |--------------------------------------------------------------------------
    |
    | These values are the access_token_endpoint, access_token_client_id and access_token_client_secret we need. You can get those values
    | from your Laravel Passport server by creating a new CLIENT CREDENTIAL GRANT client.
    | We need this since access to the introspection endpoint is not public and only allowed for client credentials grant clients.
     */
    'access_token_endpoint' => env('PASSCONTROL_ACCESS_TOKEN_ENDPOINT', 'http://localhost/oauth/token'),
    'access_token_client_id' => env('PASSCONTROL_ACCESS_TOKEN_CLIENT_ID', ''),
    'access_token_client_secret' => env('PASSCONTROL_ACCESS_TOKEN_CLIENT_SECRET', ''),

    /*
     |--------------------------------------------------------------------------
     | Public Key path to your Laravel Passport server's public key
     |--------------------------------------------------------------------------
     | This value is the path to the public key of your Laravel Passport server.
     | It MUST be the public key which corresponds to the private key used to sign the access tokens.
     | This value is used to validate the access token signature of the incoming request.
     */
    'public_key_path' => env('PASSCONTROL_PUBLIC_KEY_PATH', storage_path()),

    /*
     | -------------------------------------------------------------------------
     | Inherited Scopes
     | -------------------------------------------------------------------------
     | In Laravel Passport, you can configure that the scopes are inherited from the parent client,
     | you do this by setting the Passport::$withInheritedScopes to true. If you have set this to true,
     | you should also set this value to true. Otherwise, keep this value as is.
     */
    'inherit_scopes' => env('PASSCONTROL_INHERIT_SCOPES', false),

    'cache' => [
        /*
        |--------------------------------------------------------------------------
        | Cache Store
        |--------------------------------------------------------------------------
        | Cache store to use for caching access tokens and other cacheable data.
        */
        'store' => env('PASSCONTROL_CACHE_STORE', env('CACHE_STORE', 'file')),

        /*
        |--------------------------------------------------------------------------
        | Cache Prefix
        |--------------------------------------------------------------------------
        | Cache prefix to use for caching access tokens and other cacheable data.
        */
        'prefix' => env('PASSCONTROL_CACHE_PREFIX', 'xcoorp_passcontrol_'),

        /*
        |--------------------------------------------------------------------------
        | Cache Introspection Result
        |--------------------------------------------------------------------------
        | Cache the result from the introspection api for a token for a certain amount of time (in seconds).
        | This is useful to reduce the number of requests to the introspection endpoint.
        | Set this value to null to disable caching.
        */
        'cache_introspection_result' => null
    ],
];
