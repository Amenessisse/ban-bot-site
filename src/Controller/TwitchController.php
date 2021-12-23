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

    #[Route('/login/twitch', name: 'twitch_login')]
    public function index(#[CurrentUser] ?User $user, CsrfTokenManagerInterface $csrfToken): RedirectResponse {
        if ($user === null) {
            $urlDashboard = $this->generateUrl(route: 'dashboard',referenceType: UrlGeneratorInterface::ABSOLUTE_URL);

            return new RedirectResponse(
                url: $this->twitchDomain .
                '?client_id=' . $this->twitchId .
                '&redirect_uri=' . $urlDashboard .
                '&response_type=code' .
                '&scope=user:read:email' .
                '&state=' . $csrfToken->getToken('twitch_token_csrf')
            );
        }

        return new RedirectResponse('home_page');
    }
}