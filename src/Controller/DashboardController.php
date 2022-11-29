<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserTwitch;
use App\Repository\BannedUserRepository;
use App\Service\ApiTwitch;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard')]
class DashboardController extends AbstractController
{
    public function __construct(
        private readonly BannedUserRepository $bannedUserRepository,
        private readonly ApiTwitch $apiTwitch,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Route('/', name: 'app_dashboard_index')]
    public function index(): Response
    {
        $user  = $this->getUser();
        $count = $this->bannedUserRepository->countAllUsers();

        if ($user instanceof  UserTwitch) {
            $imageProfile = $this->apiTwitch->getImageProfile($user);
        } else {
            $imageProfile = '';
        }

        return $this->render('dashboard/dashboard.html.twig', [
            'count'        => $count,
            'imageProfile' => $imageProfile,
        ]);
    }
}
