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
use XCoorp\PassportControl\Contracts\UserContract;
use XCoorp\PassportControl\Guards\TokenGuard;
use XCoorp\PassportControl\Repositories\TokenRepository;

class PassportControlServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     *
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/passport_control.php', 'passport_control'
        );

        $this->registerJWTParser();
        $this->registerResourceServer();
        $this->registerGuard();
    }

    /**
     * Bootstrap any package services.
     *
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerModelBindings();
    }

    /**
     * Register the JWT Parser.
     */
    protected function registerJWTParser(): void
    {
        $this->app->singleton(ParserContract::class, function () {
            return new Parser(new JoseEncoder);
        });
    }

    protected function registerResourceServer(): void
    {
        $this->app->singleton(ResourceServer::class, function ($container) {
            return new ResourceServer(
                $container->make(AccessTokenRepository::class),
                $this->makeCryptKey()
            );
        });
    }

    /**
     * Create a CryptKey instance.
     */
    protected function makeCryptKey(): CryptKey
    {
        return new CryptKey('file://'.PassportControl::keyPath('oauth-public.key'), null);
    }

    /**
     * Register the token guard.
     *
     * @throws BindingResolutionException
     */
    protected function registerGuard(): void
    {
        Auth::resolved(function ($auth) {
            $auth->extend('passport_control', function ($app, $name, array $config) {
                return tap($this->makeGuard($config), function ($guard) {
                    /** @noinspection PhpUndefinedFunctionInspection */
                    app()->refresh('request', $guard, 'setRequest');
                });
            });
        });
    }

    /**
     * Make an instance of the token guard.
     *
     * @throws BindingResolutionException
     */
    protected function makeGuard(array $config): TokenGuard
    {
        return new TokenGuard(
            $this->app->make(ResourceServer::class),
            new PassportControlUserProvider(Auth::createUserProvider($config['provider']), $config['provider']),
            $this->app->make(TokenRepository::class),
            $this->app->make('encrypter'),
            $this->app->make('request')
        );
    }

    /**
     * Register the package's publishable resources.
     *
     * @throws BindingResolutionException
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
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @throws BindingResolutionException
     */
    protected function getMigrationFileName(string $migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make([$this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR])
            ->flatMap(fn ($path) => $filesystem->glob($path.'*_'.$migrationFileName))
            ->push($this->app->databasePath()."/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }

    protected function registerModelBindings(): void
    {
        $this->app->bind(UserContract::class, fn ($app) => $app->make($app->config['passport_control.user_model']));
    }
}
