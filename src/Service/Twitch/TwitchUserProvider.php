<?php

namespace App\Service\Twitch;

use App\Entity\User;
use Exception;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @method UserInterface loadUserByIdentifier(string $identifier)
 */
class TwitchUserProvider implements UserProviderInterface
{
    private HttpClientInterface $httpClient;
    private string $twitchId;
    private string $twitchSecret;
    private UrlGeneratorInterface $urlGenerator;


    public function __construct(
        HttpClientInterface $httpClient,
        string $twitchId,
        string $twitchSecret,
        UrlGeneratorInterface $urlGenerator,
    ) {
        $this->httpClient = $httpClient;
        $this->twitchId = $twitchId;
        $this->twitchSecret = $twitchSecret;
        $this->urlGenerator = $urlGenerator;

    }

    public function refreshUser(UserInterface $user)
    {
    }

    public function supportsClass(string $class)
    {
        // TODO: Implement supportsClass() method.
    }

    /**
     * @throws Exception
     * @throws TransportExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function loadUserByUsername(string $username)
    {
        $urlDashboard = $this->urlGenerator->generate(name: 'dashboard', referenceType: UrlGeneratorInterface::ABSOLUTE_URL);

        $url = 'https://id.twitch.tv/oauth2/token?client_id=' . $this->twitchId . '&client_secret=' . $this->twitchSecret . '&code=' . $username . '&grant_type=authorization_code&redirect_uri=' . $urlDashboard;

        $response = $this->httpClient->request(method: 'POST', url: $url, options: [
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        $token = $response->toArray()['access_token'];

        $response = $this->httpClient->request(method: 'GET', url: 'https://id.twitch.tv/oauth2/validate', options: [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return new User($response->toArray());
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement @method UserInterface loadUserByIdentifier(string $identifier)
    }
}