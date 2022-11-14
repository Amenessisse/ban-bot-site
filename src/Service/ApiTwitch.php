<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiTwitch
{
    public function __construct(
        private readonly HttpClientInterface $apiTwitch,
        private readonly Security $security,
        private readonly string $twitchId,
    ) {
    }

    /**
     * @param string[] $query
     *
     * @throws TransportExceptionInterface
     */
    public function get(string $url, array $query): ResponseInterface
    {
        $result = $this->apiTwitch->request(method: 'GET', url: $url, options: [
            'auth_bearer' => $this->security->getUser()?->getaccessToken(),
            'headers' => ['Client-Id' => $this->twitchId],
            'query' => $query,
        ]);

        return $result;
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
