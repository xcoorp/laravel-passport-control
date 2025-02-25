<?php

namespace XCoorp\PassportControl\Guards;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
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
use XCoorp\PassportControl\Contracts\TokenRepository;
use XCoorp\PassportControl\Contracts\UserResolver;
use XCoorp\PassportControl\Exceptions\UnauthorizedException;

class TokenGuard implements Guard
{
    use GuardHelpers, Macroable;

    /**
     * The currently authenticated user.
     *
     * @var Authenticatable|null
     */
    protected $user = null;

    public function __construct(
        protected ResourceServer $server,
        /** @var UserProvider */
        protected $provider,
        protected UserResolver $userResolver,
        protected TokenRepository $tokens,
        protected Encrypter $encrypter,
        protected Request $request
    ) {
    }

    /**
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
     * @throws Throwable
     */
    public function validate(array $credentials = []): bool
    {
        return ! is_null((new static(
            $this->server,
            $this->provider,
            $this->userResolver,
            $this->tokens,
            $this->encrypter,
            $credentials['request'],
        ))->user());
    }

    /**
     * @throws Throwable
     */
    protected function authenticateViaBearerToken(Request $request): ?Authenticatable
    {
        if (! $psr = $this->getPsrRequestViaBearerToken($request)) {
            return null;
        }

        $token = $this->tokens->introspect($psr->getAttribute('oauth_access_token_id'));

        if (! $token
            || ! $token->isActive()
            || $token->expiresAt()->diffInSeconds() >= 0
            || $token->notBefore() !== null && $token->notBefore()->diffInSeconds() < 0
            || $psr->getAttribute('oauth_user_id') !== $token->user()
        ) {
            return null;
        }

        $user = $this->userResolver->resolveUserByToken($token, $this->provider);

        /** @noinspection PhpUndefinedMethodInspection */
        return $user?->withAccessToken($token);
    }

    /**
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
        } catch (OAuthServerException) {
            $request->headers->set('Authorization', '');

            Container::getInstance()->make(ExceptionHandler::class)
                ->report(UnauthorizedException::notLoggedIn());
        }

        return null;
    }

    public function setRequest(Request $request): static
    {
        $this->request = $request;

        return $this;
    }
}
