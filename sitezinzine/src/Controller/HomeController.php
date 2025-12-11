<?php

namespace App\Controller;


use App\Repository\EmissionRepository;
use App\Repository\EvenementRepository;
use App\Repository\PageRepository;
use App\Entity\Evenement;
use Symfony\Component\Routing\Requirement\Requirement;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
#[Route("/", name: "home")]
public function index(EmissionRepository $emissionRepository, EvenementRepository $evenementRepository): Response
{
    $date = new \DateTime('2025-06-25');

    $emissions = $emissionRepository->findEmissionsByDate($date);

    // On prépare une structure : une liste de couples [emission, diffusion]
    $lastEmissions = [];

    foreach ($emissions as $emission) {
        foreach ($emission->getDiffusions() as $diffusion) {
            if ($diffusion->getHoraireDiffusion()->format('Y-m-d') === $date->format('Y-m-d')) {
                $lastEmissions[] = [
                    'emission' => $emission,
                    'diffusion' => $diffusion->getHoraireDiffusion(),
                ];
            }
        }
    }

    return $this->render('home/index.html.twig', [
        'lastEmissions' => $lastEmissions,
        'lastEmissionsByTheme' => $emissionRepository->lastEmissionsByGroupTheme(''),
        'evenements' => $evenementRepository->findUpcomingEvenements(),
    ]);
}






    #[Route('/{id}', name: 'showEvenement', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function showEvenement(Evenement $evenement): Response
    {

        
        return $this->render('/home/showEvenement.html.twig', [
            'evenement' => $evenement,
            
        ]);
    }

   #[Route('/radio', name: 'radio')]
    public function radio(PageRepository $pageRepository): Response
    {
        // adapte le slug si tu l'as appelé autrement dans l’admin
        $page = $pageRepository->findOneBy(['slug' => 'radio']);

        if (!$page) {
            throw $this->createNotFoundException('Page "radio" introuvable.');
        }

        return $this->render('home/radio.html.twig', [
            'page' => $page,
        ]);
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
    function don(PageRepository $pageRepository): Response
    {
 // adapte le slug si tu l'as appelé autrement dans l’admin
        $page = $pageRepository->findOneBy(['slug' => 'don']);

        if (!$page) {
            throw $this->createNotFoundException('Page "don" introuvable.');
        }

        return $this->render('home/don.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route("/newsletter", name: "newsletter")]
    function newsletter(): Response
    {

        return $this->render('home/newsletter.html.twig');
    }


}
