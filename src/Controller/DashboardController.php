<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ApiTwitch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

use function array_merge;

#[Route('/dashboard')]
class DashboardController extends AbstractController
{
    public function __construct(private readonly ApiTwitch $apiTwitch)
    {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/', name: 'app_dashboard_index')]
    public function board(): Response
    {
        $usersBanList = [];
        do {
            $usersBlocks = $this->apiTwitch->get(url: 'moderation/banned', query: [
                'broadcaster_id' => $this->getUser()->getTwitchId(),
                'after' => $usersBlocks['pagination']['cursor'] ?? '',
                'first' => 100,
            ])->toArray();

            $usersBanList[] = $usersBlocks['data'];
            $cursor         = $usersBlocks['pagination']['cursor'] ?? null;
        } while ($cursor !== null);

        $userDatas = $this->apiTwitch->get(url: 'users', query: [
            'login' => $this->getUser()->getLogin(),
        ])->toArray();

        $usersBans = [];
        foreach ($usersBanList as $users) {
            if ($usersBans === []) {
                $usersBans = $users;
                continue;
            }

            $usersBans = array_merge($users, $usersBans);
        }

        return $this->render('dashboard/dashboard.html.twig', [
            'usersBans' => $usersBans,
            'userDatas' => $userDatas,
        ]);
    }
}
