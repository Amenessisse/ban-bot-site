<?php

declare(strict_types=1);

namespace App\Security\Twitch;

use App\Entity\UserTwitch;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function is_subclass_of;

class TwitchUserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly HttpClientInterface    $apiTwitch,
        private readonly HttpClientInterface    $identityTwitch,
        private readonly string                 $twitchId,
        private readonly string                 $twitchSecret,
        private readonly UrlGeneratorInterface  $urlGenerator,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository         $userRepository,
    ) {
    }

    /**
     * @throws Exception
     * @throws TransportExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function loadUserByCode(string $code): UserTwitch
    {
        $urlLoginCheck = $this->urlGenerator->generate(
            name: 'app_twitch_login_check',
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $dataToken = $this->identityTwitch->request(method: 'POST', url: 'oauth2/token', options: [
            'headers' => ['Accept' => 'application/json'],
            'query' => [
                'client_id' => $this->twitchId,
                'client_secret' => $this->twitchSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $urlLoginCheck,
            ],
        ]);

        $token        = $dataToken->toArray()['access_token'];
        $refreshToken = $dataToken->toArray()['refresh_token'];

        $validateData = $this->identityTwitch->request(
            method: 'GET',
            url: 'oauth2/validate',
            options: ['auth_bearer' => $token],
        );

        $user = $this->userRepository->findOneBy(['twitchId' => $validateData->toArray()['user_id']]);

        if (! $user instanceof UserTwitch) {
            $dataUser = $this->apiTwitch->request(method: 'GET', url: 'users', options: [
                'auth_bearer' => $token,
                'headers' => [
                    'Client-Id' => $this->twitchId,
                ],
            ]);

            $user = new UserTwitch($dataUser->toArray()['data'][0]);

            $this->entityManager->persist($user);
        }

        $user->setAccessToken($token);
        $user->setRefreshToken($refreshToken);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if ($user instanceof UserTwitch && $user->getAccessToken() !== null) {
            $response = $this->identityTwitch->request(
                method: 'GET',
                url: 'oauth2/validate',
                options: ['auth_bearer' => $user->getAccessToken()],
            );

            if ($response->getStatusCode() === Response::HTTP_UNAUTHORIZED && $user->getRefreshToken() !== null) {
                $dataToken = $this->identityTwitch->request(method: 'POST', url: 'oauth2/token', options: [
                    'headers' => ['Accept' => 'application/json'],
                    'query' => [
                        'client_id' => $this->twitchId,
                        'client_secret' => $this->twitchSecret,
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $user->getRefreshToken(),
                    ],
                ]);

                $token        = $dataToken->toArray()['access_token'];
                $refreshToken = $dataToken->toArray()['refresh_token'];

                $user->setAccessToken($token);
                $user->setRefreshToken($refreshToken);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }
        }

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === UserTwitch::class || is_subclass_of($class, UserTwitch::class);
    }

    /**
     * @throws DecodingExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function loadUserByIdentifier(string $identifier): UserInterface|UserTwitch
    {
        return self::loadUserByCode($identifier);
    }
}
