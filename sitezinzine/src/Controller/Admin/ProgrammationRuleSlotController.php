<?php

namespace App\Controller\Admin;

use App\Entity\ProgrammationRule;
use App\Entity\ProgrammationRuleSlot;
use App\Form\ProgrammationRuleSlotType;
use App\Repository\ProgrammationRuleSlotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/programmationRule/{ruleId}/slots', name: 'admin_programmationRuleSlot_')]
#[IsGranted('ROLE_ADMIN')]
class ProgrammationRuleSlotController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        int $ruleId,
        ProgrammationRuleSlotRepository $slotRepository,
        EntityManagerInterface $em
    ): Response {
        $rule = $em->getRepository(ProgrammationRule::class)->find($ruleId);

        if (!$rule || $rule->isDeleted()) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/programmationRuleSlot/index.html.twig', [
            'rule' => $rule,
            'slots' => $slotRepository->findNotDeletedByRule($rule),
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(
        int $ruleId,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $rule = $em->getRepository(ProgrammationRule::class)->find($ruleId);

        if (!$rule || $rule->isDeleted()) {
            throw $this->createNotFoundException();
        }

        $slot = new ProgrammationRuleSlot();
        $slot->setRule($rule);

        $form = $this->createForm(ProgrammationRuleSlotType::class, $slot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($slot);
            $em->flush();

            $this->addFlash('success', 'Créneau ajouté.');

            return $this->redirectToRoute('admin_programmationRuleSlot_index', [
                'ruleId' => $ruleId,
            ]);
        }

        return $this->render('admin/programmationRuleSlot/create.html.twig', [
            'form' => $form,
            'rule' => $rule,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        int $ruleId,
        ProgrammationRuleSlot $slot,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($slot->isDeleted()) {
            return $this->redirectToRoute('admin_programmationRuleSlot_index', ['ruleId' => $ruleId]);
        }

        $form = $this->createForm(ProgrammationRuleSlotType::class, $slot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Créneau modifié.');

            return $this->redirectToRoute('admin_programmationRuleSlot_index', [
                'ruleId' => $ruleId,
            ]);
        }

        return $this->render('admin/programmationRuleSlot/edit.html.twig', [
            'form' => $form,
            'rule' => $slot->getRule(),
            'slot' => $slot,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        int $ruleId,
        ProgrammationRuleSlot $slot,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if (!$this->isCsrfTokenValid('delete_slot_' . $slot->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('admin_programmationRuleSlot_index', ['ruleId' => $ruleId]);
        }

        $slot->softDelete();
        $em->flush();

        $this->addFlash('success', 'Créneau supprimé.');

        return $this->redirectToRoute('admin_programmationRuleSlot_index', [
            'ruleId' => $ruleId,
        ]);
    }
}