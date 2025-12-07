<?php

namespace App\Controller;

use App\Entity\Etudiant;
use App\Form\EtudiantType;
use App\Repository\TuteurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/etudiants')]
class EtudiantController extends AbstractController
{
    #[Route('/', name: 'etudiants', methods: ['GET'])]
    public function index(SessionInterface $session, TuteurRepository $tuteurRepository)
    {
        if (!$session->has('tuteur_id')) {
            return $this->redirectToRoute('login');
        }

        $tuteur = $tuteurRepository->find($session->get('tuteur_id'));
        $etudiantsSuivis = $tuteur ? $tuteur->getEtudiants() : [];

        return $this->render('etudiant/etudiants.html.twig', [
            'tuteur' => $tuteur,
            'etudiants' => $etudiantsSuivis,
        ]);
    }

    #[Route('/ajouter', name: 'etudiants_ajouter', methods: ['GET', 'POST'])]
    public function ajouter(
        Request $request,
        SessionInterface $session,
        TuteurRepository $tuteurRepository,
        EntityManagerInterface $em
    ) {
        if (!$session->has('tuteur_id')) {
            return $this->redirectToRoute('login');
        }

        $tuteur = $tuteurRepository->find($session->get('tuteur_id'));
        if (!$tuteur) {
            throw $this->createAccessDeniedException();
        }

        $etudiant = new Etudiant();
        $form = $this->createForm(EtudiantType::class, $etudiant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $etudiant->setTuteur($tuteur);
            $em->persist($etudiant);
            $em->flush();

            return $this->redirectToRoute('etudiants');
        }

        return $this->render('etudiant/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/modifier', name: 'etudiants_modifier', methods: ['GET', 'POST'])]
    public function modifier(
        Etudiant $etudiant,
        Request $request,
        SessionInterface $session,
        TuteurRepository $tuteurRepository,
        EntityManagerInterface $em
    ) {
        if (!$session->has('tuteur_id')) {
            return $this->redirectToRoute('login');
        }

        $tuteur = $tuteurRepository->find($session->get('tuteur_id'));
        if (!$tuteur || $etudiant->getTuteur()?->getId() !== $tuteur->getId()) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            $etudiant->setNom($request->request->get('nom'));
            $etudiant->setPrenom($request->request->get('prenom'));
            $etudiant->setFormation($request->request->get('formation'));

            $em->flush();

            return $this->redirectToRoute('etudiants');
        }

        return $this->render('etudiant/modifier.html.twig', [
            'etudiant' => $etudiant,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'etudiants_supprimer', methods: ['GET'])]
    public function supprimer(
        Etudiant $etudiant,
        Request $request,
        SessionInterface $session,
        TuteurRepository $tuteurRepository,
        EntityManagerInterface $em
    ) {
        if (!$session->has('tuteur_id')) {
            return $this->redirectToRoute('login');
        }

        $tuteur = $tuteurRepository->find($session->get('tuteur_id'));
        if (!$tuteur || $etudiant->getTuteur()?->getId() !== $tuteur->getId()) {
            throw $this->createAccessDeniedException();
        }

        $token = $request->query->get('_token');
        if (!$this->isCsrfTokenValid('delete_etudiant_'.$etudiant->getId(), $token)) {
            throw $this->createAccessDeniedException('Action non autorisée.');
        }

        foreach ($etudiant->getVisites() as $visite) {
            $em->remove($visite);
        }

        $em->remove($etudiant);
        $em->flush();

        return $this->redirectToRoute('etudiants');
    }
}
