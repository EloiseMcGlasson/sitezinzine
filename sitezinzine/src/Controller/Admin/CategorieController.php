<?php

namespace App\Controller\Admin;

use App\Entity\Categories;
use App\Form\CategorieType;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\SecurityBundle\Security;

#[Route("/admin/categorie", name: 'admin.categorie.')]
#[IsGranted("ROLE_USER")]
class CategorieController extends AbstractController
{

#[Route(name: 'index')]
public function index(Request $request, CategoriesRepository $categoriesRepository, SessionInterface $session, Security $security): Response
{
    $page = $request->query->getInt('page', 1);
    $limit = 10;

    // Stockage de la page courante dans la session
    $session->set('previous_page', $page);

    $user = $security->getUser();
    $categorie = $categoriesRepository->paginateCategoriesWithCount($page, $limit, $user);
    $maxPage = ceil($categorie->getTotalItemCount() / $limit);

    return $this->render('admin/categorie/index.html.twig', [
        'categories' => $categorie
    ]);
}



 #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
public function show(Categories $categorie): Response
{
    // ⛔ Vérifie si la catégorie est supprimée
    if ($categorie->isSoftDelete()) {
        $this->addFlash('warning', 'Cette catégorie a été supprimée.');
        return $this->redirectToRoute('admin.categorie.index');
    }

    return $this->render('admin/categorie/show.html.twig', [
        'categorie' => $categorie,
    ]);
}


#[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
public function edit(
    Categories $categorie,
    Request $request,
    EntityManagerInterface $em,
    SessionInterface $session
): Response {
    // ⛔ Blocage si soft deleted
    if ($categorie->isSoftDelete()) {
        $this->addFlash('warning', 'Impossible de modifier une catégorie supprimée.');
        return $this->redirectToRoute('admin.categorie.index');
    }

    // 🔐 Vérifie les droits d'accès à la catégorie
if (
    !$this->isGranted('ROLE_ADMIN') &&
    !$this->isGranted('ROLE_SUPER_ADMIN') &&
    $categorie->getUser() !== $this->getUser()
) {
    throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette catégorie.');
}

    // 🔁 Priorité au lien direct (via ?returnTo=...)
    $returnTo = $request->query->get('returnTo');
    if ($returnTo) {
        $session->set('return_to_url', $returnTo);
    }

    $form = $this->createForm(CategorieType::class, $categorie);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $categorie->setUpdatedAt(new \DateTime());
        $em->flush();

        $this->addFlash('success', 'La catégorie a bien été modifiée.');

        // ↩️ Redirection prioritaire vers l'URL stockée
        if ($session->has('return_to_url')) {
            $url = $session->get('return_to_url');
            $session->remove('return_to_url'); // nettoyage
            return $this->redirect($url);
        }

        // 🧭 Sinon fallback
        $previousPage = $session->get('previous_page', 1);
        return $this->redirectToRoute('admin.categorie.index', ['page' => $previousPage]);
    }

    return $this->render('admin/categorie/edit.html.twig', [
        'categorie' => $categorie,
        'form' => $form->createView()
    ]);
}





    #[Route('/create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $em, Security $security)
    {
        $categorie = new Categories();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $categorie->setUpdatedAt(new \DateTime());
            $categorie->setSoftDelete(false); // facultatif si déjà par défaut
            $categorie->setUser($security->getUser());
            $em->persist($categorie);
            $em->flush();
            $this->addFlash('success', 'La catégorie a été crée !');
            return $this->redirectToRoute('admin.categorie.index');
        }
        return $this->render('admin/categorie/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'softDelete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function remove(Request $request, Categories $categorie, EntityManagerInterface $em)
    {

         // ✅ Vérifie que la catégorie appartient bien à l'utilisateur (sauf admin)
    if (
        !$this->isGranted('ROLE_ADMIN') &&
        !$this->isGranted('ROLE_SUPER_ADMIN') &&
        $categorie->getUser() !== $this->getUser()
    ) {
        throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer cette catégorie.');
    }
        // Vérification du jeton CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete' . $categorie->getId(), $token)) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('admin.categorie.index');
        }
    
        // Marque la catégorie comme supprimée
        $categorie->setSoftDelete(true);
        $categorie->setUpdatedAt(new \DateTime());
        $em->flush();
        $this->addFlash('success', 'La catégorie a bien été supprimée');

          // Retourne à l'URL courante (fournie en paramètre)
    $returnTo = $request->query->get('returnTo');
    if ($returnTo) {
        return $this->redirect($returnTo);
    }

        return $this->redirectToRoute('admin.categorie.index');
    }
}
