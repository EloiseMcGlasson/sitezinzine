<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class TinyMCEController extends AbstractController
{
    #[Route('/admin/tinymce/upload', name: 'admin.tinymce.upload', methods: ['POST'])]
    public function upload(Request $request, SluggerInterface $slugger): JsonResponse
    {
        // "file" est le nom du champ envoyé par TinyMCE
        $uploadedFile = $request->files->get('file');

        if (!$uploadedFile) {
            return new JsonResponse(['error' => 'Aucun fichier reçu'], 400);
        }

        if (!in_array($uploadedFile->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            return new JsonResponse(['error' => 'Format d’image non supporté'], 400);
        }

        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

        $targetDirectory = $this->getParameter('kernel.project_dir').'/public/uploads/tinymce';

        try {
            $uploadedFile->move($targetDirectory, $newFilename);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur pendant l’upload du fichier'], 500);
        }

        // TinyMCE attend un JSON avec une clé "location"
        $publicUrl = '/uploads/tinymce/'.$newFilename;

        return new JsonResponse(['location' => $publicUrl]);
    }
}
