<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\EmissionRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route("/", name: "home")]
    function index(Request $request, EmissionRepository $emissionRepository): Response
    {
        $lastEmissions = $emissionRepository->lastEmissions('');
        
        return $this->render('home/index.html.twig', [
            'lastEmissions' => $lastEmissions
            

        ]);
    }

    #[Route("/radio", name: "radio")]
    function radio(Request $request): Response
    {
        
        return $this->render('home/radio.html.twig');
    }

    #[Route("/programme", name: "programme")]
    function programme(Request $request): Response
    {
        
        return $this->render('home/programme.html.twig');
    }

    #[Route("/infos", name: "infos")]
    function infos(Request $request): Response
    {
        
        return $this->render('home/infos.html.twig');
    }
}
