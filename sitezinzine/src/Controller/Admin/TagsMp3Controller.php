<?php

namespace App\Controller\Admin;

use App\Form\GetID3Type;
use App\Model\TagsMp3;
use App\Form\TagsMp3Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use getID3;

#[Route("/admin/tagsmp3", name: 'admin.tagsmp3.')]
#[IsGranted('ROLE_USER')]
class TagsMp3Controller extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, TagsMp3 $tagsMp3): Response
    {
        
        $filename = 'C:\Users\mcgla\xampp\htdocs\siteZinzine\sitezinzine\sitezinzine\public\emissionsMp3\emission test.mp3';
        $getID3 = new getID3;
        $ThisFileInfo = $getID3->analyze($filename);
        $getID3->CopyTagsToComments($ThisFileInfo);
        //dd($getID3);
        $imageData = null;
        $imageMime = null;
        
        $metadata = [
            'title'  => $fileInfo['tags']['id3v2']['title'][0] ?? null,
            'artist' => $fileInfo['tags']['id3v2']['artist'][0] ?? null,
            'album'  => $fileInfo['tags']['id3v2']['album'][0] ?? null,
            'year'   => isset($fileInfo['tags']['id3v2']['year'][0]) ? (int) $fileInfo['tags']['id3v2']['year'][0] : null,
        ];
        
        $formTags = $this->createForm(GetID3Type::class, $metadata);
        if (isset($ThisFileInfo['comments']['picture'][0])) {
            $imageData = base64_encode($ThisFileInfo['comments']['picture'][0]['data']);
            $imageMime = $ThisFileInfo['comments']['picture'][0]['image_mime'];
        }

        return $this->render('admin/tagsmp3/show.html.twig', [
            'formTags' => $formTags,
            'fileInfo' => $ThisFileInfo['comments'],
            'imageData' => $imageData,
            'imageMime' => $imageMime,
        ]);
    }
}
