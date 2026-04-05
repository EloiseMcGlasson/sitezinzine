<?php

namespace App\Controller\Admin;

use App\Entity\ProgrammationRule;
use App\Form\ProgrammationRuleType;
use App\Repository\ProgrammationRuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/programmationRule', name: 'admin_programmationRule_')]
#[IsGranted('ROLE_ADMIN')]
class ProgrammationRuleController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(ProgrammationRuleRepository $repository): Response
    {
        return $this->render('admin/programmationRule/index.html.twig', [
            'rules' => $repository->findAllNotDeleted(),
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $programmationRule = new ProgrammationRule();

        $form = $this->createForm(ProgrammationRuleType::class, $programmationRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($programmationRule);
            $em->flush();

            $this->addFlash('success', 'Règle créée avec succès.');

            return $this->redirectToRoute('admin_programmationRule_index');
        }

        return $this->render('admin/programmationRule/create.html.twig', [
            'form' => $form,
            'rule' => $programmationRule,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, ProgrammationRule $programmationRule, EntityManagerInterface $em): Response
    {
        if ($programmationRule->isDeleted()) {
            $this->addFlash('danger', 'Règle supprimée.');
            return $this->redirectToRoute('admin_programmationRule_index');
        }

        $form = $this->createForm(ProgrammationRuleType::class, $programmationRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Règle mise à jour.');

            return $this->redirectToRoute('admin_programmationRule_index');
        }

        return $this->render('admin/programmationRule/edit.html.twig', [
            'form' => $form,
            'rule' => $programmationRule,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, ProgrammationRule $programmationRule, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete_rule_' . $programmationRule->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('admin_programmationRule_index');
        }

        $programmationRule->softDelete();
        $em->flush();

        $this->addFlash('success', 'Règle supprimée.');

        return $this->redirectToRoute('admin_programmationRule_index');
    }
}