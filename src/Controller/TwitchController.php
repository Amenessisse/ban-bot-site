<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class TwitchController extends AbstractController
{

    private string $twitchDomain;
    private string $twitchId;

    public function __construct(string $twitchDomain, string $twitchId)
    {
        $this->twitchDomain = $twitchDomain;
        $this->twitchId = $twitchId;
    }

    #[Route('/login/twitch', name: 'app_twitch_login')]
    public function index(#[CurrentUser] ?User $user, CsrfTokenManagerInterface $csrfToken): RedirectResponse
    {
        if (! $user instanceof User) {
            $urlLoginCheck = $this->generateUrl(route: 'app_twitch_login_check',referenceType: UrlGeneratorInterface::ABSOLUTE_URL);

            return new RedirectResponse(
                url: $this->twitchDomain . 'oauth2/authorize' .
                '?client_id=' . $this->twitchId .
                '&redirect_uri=' . $urlLoginCheck .
                '&response_type=code' .
                '&scope=user:read:email' .
                '&state=' . $csrfToken->getToken('twitch_token_csrf')
            );
        }

        return $this->redirectToRoute('dashboard');
    }

    #[Route('/login/check', name: 'app_twitch_login_check')]
    public function loginCheck(): RedirectResponse
    {
        return $this->redirectToRoute('dashboard');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): RedirectResponse
    {
        return $this->redirectToRoute('home_page');
    }
}