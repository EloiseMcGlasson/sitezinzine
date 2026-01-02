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
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Storage\StorageInterface;



#[Route("/admin/categorie", name: 'admin.categorie.')]
#[IsGranted("ROLE_USER")]
class CategorieController extends AbstractController
{
    #[Route(name: 'index')]
    public function index(Request $request, CategoriesRepository $categoriesRepository, SessionInterface $session, Security $security): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10;

        $session->set('previous_page', $page);

        $user = $security->getUser();
        $categorie = $categoriesRepository->paginateCategoriesWithCount($page, $limit, $user);

        return $this->render('admin/categorie/index.html.twig', [
            'categories' => $categorie
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function show(Categories $categorie): Response
    {
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
    SessionInterface $session,
    StorageInterface $storage,
    PropertyMappingFactory $mappingFactory
): Response {

    if ($categorie->isSoftDelete()) {
        $this->addFlash('warning', 'Impossible de modifier une catégorie supprimée.');
        return $this->redirectToRoute('admin.categorie.index');
    }

    // 🔐 Vérifie les droits (admin/super_admin OK) sinon il faut être dans categorie.users
    if (
        !$this->isGranted('ROLE_ADMIN') &&
        !$this->isGranted('ROLE_SUPER_ADMIN') &&
        !$categorie->getUsers()->contains($this->getUser())
    ) {
        throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette catégorie.');
    }

    $returnTo = $request->query->get('returnTo');
    if ($returnTo) {
        $session->set('return_to_url', $returnTo);
    }

    $form = $this->createForm(CategorieType::class, $categorie);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        // ✅ suppression image si demandée
        if ($request->request->getBoolean('delete_thumbnail')) {

            $mappings = $mappingFactory->fromObject($categorie);
            $thumbnailMapping = null;

            foreach ($mappings as $m) {
                // Vich PropertyMapping expose en général getPropertyName()
                if (method_exists($m, 'getPropertyName') && $m->getPropertyName() === 'thumbnailFile') {
                    $thumbnailMapping = $m;
                    break;
                }
            }

            if (null !== $thumbnailMapping) {
                $storage->remove($categorie, $thumbnailMapping);
            }

            // on nettoie aussi le nom de fichier en BDD
            $categorie->setThumbnail(null);
        }

        $categorie->setUpdatedAt(new \DateTime());
        $em->flush();

        $this->addFlash('success', 'La catégorie a bien été modifiée.');

        if ($session->has('return_to_url')) {
            $url = $session->get('return_to_url');
            $session->remove('return_to_url');
            return $this->redirect($url);
        }

        $previousPage = $session->get('previous_page', 1);
        return $this->redirectToRoute('admin.categorie.index', ['page' => $previousPage]);
    }

    return $this->render('admin/categorie/edit.html.twig', [
        'categorie' => $categorie,
        'form' => $form->createView()
    ]);
}


#[Route('/create', name: 'create', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_ADMIN')]
public function create(
    Request $request,
    EntityManagerInterface $em,
    Security $security,
    SessionInterface $session
): Response {
    $categorie = new Categories();

    // Gestion "returnTo" comme dans edit
    $returnTo = $request->query->get('returnTo');
    if ($returnTo) {
        $session->set('return_to_url', $returnTo);
    }

    $form = $this->createForm(CategorieType::class, $categorie);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $categorie->setSoftDelete(false);
        $categorie->setUpdatedAt(new \DateTime());

        // ✅ si aucun user sélectionné dans le form, on met l'utilisateur courant par défaut
        $user = $security->getUser();
        if ($categorie->getUsers()->isEmpty() && $user) {
            $categorie->addUser($user);
        }

        $em->persist($categorie);
        $em->flush();

        $this->addFlash('success', 'La catégorie a été créée !');

        // Retour prioritaire : returnTo (comme edit)
        if ($session->has('return_to_url')) {
            $url = $session->get('return_to_url');
            $session->remove('return_to_url');
            return $this->redirect($url);
        }

        // Sinon retour index + page précédente si connue
        $previousPage = $session->get('previous_page', 1);
        return $this->redirectToRoute('admin.categorie.index', ['page' => $previousPage]);
    }

    return $this->render('admin/categorie/create.html.twig', [
        'form' => $form->createView(),
    ]);
}

    #[Route('/{id}', name: 'softDelete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function remove(Request $request, Categories $categorie, EntityManagerInterface $em): Response
    {
        // ✅ admin/super_admin OK, sinon il faut être dans categorie.users
        if (
            !$this->isGranted('ROLE_ADMIN') &&
            !$this->isGranted('ROLE_SUPER_ADMIN') &&
            !$categorie->getUsers()->contains($this->getUser())
        ) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer cette catégorie.');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete' . $categorie->getId(), $token)) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('admin.categorie.index');
        }

        $categorie->setSoftDelete(true);
        $categorie->setUpdatedAt(new \DateTime());
        $em->flush();

        $this->addFlash('success', 'La catégorie a bien été supprimée');

        $returnTo = $request->query->get('returnTo');
        if ($returnTo) {
            return $this->redirect($returnTo);
        }

        return $this->redirectToRoute('admin.categorie.index');
    }
}
