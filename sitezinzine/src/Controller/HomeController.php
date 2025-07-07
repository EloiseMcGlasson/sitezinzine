<?php

namespace App\Controller;


use App\Repository\EmissionRepository;
use App\Repository\EvenementRepository;
use App\Entity\Evenement;
use Symfony\Component\Routing\Requirement\Requirement;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route("/", name: "home")]
    function index(EmissionRepository $emissionRepository, EvenementRepository $evenement): Response
    {
        $lastEmissions = $emissionRepository->lastEmissions('');
        $lastEmissionsByGroupTheme = $emissionRepository->lastEmissionsByGroupTheme('');
        $evenement = $evenement->findUpcomingEvenements();
        

        return $this->render('home/index.html.twig', [

            'lastEmissions' => $lastEmissions,
            'lastEmissionsByTheme' => $lastEmissionsByGroupTheme,
            'evenements' => $evenement
        ]);
    }

    #[Route('/{id}', name: 'showEvenement', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function showEvenement(Evenement $evenement): Response
    {

        
        return $this->render('/home/showEvenement.html.twig', [
            'evenement' => $evenement,
            
        ]);
    }

    #[Route("/radio", name: "radio")]
    function radio(): Response
    {

        return $this->render('home/radio.html.twig');
    }

    #[Route("/programme", name: "programme")]
    function programme(): Response
    {

        return $this->render('home/programme.html.twig');
    }

    #[Route("/infos", name: "infos")]
    function infos(): Response
    {

        return $this->render('home/infos.html.twig');
    }

    #[Route("/zone", name: "zone")]
    function zone(): Response
    {

        return $this->render('home/zoneecoute.html.twig');
    }
    #[Route("/aide", name: "aide")]
    function aide(): Response
    {

        return $this->render('home/aide.html.twig');
    }
    #[Route("/amis", name: "amis")]
    function amis(): Response
    {

        return $this->render('home/amis.html.twig');
    }

    #[Route("/mentions", name: "mentions")]
    function mentions(): Response
    {

        return $this->render('home/mentions.html.twig');
    }

    #[Route("/contacts", name: "contacts")]
    function contacts(): Response
    {

        return $this->render('home/contacts.html.twig');
    }

    #[Route("/don", name: "don")]
    function don(): Response
    {

        return $this->render('home/don.html.twig');
    }


}
