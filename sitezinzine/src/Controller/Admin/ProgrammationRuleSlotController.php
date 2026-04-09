<?php

namespace App\Controller\Admin;

use App\Entity\ProgrammationRule;
use App\Entity\ProgrammationRuleSlot;
use App\Form\ProgrammationRuleSlotType;
use App\Repository\ProgrammationRuleSlotRepository;
use App\Repository\ProgrammationRuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
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
        ProgrammationRuleRepository $programmationRuleRepository,
        ProgrammationRuleSlotRepository $slotRepository
    ): Response {
        $programmationRule = $programmationRuleRepository->find($ruleId);

        if (!$programmationRule || $programmationRule->isDeleted()) {
            throw $this->createNotFoundException('Règle de programmation introuvable.');
        }

        return $this->render('admin/programmationRuleSlot/index.html.twig', [
            'rule' => $programmationRule,
            'slots' => $slotRepository->findNotDeletedByRule($programmationRule),
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(
        int $ruleId,
        Request $request,
        ProgrammationRuleRepository $programmationRuleRepository,
        EntityManagerInterface $em
    ): Response {
        $programmationRule = $programmationRuleRepository->find($ruleId);

        if (!$programmationRule || $programmationRule->isDeleted()) {
            throw $this->createNotFoundException('Règle de programmation introuvable.');
        }

        $programmationRuleSlot = new ProgrammationRuleSlot();
        $programmationRuleSlot->setRule($programmationRule);

        $form = $this->createForm(ProgrammationRuleSlotType::class, $programmationRuleSlot);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->validateSlotForm($form, $programmationRuleSlot);

            if ($form->isValid()) {
                $em->persist($programmationRuleSlot);
                $em->flush();

                $this->addFlash('success', 'Le créneau a bien été créé.');

                return $this->redirectToRoute('admin_programmationRuleSlot_index', [
                    'ruleId' => $programmationRule->getId(),
                ]);
            }
        }

        return $this->render('admin/programmationRuleSlot/create.html.twig', [
            'form' => $form->createView(),
            'rule' => $programmationRule,
            'form_title' => 'Créer un créneau',
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(
        int $ruleId,
        ProgrammationRuleSlot $programmationRuleSlot,
        Request $request,
        ProgrammationRuleRepository $programmationRuleRepository,
        EntityManagerInterface $em
    ): Response {
        $programmationRule = $programmationRuleRepository->find($ruleId);

        if (!$programmationRule || $programmationRule->isDeleted()) {
            throw $this->createNotFoundException('Règle de programmation introuvable.');
        }

        if ($programmationRuleSlot->isDeleted()) {
            $this->addFlash('danger', 'Ce créneau a été supprimé.');

            return $this->redirectToRoute('admin_programmationRuleSlot_index', [
                'ruleId' => $ruleId,
            ]);
        }

        if ($programmationRuleSlot->getRule()?->getId() !== $programmationRule->getId()) {
            throw $this->createNotFoundException('Ce créneau n’appartient pas à cette règle.');
        }

        $form = $this->createForm(ProgrammationRuleSlotType::class, $programmationRuleSlot);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->validateSlotForm($form, $programmationRuleSlot);

            if ($form->isValid()) {
                $em->flush();

                $this->addFlash('success', 'Le créneau a bien été modifié.');

                return $this->redirectToRoute('admin_programmationRuleSlot_index', [
                    'ruleId' => $programmationRule->getId(),
                ]);
            }
        }

        return $this->render('admin/programmationRuleSlot/edit.html.twig', [
            'form' => $form->createView(),
            'rule' => $programmationRule,
            'slot' => $programmationRuleSlot,
            'form_title' => 'Modifier un créneau',
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(
        int $ruleId,
        ProgrammationRuleSlot $programmationRuleSlot,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $programmationRule = $em->getRepository(ProgrammationRule::class)->find($ruleId);

        if (!$programmationRule || $programmationRule->isDeleted()) {
            throw $this->createNotFoundException('Règle de programmation introuvable.');
        }

        if ($programmationRuleSlot->getRule()?->getId() !== $programmationRule->getId()) {
            throw $this->createNotFoundException('Ce créneau n’appartient pas à cette règle.');
        }

        if (!$this->isCsrfTokenValid(
            'delete_slot_' . $programmationRuleSlot->getId(),
            (string) $request->request->get('_token')
        )) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('admin_programmationRuleSlot_index', [
                'ruleId' => $programmationRule->getId(),
            ]);
        }

        if ($programmationRuleSlot->isDeleted()) {
            $this->addFlash('warning', 'Ce créneau est déjà supprimé.');

            return $this->redirectToRoute('admin_programmationRuleSlot_index', [
                'ruleId' => $programmationRule->getId(),
            ]);
        }

        $programmationRuleSlot->softDelete();
        $em->flush();

        $this->addFlash('success', 'Le créneau a bien été supprimé.');

        return $this->redirectToRoute('admin_programmationRuleSlot_index', [
            'ruleId' => $programmationRule->getId(),
        ]);
    }

    private function validateSlotForm($form, ProgrammationRuleSlot $programmationRuleSlot): void
    {
        $recurrenceType = $programmationRuleSlot->getRecurrenceType();
        $monthlyOccurrence = $programmationRuleSlot->getMonthlyOccurrence();
        $monthInterval = $programmationRuleSlot->getMonthInterval();
        $weekOffset = $programmationRuleSlot->getWeekOffset();
        $broadcastRank = $programmationRuleSlot->getBroadcastRank();
        $durationMinutes = $programmationRuleSlot->getDurationMinutes();

        if ($recurrenceType === ProgrammationRuleSlot::RECURRENCE_WEEKLY) {
            $programmationRuleSlot->setMonthlyOccurrence(null);
            $programmationRuleSlot->setMonthInterval(1);
        }

        if ($recurrenceType === ProgrammationRuleSlot::RECURRENCE_MONTHLY) {
            if ($monthlyOccurrence === null) {
                $form->get('monthlyOccurrence')->addError(
                    new FormError('Ce champ est obligatoire pour une programmation mensuelle.')
                );
            }

            if ($monthInterval < 1) {
                $form->get('monthInterval')->addError(
                    new FormError('L’intervalle mensuel doit être supérieur ou égal à 1.')
                );
            }
        }

        if (!in_array($weekOffset, [0, 1, 2, 3, 4], true)) {
            $form->get('weekOffset')->addError(
                new FormError('Le décalage en semaines radio sélectionné est invalide.')
            );
        }

        if (!in_array($broadcastRank, [1, 2, 3], true)) {
            $form->get('broadcastRank')->addError(
                new FormError('L’ordre de diffusion sélectionné est invalide.')
            );
        }

        if ($durationMinutes === null || $durationMinutes < 1) {
            $form->get('durationMinutes')->addError(
                new FormError('La durée doit être supérieure à 0.')
            );
        }
    }
}
