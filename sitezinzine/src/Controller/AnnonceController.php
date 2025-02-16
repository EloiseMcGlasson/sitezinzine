<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Form\AnnonceType;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/annonce", name: 'annonce.')]
class AnnonceController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(AnnonceRepository $annonceRepository): Response
    {
        $annonces = $annonceRepository->findUpcomingAnnonces();
        return $this->render('/home/annonces.html.twig', [
            'annonces' => $annonces,
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        {
            $annonce = new Annonce();
            $form = $this->createForm(AnnonceType::class, $annonce, [
                'show_valid' => false, // Cacher le champ valid
                'show_annonce' => true, // Afficher les champs du form annonce
            ]);
            
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $annonce->setUpdateAt(new \DateTimeImmutable());
                $annonce->setValid(false);
                $em->persist($annonce);
                $em->flush();
            $this->addFlash('success', 'L\'annonce a bien été créee, il faudra attendre sa validation pour qu\'elle s\'affiche');
    
                return $this->redirectToRoute('annonce.index');
            }
    
            return $this->render('/home/annoncesCreate.html.twig', [
                'form' => $form ->createView(),
            ]);
        }
       
    }
    }

