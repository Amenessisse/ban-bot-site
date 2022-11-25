<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserTwitch;
use App\Service\ApiTwitch;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Throwable;

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
     * @throws Exception
     */
    #[Route('/', name: 'app_dashboard_index')]
    public function board(): Response
    {
        $user = $this->getUser();

        if (! $user instanceof UserTwitch) {
            throw new Exception('user not user');
        }

        $usersBanList = [];
        do {
            try {
                $usersBlocks = $this->apiTwitch->get(url: 'moderation/banned', query: [
                    'broadcaster_id' => $user->getTwitchId(),
                    'after' => $usersBlocks['pagination']['cursor'] ?? '',
                    'first' => 100,
                ])->toArray();
            } catch (Throwable $exception) {
                $this->addFlash('message_error', 'Token error : ' . $exception->getMessage());

                return $this->redirectToRoute('app_logout');
            }

            $usersBanList[] = $usersBlocks['data'];
            $cursor         = $usersBlocks['pagination']['cursor'] ?? null;
        } while ($cursor !== null);

        $userDatas = $this->apiTwitch->get(url: 'users', query: ['id' => $user->getTwitchId()])->toArray();

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
            'userDatas' => $userDatas['data'][0],
        ]);
    }
}
