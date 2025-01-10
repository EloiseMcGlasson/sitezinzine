<?php

namespace App\Controller\Admin;

use App\Model\TagsMp3;
use App\Form\TagsMp3Type;
use App\Entity\Emission;
use Symfony\Bundle\SecurityBundle\Security;
use App\Form\EmissionType;
use App\Repository\EmissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use getID3;

#[Route("/admin/emission", name: 'admin.emission.')]
#[IsGranted('ROLE_USER')]
class EmissionController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, EmissionRepository $emissionRepository): Response
    {
    
        $page = $request->query->getInt('page', 1);
        $limit= 25;
        $emissions = $emissionRepository->paginateEmissions($page, '');
        $maxPage = ceil($emissions->getTotalItemCount() / $limit);
        //dd($emissions->count());
        return $this->render('admin/emission/index.html.twig', [
            'emissions' => $emissions,
            

        ]);
    }
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function show(Emission $emission, int $id, EmissionRepository $emissionRepository)
    {
        $emission = $emissionRepository->find($id);
        return $this->render('admin/emission/show.html.twig', [
            'emission' => $emission,
            
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
    public function edit(Emission $emission, Request $request, EntityManagerInterface $em, Security $security)
    {
        $formEmission = $this->createForm(EmissionType::class, $emission);
        $formEmission->handleRequest($request);
        $userId = $security->getUser();
        $tagsMp3 = new TagsMp3();
        $form = $this->createForm(TagsMp3Type::class, $tagsMp3);
        $form->handleRequest($request);
        if ($formEmission->isSubmitted() && $formEmission->isValid()) {
            if (!$emission->getUser()){
                $emission->setUser($userId);
            }
            $emission->setUpdatedat(new \DateTime());
            $em->flush();
            $this->addFlash('success', 'L\'émission a bien été modifié');
            return $this->redirectToRoute('admin.emission.index');
        }
        if ($form->isSubmitted() && $form->isValid()) {
            // Traiter le fichier MP3 et mettre à jour les métadonnées
            $file = $form->get('logo')->getData();
            if ($file) {
                $filePath = $file->getRealPath();
                $getID3 = new getID3;
                $ThisFileInfo = $getID3->analyze($filePath);

                // Mettre à jour les métadonnées du fichier MP3
                // (ajoutez ici le code pour modifier les métadonnées en utilisant getID3)
            }
            return $this->redirectToRoute('admin.emission.index');
        }
        return $this->render('admin/emission/edit.html.twig', [
            'emission' => $emission,
            'formEmission' => $formEmission,
            'tagsMp3' => $tagsMp3,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $em, Security $security)

    {
        $emission = new Emission();
        $userId = $security->getUser();
        $form = $this->createForm(EmissionType::class, $emission);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $emission->setDatepub(new \DateTime());
            $emission->setUpdatedat(new \DateTime());
            $emission->setUser($userId);
            $em->persist($emission);
            $em->flush();
            $this->addFlash('success', 'L\'émission a été crée !');
            return $this->redirectToRoute('admin.emission.index');
        }
        return $this->render('admin/emission/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function remove(Emission $emission, EntityManagerInterface $em)
    {
        $em->remove($emission);
        $em->flush();
        $this->addFlash('success', 'L\'émission a bien été supprimé');
        return $this->redirectToRoute('admin.emission.index');
    }
}
