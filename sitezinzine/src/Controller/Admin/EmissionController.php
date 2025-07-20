<?php

namespace App\Controller\Admin;

use App\Entity\Emission;

use App\Form\EmissionType;
use App\Repository\EmissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/admin/emission', name: 'admin.emission.')]
#[IsGranted('ROLE_USER')]
class EmissionController extends AbstractController
{
    #[Route('/', name: 'index')]
public function index(Request $request, EmissionRepository $repository, Security $security, SessionInterface $session): Response
{
    $page = $request->query->getInt('page', 1);
    $limit = 25;

    // Stockage de la page courante dans la session
    $session->set('previous_page_emission', $page);

    $user = $security->getUser();
    $isAdmin = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN');

    $emissions = $repository->paginateEmissionsAdmin($page, '', $user, $isAdmin);
    $maxPage = (int) ceil($emissions->getTotalItemCount() / $limit);

    return $this->render('admin/emission/index.html.twig', [
        'emissions' => $emissions,
        'maxPage' => $maxPage,
    ]);
}



    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function show(Emission $emission): Response
    {
        return $this->render('admin/emission/show.html.twig', [
            'emission' => $emission,
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $emission = new Emission();
        $form = $this->createForm(EmissionType::class, $emission);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $now = new \DateTime();
            $emission
                ->setDatepub($now)
                ->setUpdatedat($now)
                ->setUser($security->getUser());

            $em->persist($emission);
            $em->flush();

            $this->addFlash('success', 'L\'émission a été créée !');

            return $this->redirectToRoute('admin.emission.index');
        }

        return $this->render('admin/emission/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
public function edit(
    Request $request,
    Emission $emission,
    EntityManagerInterface $em,
    Security $security,
    SessionInterface $session
): Response {
    // Vérifie si l'utilisateur est admin/super_admin ou si c'est l'auteur de l'émission
    if (
        !$this->isGranted('ROLE_ADMIN') &&
        !$this->isGranted('ROLE_SUPER_ADMIN') &&
        $emission->getUser() !== $security->getUser()
    ) {
        throw $this->createAccessDeniedException('Vous n\'avez pas les droits pour modifier cette émission.');
    }

    // Récupère l'URL de redirection précédente via `?returnTo=...` s'il existe
    $returnTo = $request->query->get('returnTo');
    if ($returnTo) {
        $session->set('return_to_url', $returnTo);  // Enregistrer dans la session
    }

    // Création et gestion du formulaire
    $form = $this->createForm(EmissionType::class, $emission);
    $form->handleRequest($request);

    // Vérifie si le formulaire a été soumis et est valide
    if ($form->isSubmitted() && $form->isValid()) {
        if (!$emission->getUser()) {
            $emission->setUser($security->getUser());
        }

        $emission->setUpdatedat(new \DateTime());
        $em->flush();

        // Message flash pour indiquer la réussite
        $this->addFlash('success', 'L\'émission a bien été modifiée.');

        // Si un lien de retour a été enregistré dans la session, redirige là
        if ($session->has('return_to_url')) {
            $url = $session->get('return_to_url');
            $session->remove('return_to_url'); // nettoyage
            return $this->redirect($url);
        }

        // Sinon, redirige vers la liste des émissions, avec la page précédente
        $previousPage = $session->get('previous_page_emission', 1);
        return $this->redirectToRoute('admin.emission.index', ['page' => $previousPage]);
    }

    // Rendu du formulaire
    return $this->render('admin/emission/edit.html.twig', [
        'emission' => $emission,
        'formEmission' => $form->createView(),
    ]);
}



    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(Emission $emission, EntityManagerInterface $em): Response
    {
        $em->remove($emission);
        $em->flush();

        $this->addFlash('success', 'L\'émission a bien été supprimée.');

        return $this->redirectToRoute('admin.emission.index');
    }
}
