<?php

namespace App\Controller\API;

use App\Entity\Emission;
use App\Repository\EmissionRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class EmissionsController extends AbstractController
{


#[Route("/api/emissions", methods: ["GET"])]
Public function index(EmissionRepository $repository, Request $request)
{
    $emissions = $repository->paginateEmissions($request->query->getInt('page', 1), '');
    return $this->json($emissions, 200, [], [
        'groups' => ['emissions.index']
    ]);

}

#[Route("/api/emissions/{id}", requirements: ['id' => Requirement::DIGITS])]
Public function show(Emission $emission)
{
    
    return $this->json($emission, 200, [], [
        'groups' => ['emissions.index']
    ]);

}

#[Route("/api/emissions", methods: ["POST"])]
Public function create(Request $request, SerializerInterface $serializer)
{
    $emission = new Emission();
    $emission->setDatepub(new DateTimeImmutable());
    $emission->setUpdatedat(new DateTimeImmutable());
    dd($serializer->deserialize($request->getContent(), Emission::class, 'json', [
        AbstractNormalizer::OBJECT_TO_POPULATE => $emission,
        'groups' => ['emissions.create']
    ]));

}

}