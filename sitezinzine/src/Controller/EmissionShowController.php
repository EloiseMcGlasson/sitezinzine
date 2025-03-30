<?php

namespace App\Controller;

use App\Entity\Emission;
use App\Form\EmissionSearchType;
use Symfony\Bundle\SecurityBundle\Security;

use App\Repository\EmissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;



#[Route("/emission", name: 'emission.')]
class EmissionShowController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, EmissionRepository $emissionRepository, Security $security): Response
    {
    
        $page = $request->query->getInt('page', 1);
        $limit= 25;
        $emissions = $emissionRepository->paginateEmissions($page, '', $this->getUser(), $security);
        $maxPage = ceil($emissions->getTotalItemCount() / $limit);
      
        //dd($emissions->count());
        return $this->render('/home/emissions.html.twig', [
            'emissions' => $emissions
            

        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function show(Emission $emission): Response
    {

        
        return $this->render('/home/show.html.twig', [
            'emission' => $emission,
            
        ]);
    }

   /*  #[Route('/recherche-emissions', name: 'recherche_emissions')]
    public function rechercher(Request $request, EmissionRepository $emissionRepository): Response
    {
        $form = $this->createForm(EmissionSearchType::class);
        $form->handleRequest($request);

        $emissions = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $emissions = $emissionRepository->findByCriteria($data['titre'], $data['dateDiffusion']);
        }

        return $this->render('home/recherche.html.twig', [
            'form' => $form->createView(),
            'emissions' => $emissions,
        ]);
    } */
    #[Route('/recherche', name: 'recherche')]
    public function search(Request $request, EmissionRepository $emissionRepository): Response
{
    $form = $this->createForm(EmissionSearchType::class);
    $form->handleRequest($request);

    $emissions = [];
    
    if ($form->isSubmitted() && $form->isValid()) {
        $criteria = $form->getData();
        $page = $request->query->getInt('page', 1);
        $emissions = $emissionRepository->findBySearch($criteria, $page);
    
    }

    return $this->render('home/recherche.html.twig', [
        'form' => $form->createView(),
        'emissions' => $emissions,
        'searchTerm' => $form->get('titre')->getData() // ✅ Récupère la valeur du champ "titre"
    ]);
}

    
    
}
