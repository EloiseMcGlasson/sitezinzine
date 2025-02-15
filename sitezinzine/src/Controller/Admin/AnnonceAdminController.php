<?php

namespace App\Controller\Admin;

use App\Entity\Annonce;
use App\Form\AnnonceType;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Requirement\Requirement;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/admin/annonce", name: 'admin.annonce.')]
class AnnonceAdminController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
    public function index(Request $request, EntityManagerInterface $em, AnnonceRepository $annonceRepository): Response
    {
        $annonces = $annonceRepository->findAll();
        $annonce = new Annonce();
        $form = $this->createForm(AnnonceType::class, $annonce, [
            'show_valid' => true, // Montrer le champ valid
            'show_annonce' => false, // cacher le reste des champs
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $annonce->setUpdateAt(new \DateTimeImmutable());
            $em->persist($annonce);
            $em->flush();
        $this->addFlash('success', 'L\'annonce a bien été validée');

            return $this->redirectToRoute('admin.annonce.index');
        }
        
        return $this->render('/admin/annonce/index.html.twig', [
            'annonces' => $annonces,
            'form' => $form,
        ]);
    }

    
}

