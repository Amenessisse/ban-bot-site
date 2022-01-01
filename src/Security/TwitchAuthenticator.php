<?php

namespace App\Security;

use App\Security\Twitch\TwitchUserProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class TwitchAuthenticator extends AbstractAuthenticator
{
    private TwitchUserProvider $twitchUserProvider;

    public function __construct(TwitchUserProvider $twitchUserProvider)
    {
        $this->twitchUserProvider = $twitchUserProvider;
    }

    public function supports(Request $request): ?bool
    {
        return $request->query->get('code') !== null;
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $code = $request->query->get('code');

        if ($code !== null) {
            return new SelfValidatingPassport(
                new UserBadge($code, function () use ($code): UserInterface {
                    return $this->twitchUserProvider->loadUserByCode($code);
                })
            );
        }

        throw new CustomUserMessageAuthenticationException('No API token provided');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?JsonResponse
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
