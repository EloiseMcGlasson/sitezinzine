<?php

namespace App\Controller\API;

use App\Entity\Emission;
use App\Repository\EmissionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

class EmissionsController extends AbstractController
{


#[Route("/api/emissions")]
Public function index(EmissionRepository $repository, Request $request)
{
    $emissions = $repository->paginateEmissions($request->query->getInt('page', 1), '');
    return $this->json($emissions, 200, [], [
        'groups' => ['emissions.index']
    ]);

}

#[Route("/api/emissions/{id}", requirements: ['id' => Requirement::DIGITS])]
Public function show(Emission $emission)
{
    
    return $this->json($emission, 200, [], [
        'groups' => ['emissions.index']
    ]);

}

}