<?php

namespace App\Security;

use App\Service\Metric;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * @see https://symfony.com/doc/current/security/custom_authenticator.html
 */
class ApiKeyAuthenticator extends AbstractAuthenticator
{
    public const AUTH_HEADER = 'Authorization';
    public const AUTH_HEADER_PREFIX = 'Apikey ';

    public function __construct(
        private readonly Metric $metric,
    ) {}


    /**
     * Called on every request to decide if this authenticator should be used for the request.
     *
     * Returning `false` will cause this authenticator to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        if ($request->headers->has(self::AUTH_HEADER)) {
            $authHeader = $request->headers->get(self::AUTH_HEADER);

            return !is_null($authHeader) && str_starts_with($authHeader, self::AUTH_HEADER_PREFIX);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(Request $request): Passport
    {
        $authHeader = $request->headers->get(self::AUTH_HEADER);

        if (null === $authHeader) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new CustomUserMessageAuthenticationException('No authentication header provided');
        }

        $apiKey = substr($authHeader, strlen(self::AUTH_HEADER_PREFIX));

        if (!$apiKey) {
            throw new CustomUserMessageAuthenticationException('No API key provided');
        }

        return new SelfValidatingPassport(new UserBadge($apiKey));
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->metric->counter('AuthenticationFailure', null, $this);

        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
