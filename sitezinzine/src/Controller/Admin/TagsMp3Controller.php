<?php

namespace App\Controller;

use App\Model\TagsMp3;
use App\Form\TagsMp3Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TagsMp3Controller extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/tags/new', name: 'tags_mp3_new')]
    public function new(Request $request): Response
    {
        $tagsMp3 = new TagsMp3();
        $form = $this->createForm(TagsMp3Type::class, $tagsMp3);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si un fichier logo est téléchargé, gère son traitement ici
            /** @var File $logoFile */
            $logoFile = $tagsMp3->getLogo();
            if ($logoFile) {
                $fileName = uniqid().'.'.$logoFile->guessExtension();
                $logoFile->move($this->getParameter('logos_directory'), $fileName);
                $tagsMp3->setLogo($fileName); // Enregistre le nom du fichier dans l'entité
            }

            // Sauvegarde l'entité en base de données
            $this->entityManager->persist($tagsMp3);
            $this->entityManager->flush();

            // Redirection ou réponse après la soumission
            return $this->redirectToRoute('tags_mp3_list'); // Remplacez par votre route de liste
        }

        return $this->render('tags_mp3/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/tags/{id}/edit', name: 'tags_mp3_edit')]
    public function edit(Request $request, TagsMp3 $tagsMp3): Response
    {
        $form = $this->createForm(TagsMp3Type::class, $tagsMp3);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si un nouveau fichier logo est téléchargé, gère son traitement ici
            /** @var File $logoFile */
            $logoFile = $tagsMp3->getLogo();
            if ($logoFile) {
                $fileName = uniqid().'.'.$logoFile->guessExtension();
                $logoFile->move($this->getParameter('logos_directory'), $fileName);
                $tagsMp3->setLogo($fileName); // Enregistre le nom du fichier dans l'entité
            }

            // Sauvegarde les modifications en base de données
            $this->entityManager->flush();

            // Redirection ou réponse après la soumission
            return $this->redirectToRoute('tags_mp3_list'); // Remplacez par votre route de liste
        }

        return $this->render('tags_mp3/edit.html.twig', [
            'form' => $form->createView(),
            'tagsMp3' => $tagsMp3,
        ]);
    }

    #[Route('/tags', name: 'tags_mp3_list')]
    public function list(): Response
    {
        $tagsMp3List = $this->entityManager->getRepository(TagsMp3::class)->findAll();

        return $this->render('tags_mp3/list.html.twig', [
            'tagsMp3List' => $tagsMp3List,
        ]);
    }
}
