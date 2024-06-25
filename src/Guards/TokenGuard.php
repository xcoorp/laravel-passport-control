<?php

namespace XCoorp\PassportControl\Guards;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\Macroable;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Throwable;
use XCoorp\PassportControl\Exceptions\UnauthorizedException;
use XCoorp\PassportControl\PassportControlUserProvider;
use XCoorp\PassportControl\Repositories\TokenRepository;

class TokenGuard implements Guard
{
    use GuardHelpers, Macroable;

    /**
     * The resource server instance.
     */
    protected ResourceServer $server;

    /**
     * The user provider implementation.
     *
     * @var PassportControlUserProvider
     */
    protected $provider;

    /**
     * The token repository instance.
     */
    protected TokenRepository $tokens;

    /**
     * The encrypter implementation.
     */
    protected Encrypter $encrypter;

    /**
     * The request instance.
     */
    protected Request $request;

    /**
     * The currently authenticated user.
     *
     * @var Authenticatable|null
     */
    protected $user = null;

    /**
     * Create a new token guard instance.
     */
    public function __construct(
        ResourceServer $server,
        PassportControlUserProvider $provider,
        TokenRepository $tokens,
        Encrypter $encrypter,
        Request $request
    ) {
        $this->server = $server;
        $this->provider = $provider;
        $this->tokens = $tokens;
        $this->encrypter = $encrypter;
        $this->request = $request;
    }

    /**
     * Get the user for the incoming request.
     *
     * @throws Throwable
     */
    public function user(): ?Authenticatable
    {
        if (! is_null($this->user)) {
            return $this->user;
        }

        return $this->user = $this->authenticateViaBearerToken($this->request);
    }

    /**
     * Validate a user's credentials.
     *
     * @throws Throwable
     */
    public function validate(array $credentials = []): bool
    {
        return ! is_null((new static(
            $this->server,
            $this->provider,
            $this->tokens,
            $this->encrypter,
            $credentials['request'],
        ))->user());
    }

    /**
     * Authenticate the incoming request via the Bearer token.
     *
     * @throws Throwable
     */
    protected function authenticateViaBearerToken(Request $request): ?Authenticatable
    {
        if (! $psr = $this->getPsrRequestViaBearerToken($request)) {
            return null;
        }

        // If the access token is valid, we introspect the token to get the user details
        $token = $this->tokens->introspect($psr->getAttribute('oauth_access_token_id'));

        if (! $token || ! $token->isActive() || $token->expiresAt()->diffInSeconds() >= 0 || $psr->getAttribute('oauth_user_id') !== $token->user()) {
            return null;
        }

        // If the access token is valid we will retrieve the user according to the user ID
        // associated with the token. We will use the provider implementation which may
        // be used to retrieve users from Eloquent. Next, we'll be ready to continue.
        $user = $this->provider->retrieveById(
            $token->user() ?: null
        );

        /** @noinspection PhpUndefinedMethodInspection */
        return $user?->withAccessToken($token);
    }

    /**
     * Authenticate and get the incoming PSR-7 request via the Bearer token.
     *
     * @throws BindingResolutionException
     * @throws Throwable
     */
    protected function getPsrRequestViaBearerToken(Request $request): ?ServerRequestInterface
    {
        // First, we will convert the Symfony request to a PSR-7 implementation which will
        // be compatible with the base OAuth2 library. The Symfony bridge can perform a
        // conversion for us to a new Nyholm implementation of this PSR-7 request.
        $psr = (new PsrHttpFactory(
            new Psr17Factory,
            new Psr17Factory,
            new Psr17Factory,
            new Psr17Factory
        ))->createRequest($request);

        try {
            return $this->server->validateAuthenticatedRequest($psr);
        } catch (OAuthServerException $e) {
            $request->headers->set('Authorization', '', true);

            Container::getInstance()->make(ExceptionHandler::class)
                ->report(UnauthorizedException::notLoggedIn());
        }

        return null;
    }

    /**
     * Set the current request instance.
     *
     * @return $this
     */
    public function setRequest(Request $request): static
    {
        $this->request = $request;

        return $this;
    }
}
