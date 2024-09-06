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

    #[Route("/zone", name: "zone")]
    function zone(Request $request): Response
    {
        
        return $this->render('home/zoneecoute.html.twig');
    }
    #[Route("/aide", name: "aide")]
    function aide(Request $request): Response
    {
        
        return $this->render('home/aide.html.twig');
    }
    #[Route("/amis", name: "amis")]
    function amis(Request $request): Response
    {
        
        return $this->render('home/amis.html.twig');
    }

    #[Route("/mentions", name: "mentions")]
    function mentions(Request $request): Response
    {
        
        return $this->render('home/mentions.html.twig');
    }

    #[Route("/contacts", name: "contacts")]
    function contacts(Request $request): Response
    {
        
        return $this->render('home/contacts.html.twig');
    }

    #[Route("/don", name: "don")]
    function don(Request $request): Response
    {
        
        return $this->render('home/don.html.twig');
    }

    #[Route("/annonces", name: "annonces")]
    function annonces(Request $request): Response
    {
        
        return $this->render('home/annonces.html.twig');
    }
}
