<?php

namespace App\Controller;

use App\Entity\Emission;
use App\Form\EmissionType;
use App\Repository\CategoriesRepository;
use App\Repository\EmissionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EmissionController extends AbstractController
{
    #[Route('/emission', name: 'emission.index')]
    public function index(Request $request, EmissionRepository $emissionRepository, CategoriesRepository $categoriesRepository): Response
    {
        $emissions = $emissionRepository->findByExampleField('');
        
        return $this->render('emission/index.html.twig', [
            'emissions' => $emissions
            
        ]);
    }

    #[Route('/emission/{slug}-{id}', name: 'emission.show', requirements: ['id' => '\d+', 'slug' => '[a-z\°0-9-]+'])]
    public function show(Request $request, string $slug, int $id, EmissionRepository $emissionRepository): Response
    {
        $emission = $emissionRepository->find($id);
        
        
        return $this->render('emission/show.html.twig', [
         
            'emission' => $emission
        ]);
    }

    #[Route('/emissions/{id}/edit', name: 'emission.edit', requirements: ['id' => '\d+'])]
    public function edit(Emission $emission){
        $formEmission = $this->createForm(EmissionType::class, $emission);
        return $this->render('emission/edit.html.twig', [
            'emission'=> $emission,
            'formEmission' => $formEmission
        ]);

    }
}
