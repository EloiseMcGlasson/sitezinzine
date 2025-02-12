<?php

namespace App\Controller\Admin;

use App\Form\GetID3Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use getID3;

class GetID3Controller extends AbstractController
{
    #[Route('/getid3', name: 'getid3_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $form = $this->createForm(GetID3Type::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traiter le fichier MP3 et lire les métadonnées
            $file = $form->get('mp3File')->getData();
            if ($file) {
                $filePath = $file->getRealPath();
                $getID3 = new getID3;
                $ThisFileInfo = $getID3->analyze($filePath);

                // Afficher les informations des tags MP3
                return $this->render('admin.tagsMp3.index', [
                    'fileInfo' => $ThisFileInfo,
                ]);
            }
        }

        return $this->render('admin/tagsMp3/showTags.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}