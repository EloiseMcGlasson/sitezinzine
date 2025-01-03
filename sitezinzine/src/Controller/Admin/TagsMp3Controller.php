<?php

namespace App\Controller;

use App\Entity\TagsMp3;
use App\Form\TagsMp3Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use getID3;

#[IsGranted('ROLE_USER')]
class TagsMp3Controller extends AbstractController
{
    /*  #[Route('/tagsmp3', name: 'tagsmp3_index', methods: ['GET'])]
    public function index(): Response
    {
        // Afficher une page d'accueil ou une liste de fichiers MP3
        return $this->render('tagsmp3/index.html.twig');
    } */

    #[Route('/tagsmp3/new', name: 'tagsmp3_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $tagsMp3 = new TagsMp3();
        $form = $this->createForm(TagsMp3Type::class, $tagsMp3);
        $form->handleRequest($request);

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

            return $this->redirectToRoute('tagsmp3_index');
        }

        return $this->render('tagsmp3/new.html.twig', [
            'tagsMp3' => $tagsMp3,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/tagsmp3/edit', name: 'tagsmp3_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request): Response
    {
        $tagsMp3 = new TagsMp3();
        $form = $this->createForm(TagsMp3Type::class, $tagsMp3);
        $form->handleRequest($request);

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

            return $this->redirectToRoute('tagsmp3_index');
        }

        return $this->render('tagsmp3/edit.html.twig', [
            'tagsMp3' => $tagsMp3,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/tagsmp3/show', name: 'tagsmp3_show', methods: ['GET'])]
    public function show(Request $request): Response
    {
        // Afficher les détails d'un fichier MP3
        $filePath = $request->query->get('file');
        $getID3 = new getID3;
        $ThisFileInfo = $getID3->analyze($filePath);

        return $this->render('tagsmp3/show.html.twig', [
            'fileInfo' => $ThisFileInfo,
        ]);
    }
}
