<?php

namespace App\Controller;

use App\Repository\TuteurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class LogoutController extends AbstractController
{
    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(SessionInterface $session)
    {
        $session->remove('tuteur_id');

        return $this->redirectToRoute('login', [
            'logout' => 'Vous avez été déconnecté'
        ]);
    }
}
