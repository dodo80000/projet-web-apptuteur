<?php

namespace App\Controller;

use App\Repository\TuteurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(Request $request, TuteurRepository $tuteurRepository, SessionInterface $session)
    {
        $error = null;

        $logoutMessage = $request->query->get('logout');

        if ($request->isMethod('POST')) {
            
            $csrfToken = $request->request->get('_csrf_token');
            if (!$this->isCsrfTokenValid('login_form', $csrfToken)) {
                throw $this->createAccessDeniedException('Action non autorisée.');
            }

            $email = $request->request->get('email');
            $password = $request->request->get('password');

            // Recherche du tuteur
            $tuteur = $tuteurRepository->findOneBy(['email' => $email]);

            if ($tuteur) {
                // Stocker en session
                $session->set('tuteur_id', $tuteur->getId());

                // Redirection vers dashboard
                return $this->redirectToRoute('dashboard');
            }

            // Si pas trouvé :
            $error = "Email incorrect. Aucun tuteur trouvé.";
        }

        return $this->render('login.html.twig', [
            'error' => $error,
            'logout' => $logoutMessage,
        ]);
    }
}
