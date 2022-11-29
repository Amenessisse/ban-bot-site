<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\UserTwitch;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

use function hash;
use function json_encode;

class ApiTwitch
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly HttpClientInterface $apiTwitch,
        private readonly HttpClientInterface $identityTwitch,
        private readonly Security $security,
        private readonly string $twitchSecret,
        private readonly string $twitchId,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function checkValidateToken(UserTwitch $user): UserTwitch
    {
        $now = new DateTime();

        if ($user->getExpiresIn() <= $now) {
            $response = $this->identityTwitch->request(
                method: 'GET',
                url: 'oauth2/validate',
                options: ['auth_bearer' => $user->getAccessToken()],
            );

            if ($response->getStatusCode() !== Response::HTTP_UNAUTHORIZED && $user->getRefreshToken() !== null) {
                return $user;
            }

            $this->refreshToken($user);
        }

        return $user;
    }

    /** @throws Exception
     * @throws TransportExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function refreshToken(UserTwitch $user): void
    {
        $response = $this->identityTwitch->request(method: 'POST', url: 'oauth2/token', options: [
            'headers' => ['Accept' => 'application/json'],
            'query' => [
                'client_id' => $this->twitchId,
                'client_secret' => $this->twitchSecret,
                'grant_type' => 'refresh_token',
                'refresh_token' => $user->getRefreshToken(),
            ],
        ]);

        if ($response->getStatusCode() === Response::HTTP_BAD_REQUEST) {
            throw new Exception('Impossible to refresh token');
        }

        $token        = $response->toArray()['access_token'];
        $refreshToken = $response->toArray()['refresh_token'];

        $user->setAccessToken($token);
        $user->setRefreshToken($refreshToken);
        $user->setExpiresIn((new DateTime())->add(new DateInterval('PT1H')));
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @param string[] $query
     *
     * @throws Exception
     * @throws TransportExceptionInterface|DecodingExceptionInterface
     * @throws InvalidArgumentException
     */
    public function get(string $url, array $query): array
    {
        $user = $this->security->getUser();

        if (! $user instanceof UserTwitch) {
            throw new Exception('user bad authent');
        }

        try {
            $this->checkValidateToken($user);
        } catch (TransportException $exception) {
            throw new Exception('Your token is not valid : ' . $exception->getMessage());
        }

        return $this->cache->get(
            hash('sha256', $url . json_encode($query)),
            function (CacheItemInterface $cacheItem) use ($url, $user, $query): array {
                $cacheItem->expiresAfter(60);

                $response = $this->apiTwitch->request(method: 'GET', url: $url, options: [
                    'auth_bearer' => $user->getAccessToken(),
                    'headers' => ['Client-Id' => $this->twitchId],
                    'query' => $query,
                ]);

                if ($response->getStatusCode() === Response::HTTP_UNAUTHORIZED) {
                    try {
                        $this->refreshToken($user);
                    } catch (TransportExceptionInterface $exception) {
                        throw new Exception('impossible refresh token : ' . $exception->getMessage());
                    }
                }

                return $response->toArray();
            },
        );
    }

    /**
     * @param string[] $query
     *
     * @throws TransportExceptionInterface
     * @throws Exception|DecodingExceptionInterface
     */
    public function post(string $url, array $query): ResponseInterface
    {
        $user = $this->security->getUser();

        if (! $user instanceof UserTwitch) {
            throw new Exception('user bad authent');
        }

        try {
            $this->checkValidateToken($user);
        } catch (TransportException $exception) {
            throw new Exception('Your token is not valid : ' . $exception->getMessage());
        }

        $response = $this->apiTwitch->request(method: 'POST', url: $url, options: [
            'headers' => ['Accept' => 'application/json'],
            'query' => $query,
        ]);

        if ($response->getStatusCode() === Response::HTTP_UNAUTHORIZED) {
            try {
                $this->refreshToken($user);
            } catch (TransportExceptionInterface $exception) {
                throw new Exception('impossible refresh token : ' . $exception->getMessage());
            }
        }

        return $response;
    }

    public function getImageProfile(UserTwitch $user): string
    {
        $userDatas = $this->get(url: 'users', query: ['id' => $user->getTwitchId()]);

        return $userDatas['data'][0]['profile_image_url'];
    }
}
