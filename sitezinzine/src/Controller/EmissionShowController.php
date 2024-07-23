<?php

namespace App\Controller;

use App\Entity\Emission;
use App\Form\EmissionType;

use App\Repository\EmissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route("/emission", name: 'emission.')]
class EmissionShowController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, EmissionRepository $emissionRepository): Response
    {
    
        $page = $request->query->getInt('page', 1);
        $limit= 25;
        $emissions = $emissionRepository->paginateEmissions($page, '');
        $maxPage = ceil($emissions->getTotalItemCount() / $limit);
        //dd($emissions->count());
        return $this->render('/home/emissions.html.twig', [
            'emissions' => $emissions,
            

        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function show(Emission $emission, Request $request, EntityManagerInterface $em)
    {
        $formEmission = $this->createForm(EmissionType::class, $emission);
        $formEmission->handleRequest($request);
        if ($formEmission->isSubmitted() && $formEmission->isValid()) {
            $emission->setUpdatedat(new \DateTime());
            $em->flush();
            $this->addFlash('success', 'L\'émission a bien été modifié');
            return $this->redirectToRoute('admin.emission.index');
        }
        return $this->render('/home/show.html.twig', [
            'emission' => $emission,
            
        ]);
    }
    
}
