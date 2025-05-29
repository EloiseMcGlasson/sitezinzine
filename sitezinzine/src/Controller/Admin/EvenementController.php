<?php

namespace App\Controller\Admin;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/admin/evenement', name: 'admin.evenement.')]
#[IsGranted('ROLE_EDITOR')]
class EvenementController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
    public function index(Request $request, EntityManagerInterface $em, EvenementRepository $evenementRepository): Response
    {
        $evenements = $evenementRepository->findAllDesc();
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement, [
            'show_valid' => true, // Montrer le champ valid

        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $evenement->setUpdateAt(new \DateTime());
            $em->persist($evenement);
            $em->flush();
            $this->addFlash('success', 'L\'évènement a bien été validée');

            return $this->redirectToRoute('admin.evenement.index');
        }

        return $this->render('/admin/evenement/index.html.twig', [
            'evenements' => $evenements,
            'form' => $form,
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $em, Security $security)

    {
        $evenement = new Evenement();
        $userId = $security->getUser();
        
        $form = $this->createForm(EvenementType::class, $evenement);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $evenement->setUpdateAt(new \DateTime());
            $evenement->setSoftDelete(false);
            $evenement->setValid(false);
            $evenement->setUser($userId);


            $em->persist($evenement);
            $em->flush();
            $this->addFlash('success', 'L\'évènement a été crée !');
            return $this->redirectToRoute('admin.evenement.index');
        }
        return $this->render('admin/evenement/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
    public function edit(Evenement $evenement, Request $request, EntityManagerInterface $em, Security $security)
    {
        $form = $this->createForm(EvenementType::class, $evenement, [
            'show_valid' => true, // Montrer le champ valid

        ]);

        $form->handleRequest($request);
        $userId = $security->getUser();
        
        if ($form->isSubmitted() && $form->isValid()) {

            if (!$evenement->getUser()){
                $evenement->setUser($userId);
            }
            // ✅ Récupérer la valeur du champ "Autre type"
            $autreType = $form->get('autreType')->getData();

            // ✅ Si "Autre" est sélectionné et que le champ "Autre type" est rempli, on l'enregistre
            if ($evenement->getType() === 'autre' && !empty($autreType)) {
                $evenement->setType($autreType);
            }
            $evenement->setUpdateAt(new \DateTime());
            $em->flush();
            $this->addFlash('success', 'L\'évènement a bien été modifié');
            return $this->redirectToRoute('admin.evenement.index');
        }
        return $this->render('admin/evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function show(Evenement $evenement, int $id, EvenementRepository $evenementRepository)
    {
        $evenement = $evenementRepository->find($id);
        return $this->render('admin/evenement/show.html.twig', [
            'evenement' => $evenement,

        ]);
    }

    #[Route('/{id}', name: 'softDelete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function remove(Evenement $evenement, EntityManagerInterface $em)
    {
        $evenement->setSoftDelete(true);

        $em->flush();
        $this->addFlash('success', 'L\'évènement a bien été supprimé');
        return $this->redirectToRoute('admin.evenement.index');
    }

    #[Route('/{id}/valid', name: 'valid', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    public function valid(Evenement $evenement, EntityManagerInterface $em)
    {
        $evenement->setValid(true);

        $em->flush();
        $this->addFlash('success', 'L\'évènement a bien été validé');
        return $this->redirectToRoute('admin.evenement.index');
    }

    #[Route('/{id}/unvalid', name: 'unvalid', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    public function unvalid(Evenement $evenement, EntityManagerInterface $em)
    {
        $evenement->setValid(false);

        $em->flush();
        $this->addFlash('success', 'L\'évènement a bien été dé-validé');
        return $this->redirectToRoute('admin.evenement.index');
    }
}
