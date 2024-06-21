<?php

namespace App\Controller;

use App\Entity\Emission;
use App\Form\EmissionType;
use App\Repository\CategoriesRepository;
use App\Repository\EmissionRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route('/emission/{id}/edit', name: 'emission.edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Emission $emission, Request $request, EntityManagerInterface $em)
    {
        $formEmission = $this->createForm(EmissionType::class, $emission);
        $formEmission->handleRequest($request);
        if ($formEmission->isSubmitted() && $formEmission->isValid()) {
            $em->flush();
            $this->addFlash('success', 'L\'émission a bien été modifié');
            return $this->redirectToRoute('emission.index');
        }
        return $this->render('emission/edit.html.twig', [
            'emission' => $emission,
            'formEmission' => $formEmission
        ]);
    }
    #[Route('/emission/create', name: 'emission.create')]
    public function create(Request $request, EntityManagerInterface $em)
    {
        $emission = new Emission();
        $form = $this->createForm(EmissionType::class, $emission);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $emission->setDatepub(new \DateTime());
            $em->persist($emission);
            $em->flush();
            $this->addFlash('success', 'L\'émission a été crée !');
            return $this->redirectToRoute('emission.index');
        }
        return $this->render('emission/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/emission/{id}', name: 'emission.delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function remove(Emission $emission, EntityManagerInterface $em) {
        $em->remove($emission);
        $em->flush();
        $this->addFlash('success','L\'émission a bien été supprimé');
        return $this->redirectToRoute('emission.index');
    }
}
