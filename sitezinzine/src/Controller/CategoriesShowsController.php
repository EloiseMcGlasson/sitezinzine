<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route("/categorie", name: 'categorie.')]
class CategoriesShowsController extends AbstractController
{
#[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    function show(Categories $categorie, int $id, CategoriesRepository $categorieRepository)
    {
        $categorie = $categorieRepository->find($id);
        return $this->render('/home/showCat.html.twig', [

            'categorie' => $categorie,
            
        ]);
    }
}