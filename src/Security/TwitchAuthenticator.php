<?php

namespace App\Security;

use App\Repository\UserRepository;
use App\Security\Twitch\TwitchUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TwitchAuthenticator extends AbstractAuthenticator
{

    private TwitchUserProvider $twitchUserProvider;
    private Security $security;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $httpClient;

    public function __construct(
        Security $security,
        TwitchUserProvider $twitchUserProvider,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        HttpClientInterface $httpClient,
    ) {

        $this->twitchUserProvider = $twitchUserProvider;
        $this->security = $security;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
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