<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\UserTwitch;
use App\Security\Twitch\TwitchUserProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

use function strtr;

class TwitchAuthenticator extends AbstractAuthenticator
{
    public function __construct(private TwitchUserProvider $twitchUserProvider)
    {
    }

    public function supports(Request $request): bool|null
    {
        return $request->query->get('code') !== null;
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $code = $request->query->get('code');

        if ($code !== null) {
            return new SelfValidatingPassport(
                new UserBadge($code, function () use ($code): UserTwitch {
                    return $this->twitchUserProvider->loadUserByCode($code);
                }),
            );
        }

        throw new CustomUserMessageAuthenticationException('No API token provided');
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName,
    ): Response|null {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
