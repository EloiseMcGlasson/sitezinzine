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
use App\Form\EmissionSearchType;
use App\Controller\Traits\ReturnToTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/admin/emission', name: 'admin.emission.')]
#[IsGranted('ROLE_USER')]
class EmissionController extends AbstractController
{
    use ReturnToTrait;

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
            $user = $security->getUser();

            // Si ref est vide, on met le username du user connecté
            if (empty($emission->getRef()) && $user) {
                $emission->setRef($user->getUserIdentifier());
            }

            $emission
                ->setDatepub($now)
                ->setUpdatedat($now)
                ->setUser($user);

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
        SessionInterface $session,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        $user = $security->getUser();

        // Vérifie si l'utilisateur est admin/super_admin ou l'auteur de l'émission
        if (
            !$this->isGranted('ROLE_ADMIN') &&
            !$this->isGranted('ROLE_SUPER_ADMIN') &&
            $emission->getUser() !== $user
        ) {
            throw $this->createAccessDeniedException('Vous n\'avez pas les droits pour modifier cette émission.');
        }

        // Enregistre returnTo si présent
        $this->storeReturnTo($request, $session);

        // Création et gestion du formulaire (envoie le username au form)
        $form = $this->createForm(EmissionType::class, $emission, [
            'current_user_identifier' => $user?->getUserIdentifier(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$emission->getUser()) {
                $emission->setUser($user);
            }

            $emission->setUpdatedat(new \DateTime());
            $em->flush();

            $this->addFlash('success', 'L\'émission a bien été modifiée.');

            // Redirection intelligente
            return $this->redirectToReturnTo($session, $urlGenerator, 'admin.emission.index', [
                'page' => $session->get('previous_page_emission', 1),
            ]);
        }

        return $this->render('admin/emission/edit.html.twig', [
            'emission' => $emission,
            'formEmission' => $form->createView(),
        ]);
    }




    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(Request $request, Emission $emission, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete' . $emission->getId(), $token)) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('admin.emission.index');
        }

        $em->remove($emission);
        $em->flush();

        $this->addFlash('success', 'L\'émission a bien été supprimée.');

        // Retourne à l'URL courante (fournie en paramètre)
        $returnTo = $request->query->get('returnTo');
        if ($returnTo) {
            return $this->redirect($returnTo);
        }

        return $this->redirectToRoute('admin.emission.index');
    }

    #[Route('/rechercheadmin', name: 'rechercheadmin')]
    public function search(Request $request, EmissionRepository $emissionRepository): Response
    {
        $form = $this->createForm(EmissionSearchType::class);
        $form->handleRequest($request);

        $emissions = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
            $page = $request->query->getInt('page', 1);
            $emissions = $emissionRepository->findBySearchAdmin($criteria, $page);
            foreach ($emissions as $emission) {
                $lastDate = $emissionRepository->findLastDiffusionDate($emission->getId());
                if ($lastDate) {
                    $emission->setLastDiffusion($lastDate);
                }
            }
        }

        return $this->render('admin/recherche.html.twig', [
            'form' => $form->createView(),
            'emissions' => $emissions,
            'searchTerm' => $form->get('titre')->getData() // ✅ Récupère la valeur du champ "titre"
        ]);
    }
}
