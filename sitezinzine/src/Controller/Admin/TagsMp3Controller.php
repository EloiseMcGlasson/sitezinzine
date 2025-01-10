<?php

namespace App\Controller\Admin;

use App\Model\TagsMp3;
use App\Form\TagsMp3Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use getID3;

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

    #[Route('/tagsmp3/edit', name: 'tags_mp3_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request): Response
    {
        $form = $this->createForm(TagsMp3Type::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traiter le fichier MP3 et lire les métadonnées
            $file = $form->get('logo')->getData();
            if ($file) {
                $filePath = $file->getRealPath();
                $getID3 = new getID3;
                $ThisFileInfo = $getID3->analyze($filePath);

                // Afficher les informations des tags MP3
                return $this->render('tags_mp3/show.html.twig', [
                    'fileInfo' => $ThisFileInfo,
                ]);
            }
        }

        return $this->render('tags_mp3/edit.html.twig', [
            'form' => $form->createView(),
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
