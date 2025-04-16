<?php

namespace App\Controller\Admin;

use App\Entity\Emission;

use App\Form\EmissionType;
use App\Repository\EmissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/emission', name: 'admin.emission.')]
#[IsGranted('ROLE_USER')]
class EmissionController extends AbstractController
{
    #[Route('/', name: 'index')]
public function index(Request $request, EmissionRepository $repository, Security $security): Response
{
    $page = $request->query->getInt('page', 1);
    $limit = 25;

    $user = $security->getUser();
    $isAdmin = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN');

    $emissions = $repository->paginateEmissionsAdmin($page, '', $user, $isAdmin);
    $maxPage = (int) ceil($emissions->getTotalItemCount() / $limit);

    return $this->render('admin/emission/index.html.twig', [
        'emissions' => $emissions,
        'maxPage' => $maxPage,
    ]);
}


    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function show(Emission $emission): Response
    {
        return $this->render('admin/emission/show.html.twig', [
            'emission' => $emission,
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $emission = new Emission();
        $form = $this->createForm(EmissionType::class, $emission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $now = new \DateTime();
            $emission
                ->setDatepub($now)
                ->setUpdatedat($now)
                ->setUser($security->getUser());

            $em->persist($emission);
            $em->flush();

            $this->addFlash('success', 'L\'émission a été créée !');

            return $this->redirectToRoute('admin.emission.index');
        }

        return $this->render('admin/emission/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
    public function edit(Request $request, Emission $emission, EntityManagerInterface $em, Security $security): Response
    {
        if (
            !$this->isGranted('ROLE_ADMIN') &&
            !$this->isGranted('ROLE_SUPER_ADMIN') &&
            $emission->getUser() !== $security->getUser()
        ) {
            throw $this->createAccessDeniedException('Vous n\'avez pas les droits pour modifier cette émission.');
        }

        $form = $this->createForm(EmissionType::class, $emission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$emission->getUser()) {
                $emission->setUser($security->getUser());
            }

            $emission->setUpdatedat(new \DateTime());
            $em->flush();

            $this->addFlash('success', 'L\'émission a bien été modifiée.');

            return $this->redirectToRoute('admin.emission.index');
        }

        return $this->render('admin/emission/edit.html.twig', [
            'emission' => $emission,
            'formEmission' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(Emission $emission, EntityManagerInterface $em): Response
    {
        $em->remove($emission);
        $em->flush();

        $this->addFlash('success', 'L\'émission a bien été supprimée.');

        return $this->redirectToRoute('admin.emission.index');
    }
}
