<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\BannedUser;
use App\Entity\UserTwitch;
use App\Service\ApiTwitch;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Throwable;

use function array_merge;

/** @IsGranted("ROLE_USER") */
#[Route('/dashboard')]
class BanListController extends AbstractController
{
    public function __construct(
        private readonly ApiTwitch $apiTwitch,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    #[Route('/banlist')]
    public function index(Request $request): RedirectResponse|Response
    {
        $user = $this->getUser();

        if (! $user instanceof UserTwitch) {
            throw new Exception('user not user');
        }

        $usersBanList = [];

        $cursor = $request->get('cursor');

        try {
            $usersBlocks = $this->apiTwitch->get(url: 'moderation/banned', query: [
                'broadcaster_id' => $user->getTwitchId(),
                'after' => $cursor ?? '',
                'first' => 50,
            ]);
        } catch (Throwable $exception) {
            $this->addFlash('message_error', 'Token error : ' . $exception->getMessage());

            return $this->redirectToRoute('app_logout');
        }

        $usersBanList[] = $usersBlocks['data'];
        $cursor         = $usersBlocks['pagination']['cursor'] ?? null;

        $userDatas = $this->apiTwitch->get(url: 'users', query: ['id' => $user->getTwitchId()]);

        $usersBans = [];
        foreach ($usersBanList as $users) {
            if ($usersBans === []) {
                $usersBans = $users;
                continue;
            }

            $usersBans = array_merge($users, $usersBans);
        }

        $imageProfile = $this->apiTwitch->getImageProfile($user);

        return $this->render('dashboard/banlist.html.twig', [
            'cursor'       => $cursor,
            'imageProfile' => $imageProfile,
            'usersBans'    => $usersBans,
            'userDatas'    => $userDatas['data'][0],
        ]);
    }

    /** @throws Exception */
    #[Route('/share', name: 'app_banlist_share')]
    public function shareListBannedUsers(): RedirectResponse
    {
        $user = $this->getUser();

        if (! $user instanceof UserTwitch) {
            throw new Exception('Bad user');
        }

        $banListUsers = [];
        do {
            try {
                $usersBlocks = $this->apiTwitch->get(url: 'moderation/banned', query: [
                    'broadcaster_id' => $user->getTwitchId(),
                    'after' => $usersBlocks['pagination']['cursor'] ?? '',
                    'first' => 10,
                ]);
            } catch (Throwable $exception) {
                $this->addFlash('message_error', 'Token error : ' . $exception->getMessage());

                return $this->redirectToRoute('app_logout');
            }

            $banListUsers = [...$banListUsers, ...$usersBlocks['data']];
            $cursor       = $usersBlocks['pagination']['cursor'] ?? null;
        } while ($cursor !== null);

        foreach ($banListUsers as $userBan) {
            // TODO : condition d'enregistrement (si user a déja enregistré, alors ne pas prendre en compte)
            $user = new BannedUser();
            $user->setTwitchId($userBan['user_id']);
            $user->setLogin($userBan['user_login']);
            $user->setUsername($userBan['user_name']);

            //TODO : condition de comptage
            $user->setCounter(1);

            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Partage réussie ! \o/');

        return $this->redirectToRoute('app_banlist_index');
    }
}
