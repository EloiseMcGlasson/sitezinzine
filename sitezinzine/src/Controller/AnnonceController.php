<?php

namespace App\Controller;

use App\Repository\AnnonceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AnnonceController extends AbstractController
{
    #[Route('/annonce', name: 'annonce')]
    public function index(AnnonceRepository $annonceRepository): Response
    {
        $annonce = $annonceRepository->findAll();
        return $this->render('/home/annonce.html.twig', [
            'annonce' => $annonce,
        ]);
    }

    #[Route('/annonce/create', name: 'annonce.create')]
    public function create(AnnonceRepository $annonceRepository): Response
    {
        $annonce = $annonceRepository->findAll();
        return $this->render('/home/annonceCreate.html.twig', [
            'annonce' => $annonce,
        ]);
    }
    }

