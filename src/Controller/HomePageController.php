<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomePageController extends AbstractController
{
    #[Route('/', name: 'home_page')]
    public function index(): Response
    {
        return $this->render('home/home.html.twig');
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function board(): Response
    {
        return $this->render('dashboard/dashboard.html.twig');
    }

}
