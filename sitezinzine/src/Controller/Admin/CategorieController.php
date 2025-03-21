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
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/admin/categorie", name: 'admin.categorie.')]
#[IsGranted("ROLE_ADMIN")]
#[IsGranted("ROLE_SUPER_ADMIN")]
class CategorieController extends AbstractController
{
    #[Route(name: 'index')]
    public function index(Request $request, CategoriesRepository $categoriesRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10;
        $categorie = $categoriesRepository->paginateCategoriesWithCount($page, $limit);
        $maxPage = ceil($categorie->getTotalItemCount()/ $limit);
        /* dd($categorie); */
        return $this->render('admin/categorie/index.html.twig', [
            //'categorie' => $categoriesRepository->findAllWithCount(),
            'categories' => $categorie
            
            
        ]);
        
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function show(Categories $categorie, int $id, CategoriesRepository $categorieRepository)
    {
        $categorie = $categorieRepository->find($id);
        return $this->render('admin/categorie/show.html.twig', [
            'categorie' => $categorie,
            
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
    public function edit(Categories $categorie, Request $request, EntityManagerInterface $em)
    {
             // Vérifie si l'utilisateur est admin/super_admin
    if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
        throw $this->createAccessDeniedException('Vous n\'avez pas les droits pour modifier cette émission.');
    }
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $categorie->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();
            $this->addFlash('success', 'La catégorie a bien été modifié');
            return $this->redirectToRoute('admin.categorie.index');
        }
        return $this->render('admin/categorie/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $em)
    {
        $categorie = new Categories();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $categorie->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($categorie);
            $em->flush();
            $this->addFlash('success', 'La catégorie a été crée !');
            return $this->redirectToRoute('admin.categorie.index');
        }
        return $this->render('admin/categorie/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function remove(Categories $categorie, EntityManagerInterface $em)
    {
        $em->remove($categorie);
        $em->flush();
        $this->addFlash('success', 'La catégorie a bien été supprimée');
        return $this->redirectToRoute('admin.categorie.index');
    }
}
