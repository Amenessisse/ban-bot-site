<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HomePageController extends AbstractController
{

    private string $twitchId;

    public function __construct(string $twitchId)
    {
        $this->twitchId = $twitchId;
    }

    #[Route('/', name: 'home_page')]
    public function index(): Response
    {
        return $this->render('home/home.html.twig');
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/dashboard', name: 'dashboard')]
    public function board(HttpClientInterface $apiTwitch): Response
    {
        $usersBlocks = $apiTwitch->request(method: 'GET', url: 'moderation/banned', options: [
            'auth_bearer' => $this->getUser()->getAccessToken(),
            'headers' => [
                'Client-Id' => $this->twitchId
            ],
            'query' => [
                'broadcaster_id' => $this->getUser()->getTwitchId(),
            ]
        ])->toArray();

        return $this->render('dashboard/dashboard.html.twig', [
            'usersBlocks' => $usersBlocks['data'],
        ]);
    }

}
