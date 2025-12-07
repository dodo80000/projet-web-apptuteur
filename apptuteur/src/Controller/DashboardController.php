<?php

namespace App\Controller;

use App\Repository\TuteurRepository;
use App\Repository\VisiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(SessionInterface $session, TuteurRepository $tuteurRepository, VisiteRepository $visiteRepository)
    {
        if (!$session->has('tuteur_id')) {
            return $this->redirectToRoute('login');
        }

        $tuteur = $tuteurRepository->find($session->get('tuteur_id'));

        $etudiantsSuivis = $tuteur->getEtudiants() ;

        $visites = $tuteur->getVisites();

        return $this->render('dashboard.html.twig', [
            'tuteur' => $tuteur,
            'etudiants' => $etudiantsSuivis,
            'visites' => $visites,
        ]);
    }
}
