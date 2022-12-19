<?php

declare(strict_types=1);

namespace App\Security\Twitch;

use App\Entity\UserTwitch;
use App\Repository\UserRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function is_subclass_of;

class TwitchUserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly HttpClientInterface $apiTwitch,
        private readonly HttpClientInterface $identityTwitch,
        private readonly string $twitchId,
        private readonly string $twitchSecret,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
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
        ])->toArray();

        $token        = $dataToken['access_token'];
        $refreshToken = $dataToken['refresh_token'];

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
        $user->setExpiresIn((new DateTime())->add(new DateInterval('PT1H')));
        $this->entityManager->flush();

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
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
