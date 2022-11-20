<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\UserTwitch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiTwitch
{
    public function __construct(
        private readonly HttpClientInterface $apiTwitch,
        private readonly HttpClientInterface $identityTwitch,
        private readonly Security $security,
        private readonly string $twitchId,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function checkValidityRequest(UserTwitch $user): UserTwitch
    {
        if ($user->getAccessToken() !== null) {
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

    /**
     * @param string[] $query
     *
     * @throws TransportExceptionInterface
     */
    public function get(string $url, array $query): ResponseInterface
    {
        return $this->apiTwitch->request(method: 'GET', url: $url, options: [
            'auth_bearer' => $this->security->getUser()?->getaccessToken(),
            'headers' => ['Client-Id' => $this->twitchId],
            'query' => $query,
        ]);
    }

    /**
     * @param string[] $query
     *
     * @throws TransportExceptionInterface
     */
    public function post(string $url, array $query): ResponseInterface
    {
        return $this->apiTwitch->request(method: 'POST', url: $url, options: [
            'headers' => ['Accept' => 'application/json'],
            'query' => $query,
        ]);
    }
}
