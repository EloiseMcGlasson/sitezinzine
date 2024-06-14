<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EmissionController extends AbstractController
{
    #[Route('/emission', name: 'emission.index')]
    public function index(Request $request): Response
    {
        return $this->render('emission/index.html.twig');
        
    }
    
    #[Route('/emission/{slug}-{id}', name: 'emission.show', requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+'])]
    public function show(Request $request, string $slug, int $id): Response
    {
        return $this->render('emission/show.html.twig', [
            'slug' => $slug,
            'id' => $id
        ]);
        
    }
}
