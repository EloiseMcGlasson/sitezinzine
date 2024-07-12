<?php

namespace App\Controller\API;

use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CategoriesController extends AbstractController
{


#[Route("/api/categories")]
Public function index(CategoriesRepository $repository)
{
    $categories = $repository->findAll();
    return $this->json($categories, 200, [], [
        'groups' => ['categories.index']
    ]);

}

}