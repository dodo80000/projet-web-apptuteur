<?php

namespace App\Controller;

use App\Entity\Visite;
use App\Entity\Etudiant;
use App\Repository\TuteurRepository;
use App\Repository\VisiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class VisiteController extends AbstractController
{
    #[Route('/etudiants/{id}/visites', name: 'visites_etudiant', methods: ['GET'])]
    public function liste(
        Etudiant $etudiant,
        Request $request,
        SessionInterface $session,
        TuteurRepository $tuteurRepository,
        VisiteRepository $visiteRepository
    ) {
        if (!$session->has('tuteur_id')) {
            return $this->redirectToRoute('login');
        }

        $tuteur = $tuteurRepository->find($session->get('tuteur_id'));
        if (!$tuteur || $etudiant->getTuteur()?->getId() !== $tuteur->getId()) {
            throw $this->createAccessDeniedException();
        }

        $tri = $request->query->get('tri', 'asc');
        $statut = $request->query->get('statut', 'toutes');

        $direction = strtoupper($tri) === 'DESC' ? 'DESC' : 'ASC';
        $visites = $visiteRepository->findByEtudiantSorted($etudiant, $direction);

        if ($statut && $statut !== 'toutes') {
            $visites = array_filter($visites, fn(Visite $v) => $v->getStatut() === $statut);
        }

        return $this->render('visite/liste.html.twig', [
            'etudiant' => $etudiant,
            'visites' => $visites,
            'statut' => $statut,
            'tri' => $tri,
        ]);
    }

    #[Route('/etudiants/{id}/visites/new', name: 'visites_ajouter', methods: ['GET', 'POST'])]
    public function ajouter(
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
            $visite = new Visite();
            $visite->setDate(new \DateTimeImmutable($request->request->get('date')));
            $visite->setCommentaire($request->request->get('commentaire'));
            $visite->setCompteRendu(null);
            $visite->setStatut('prévue');
            $visite->setTuteur($tuteur);
            $visite->setEtudiant($etudiant);

            $em->persist($visite);
            $em->flush();

            return $this->redirectToRoute('visites_etudiant', ['id' => $etudiant->getId()]);
        }

        return $this->render('visite/ajouter.html.twig', [
            'etudiant' => $etudiant,
        ]);
    }

    #[Route('/visites/{id}/edit', name: 'visites_modifier', methods: ['GET', 'POST'])]
    public function modifier(
        Visite $visite,
        Request $request,
        SessionInterface $session,
        TuteurRepository $tuteurRepository,
        EntityManagerInterface $em
    ) {
        if (!$session->has('tuteur_id')) {
            return $this->redirectToRoute('login');
        }

        $tuteur = $tuteurRepository->find($session->get('tuteur_id'));
        if (!$tuteur || $visite->getTuteur()?->getId() !== $tuteur->getId()) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            $visite->setDate(new \DateTimeImmutable($request->request->get('date')));
            $visite->setCommentaire($request->request->get('commentaire'));
            $visite->setCompteRendu($request->request->get('compteRendu'));
            $visite->setStatut($request->request->get('statut'));
            $em->flush();

            return $this->redirectToRoute('visites_etudiant', [
                'id' => $visite->getEtudiant()->getId()
            ]);
        }

        return $this->render('visite/modifier.html.twig', [
            'visite' => $visite,
        ]);
    }

    #[Route('/visites/{id}/compte-rendu', name: 'visites_compte_rendu', methods: ['GET', 'POST'])]
    public function compteRendu(
        Visite $visite,
        Request $request,
        EntityManagerInterface $em,
        SessionInterface $session,
        TuteurRepository $tuteurRepository,
        HtmlSanitizerInterface $htmlSanitizer
    ) {
        if (!$session->has('tuteur_id')) {
            return $this->redirectToRoute('login');
        }

        $tuteur = $tuteurRepository->find($session->get('tuteur_id'));
        if (!$tuteur || $visite->getTuteur()?->getId() !== $tuteur->getId()) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            $raw = $request->request->get('compteRendu');
            $clean = $htmlSanitizer->sanitize($raw);
            $visite->setCompteRendu($clean);
            $em->flush();

            return $this->redirectToRoute('visites_compte_rendu', ['id' => $visite->getId()]);
        }

        return $this->render('visite/compte_rendu.html.twig', [
            'visite' => $visite,
        ]);
    }

    #[Route('/visites/{id}/compte-rendu/pdf', name: 'visites_compte_rendu_pdf')]
    public function compteRenduPdf(Visite $visite): Response
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        $html = $this->renderView('visite/compte_rendu_pdf.html.twig', [
            'visite' => $visite,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="compte-rendu-visite-'.$visite->getId().'.pdf"'
            ]
        );
    }

    #[Route('/visites/{id}/supprimer', name: 'visites_supprimer', methods: ['GET'])]
    public function supprimer(
        Visite $visite,
        Request $request,
        SessionInterface $session,
        TuteurRepository $tuteurRepository,
        EntityManagerInterface $em
    ) {
        if (!$session->has('tuteur_id')) {
            return $this->redirectToRoute('login');
        }

        $tuteur = $tuteurRepository->find($session->get('tuteur_id'));
        if (!$tuteur || $visite->getTuteur()?->getId() !== $tuteur->getId()) {
            throw $this->createAccessDeniedException();
        }

        $token = $request->query->get('_token');
        if (!$this->isCsrfTokenValid('delete_visite_'.$visite->getId(), $token)) {
            throw $this->createAccessDeniedException('Action non autorisée.');
        }

        $etudiantId = $visite->getEtudiant()->getId();

        $em->remove($visite);
        $em->flush();

        return $this->redirectToRoute('visites_etudiant', ['id' => $etudiantId]);
    }
}
