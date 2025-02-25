<?php

namespace XCoorp\PassportControl;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Parser as ParserContract;
use Lcobucci\JWT\Token\Parser;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\ResourceServer;
use XCoorp\PassportControl\Bridge\AccessTokenRepository;
use XCoorp\PassportControl\Contracts\TokenFactory as TokenFactoryContract;
use XCoorp\PassportControl\Contracts\Token as TokenContract;
use XCoorp\PassportControl\Contracts\TokenRepository as TokenRepositoryContract;
use XCoorp\PassportControl\Contracts\UserResolver as UserResolverContract;
use XCoorp\PassportControl\Factories\TokenFactory;
use XCoorp\PassportControl\Guards\TokenGuard;
use XCoorp\PassportControl\Resolver\UserResolver;

class PassportControlServiceProvider extends ServiceProvider
{
    /**
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/passport_control.php', 'passport_control'
        );

        $this->app->bind(UserResolverContract::class, UserResolver::class);

        $this->app->bind(TokenContract::class, Token::class);
        $this->app->bind(TokenFactoryContract::class, TokenFactory::class);
        $this->app->bind(TokenRepositoryContract::class, function ($app) {
            return new Repositories\TokenRepository(
                $app->make(TokenFactoryContract::class)
            );
        });

        $this->app->singleton(ParserContract::class, function () {
            return new Parser(new JoseEncoder);
        });

        $this->app->singleton(ResourceServer::class, function ($container) {
            return new ResourceServer(
                $container->make(AccessTokenRepository::class),
                $this->makeCryptKey()
            );
        });

        $this->registerGuard();
    }


    public function boot(): void
    {
        $this->registerPublishing();
    }
    /**
     * Create a CryptKey instance for the OAuth public key.
     */
    protected function makeCryptKey(): CryptKey
    {
        return new CryptKey('file://'.PassportControl::keyPath('oauth-public.key'), null);
    }

    /**
     * Register the token guard.
     * @throws BindingResolutionException
     */
    protected function registerGuard(): void
    {
        Auth::resolved(function ($auth) {
            $auth->extend('passport_control', function ($app, $name, array $config) {
                return tap($this->makeGuard($config), function ($guard) {
                    $app = $this->app;
                    /** @noinspection PhpUndefinedMethodInspection */
                    $app->refresh('request', $guard, 'setRequest');
                });
            });
        });
    }

    /**
     * @throws BindingResolutionException
     */
    protected function makeGuard(array $config): TokenGuard
    {
        return new TokenGuard(
            $this->app->make(ResourceServer::class),
            new PassportControlUserProvider(Auth::createUserProvider($config['provider']), $config['provider']),
            $this->app->make(UserResolverContract::class),
            $this->app->make(TokenRepositoryContract::class),
            $this->app->make('encrypter'),
            $this->app->make('request')
        );
    }

    /**
     * Register the package's publishable resources.
     */
    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/passport_control.php' => $this->app->configPath('passport_control.php'),
        ], ['passport_control', 'passport_control-config']);

        $this->publishes([
            __DIR__.'/../database/migrations/change_id_column_users_table.php.stub' => $this->getMigrationFileName('change_id_column_users_table.php'),
        ], ['passport_control', 'passport_control-migrations']);
    }

    /**
     * Get the migration file name with timestamp.
     */
    protected function getMigrationFileName(string $migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');
        $filesystem = $this->app->make(Filesystem::class);
        $migrationPath = $this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR;

        return Collection::make([$migrationPath])
            ->flatMap(fn ($path) => $filesystem->glob($path.'*_'.$migrationFileName))
            ->push($migrationPath."{$timestamp}_{$migrationFileName}")
            ->first();
    }
}
