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

#[Route('/dashboard')]
class DashboardController extends AbstractController
{
    private string $twitchId;

    public function __construct(string $twitchId)
    {
        $this->twitchId = $twitchId;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/', name: 'app_dashboard_index')]
    public function board(HttpClientInterface $apiTwitch): Response
    {
        $arraytest = [];
        do {
            $usersBlocks = $apiTwitch->request(method: 'GET', url: 'moderation/banned', options: [
                'auth_bearer' => $this->getUser()->getAccessToken(),
                'headers' => [
                    'Client-Id' => $this->twitchId
                ],
                'query' => [
                    'broadcaster_id' => $this->getUser()->getTwitchId(),
                    'after' => $usersBlocks['pagination']['cursor'] ?? '',
                    'first' => 100,
                ]
            ])->toArray();

            $arraytest[] = $usersBlocks['data'];
            $cursor = $usersBlocks['pagination']['cursor'] ?? null;
        } while ($cursor !== null);

        $arraymerge = [];
        foreach ($arraytest as $array) {
            if ($arraymerge === []) {
                $arraymerge = $array;
                continue;
            }

            $arraymerge = array_merge($array, $arraymerge);
        }

        return $this->render('dashboard/dashboard.html.twig', [
            'usersBlocks' => $arraymerge,
        ]);
    }
}