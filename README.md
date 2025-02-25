<p align="center">
<a href="LICENSE"><img alt="Software License" src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square"></a>
<a href="composer.json"><img alt="Laravel Version Requirements" src="https://img.shields.io/badge/laravel-~11.0-gray?logo=laravel&style=flat-square&labelColor=F05340&logoColor=white"></a>
</p>


## Introduction
Passport Control is a Laravel Passport compatible, OAuth2 resource server package.

This package is meant to be used in a Laravel application that is acting as a
[Resource Server](https://www.oauth.com/oauth2-servers/the-resource-server/)
**only** e.g. an API server that is meant to authenticate against an Authentication Server 
running [Laravel Passport](https://laravel.com/docs/11.x/passport).

This project does not depend on the Laravel Passport package itself, but it does share some of the
same concepts and interfaces.

## Table of Contents

- [Installation](#installation)
    - [Pre-requisites](#pre-requisites) 
    - [Composer](#composer)
    - [Configuration](#configuration)
- [Usage](#usage)
    - [Advanced Usage](#advanced-usage) 
- [Testing](#testing)
- [Code of Conduct](#code-of-conduct)
- [License](#license)

## Installation

The installation of this package is quite straightforward. But before you start, make sure you have
the pre-requisites in place.

### Pre-requisites

This package assumes, you have already installed and configured Laravel Passport in your Laravel application.
Once you have done this, you need to create a new client with client credentials grant type  **on your Passport Server**.

Follow the [official Laravel Passport documentation](https://laravel.com/docs/11.x/passport#client-credentials-grant-tokens) on how to do this.

Once you have done this, set the `PASSCONTROL_INTROSPECTION_CLIENT_ID` and `PASSCONTROL_INTROSPECTION_CLIENT_SECRET` environment variables
accordingly.

As of now, Laravel does not Ship with an introspection endpoint. So you need to create one manually.
You can do this by installing the [Laravel Passport Introspection](https://github.com/xcoorp/laravel-passport-introspection) 
package **on your Passport Server**:

```bash
composer require xcoorp/laravel-passport-introspection
```

Once you have done this an introspection Endpoint will be available for your Laravel application, 
you can now continue with the installation of this package.

> [!TIP]
> If you need more information on how to install and configure the introspection package, check out its [documentation](https://github.com/xcoorp/laravel-passport-introspection/README.md).

### Composer
You can simply install the package via composer:

```bash
composer require xcoorp/laravel-passport-control
```

Once the package is installed, you should publish the configuration and migration files:

```bash
php artisan vendor:publish --provider="XCoorp\PassportControl\PassportControlServiceProvider"
```

and run the migrations:

```bash
php artisan migrate
```

### Configuration

This package comes with a configuration file that you can and should customize to your needs.
The configuration file is located at `config/passport-control.php`.

All configuration options (except the User Model) can be also configured via environment variables instead of the configuration file.

The following Environment variables are available:

| Environment Variable                   | Value                                                                                                                                              | Default                           |
|----------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------|-----------------------------------|
| PASSCONTROL_INTROSPECTION_ENDPOINT     | The Introspection Endpoint URL. Usually $YOUR_PASSPORT_SERVER/oauth/introspect                                                                     | http://localhost/oauth/introspect |
| PASSCONTROL_ACCESS_TOKEN_ENDPOINT      | The Token Endpoint URL to receive a new access token. Usually $YOUR_PASSPORT_SERVER/oauth/token                                                    | http://localhost/oauth/token      |
| PASSCONTROL_ACCESS_TOKEN_CLIENT_ID     | Client Id needed to get the access token for introspection. Check [Pre-requisites](#pre-requisites) for more information.                          |                                   |
| PASSCONTROL_ACCESS_TOKEN_CLIENT_SECRET | Client Secret needed to get the access token for introspection. Check [Pre-requisites](#pre-requisites) for more information.                      |                                   |
| PASSCONTROL_PUBLIC_KEY_PATH            | Path where the public key file `oauth-public.key` is stored. NOTE: Specify the path without the filename.                                          | Laravel Storage Path (storage)    |
| PASSCONTROL_INHERIT_SCOPES             | In Laravel Passport, you can configure that the scopes are inherited from the parent client, set to true if you have passport configured that way. | False                             |
| PASSCONTROL_CACHE_STORE                | Cache Storage used for storing the Client Credential Access Token                                                                                  | `CACHE_STORE`, file               |
| PASSCONTROL_CACHE_PREFIX               | Cache Prefix used for storing the Client Credential Access Token                                                                                   | xcoorp_passcontrol_               |
| PASSCONTROL_CACHE_INTROSPECTION_RESULT | Cache Introspection Endpoint results to a token for the given time (but never longer then the tokens expiry)                                       | null (Don't cache)                |

## Usage

After you have installed and configured the package, you need to configure your auth guard to be passport_control.
In your `config/auth.php` file, you can add a new guard configuration like this:

```php
'guards' => [
    'api' => [
        'driver' => 'passport_control',
        'provider' => 'users',
    ],
],
```

That's it, now you can protect your API routes as usual by using the `auth:api` middleware.

```php
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
```

You should also check out the other 2 middlewares provided by this package:

- `CheckScopes` - Check if the authenticated user's token has the given scopes.
- `CheckCredentialType` - Check the credential type of the authenticated user's token. (e.g "pkce", "client_credentials", "password")

### Advanced Usage

This package makes use of Laravel's Dependency Injection, so you can easily override the default classes used by this package, for example
custom user resolvers (for creating users when they do not exist in your db yet) or custom Token class if you need to extend the default functionality.

Checkout the `PassportControlServiceProvider` class for more information.

## Testing

Functionality of this package is tested with [Pest PHP](https://pestphp.com/).
You can run the tests with:

``` bash
composer test
```

## Code of Conduct

In order to ensure that the community is welcoming to all, please review and abide by
the [Code of Conduct](CODE_OF_CONDUCT.md).

## Security Vulnerabilities

Please review the [security policy](SECURITY.md) on how to report security vulnerabilities.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
