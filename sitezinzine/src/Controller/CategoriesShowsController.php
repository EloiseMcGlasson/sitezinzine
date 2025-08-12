<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use App\Repository\EmissionRepository;
use Symfony\Component\HttpFoundation\Response;

#[Route("/categorie", name: 'categorie.')]
class CategoriesShowsController extends AbstractController
{
#[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    function show(int $id, CategoriesRepository $categoriesRepository, EmissionRepository $emissionRepository): Response
    {
        $categorie = $categoriesRepository->find($id);
        $emissions = $emissionRepository->findLatestByCategory($id, 20);
        return $this->render('/home/showCat.html.twig', [

            'categorie' => $categorie,
            'emissions' => $emissions,
        ]);
    }
}