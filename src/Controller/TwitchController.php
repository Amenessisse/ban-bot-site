<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserTwitch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class TwitchController extends AbstractController
{
    public function __construct(
        private readonly string $twitchDomain,
        private readonly string $twitchId,
        private readonly string $twitchScope,
    ) {
    }

    #[Route('/login/twitch', name: 'app_twitch_login')]
    public function index(#[CurrentUser] UserTwitch|null $user, CsrfTokenManagerInterface $csrfToken): RedirectResponse
    {
        if (! $user instanceof UserTwitch) {
            $urlLoginCheck = $this->generateUrl(
                route: 'app_twitch_login_check',
                referenceType: UrlGeneratorInterface::ABSOLUTE_URL,
            );

            return new RedirectResponse(
                url: $this->twitchDomain . 'oauth2/authorize' .
                '?client_id=' . $this->twitchId .
                '&redirect_uri=' . $urlLoginCheck .
                '&response_type=code' .
                '&scope=' . $this->twitchScope .
                '&state=' . $csrfToken->getToken('twitch_token_csrf'),
            );
        }

        return $this->redirectToRoute('app_dashboard_index');
    }

    #[Route('/login/check', name: 'app_twitch_login_check')]
    public function loginCheck(): RedirectResponse
    {
        return $this->redirectToRoute('app_dashboard_index');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): RedirectResponse
    {
        return $this->redirectToRoute('home_page');
    }
}
