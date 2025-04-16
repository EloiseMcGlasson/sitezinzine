<?php

namespace App\Tests\Repository;

use App\Entity\Emission;
use App\Entity\Categories;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Repository\EmissionRepository;

class EmissionRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private EmissionRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->repository = $container->get(EmissionRepository::class);
    }

    public function testFindWithDureeLowerThan(): void
    {
        $now = new \DateTime();

        $emission1 = (new Emission())
            ->setTitre('Emission Courte')
            ->setKeyword('test')
            ->setDatepub($now)
            ->setRef('ref1')
            ->setDuree(90)
            ->setUrl('https://test.com/1')
            ->setDescriptif('courte')
            ->setThumbnail('thumb1.jpg');

        $emission2 = (new Emission())
            ->setTitre('Emission Longue')
            ->setKeyword('test2')
            ->setDatepub($now)
            ->setRef('ref2')
            ->setDuree(180)
            ->setUrl('https://test.com/2')
            ->setDescriptif('longue')
            ->setThumbnail('thumb2.jpg');

        $this->em->persist($emission1);
        $this->em->persist($emission2);
        $this->em->flush();

        $results = $this->repository->findWithDureeLowerThan(150);

        $this->assertCount(1, $results);
        $this->assertSame('Emission Courte', $results[0]->getTitre());
    }

    public function testPaginateEmissionsExcludesUrl(): void
{
    $now = new \DateTime();

    $excludedEmission = (new Emission())
        ->setTitre('Exclue')
        ->setKeyword('test')
        ->setDatepub($now)
        ->setRef('refX')
        ->setDuree(100)
        ->setUrl('https://exclude.com')
        ->setDescriptif('A exclure')
        ->setThumbnail('thumbX.jpg');

    $includedEmission = (new Emission())
        ->setTitre('Incluse')
        ->setKeyword('test')
        ->setDatepub($now)
        ->setRef('refY')
        ->setDuree(120)
        ->setUrl('https://include.com')
        ->setDescriptif('A inclure')
        ->setThumbnail('thumbY.jpg');

    $this->em->persist($excludedEmission);
    $this->em->persist($includedEmission);
    $this->em->flush();

    $pagination = $this->repository->paginateEmissions(1, 'https://exclude.com');

    $this->assertInstanceOf(\Knp\Component\Pager\Pagination\PaginationInterface::class, $pagination);
    $this->assertCount(1, $pagination->getItems());

    $this->assertEquals('Incluse', $pagination->getItems()[0]->getTitre());
}

public function testLastEmissionsByTheme(): void
{
    $now = new \DateTime();

    // Création et insertion des thèmes
    $theme1 = new \App\Entity\Theme();
    $theme1->setName('Thème A');
    $theme1->setUpdatedat($now);

    $theme2 = new \App\Entity\Theme();
    $theme2->setName('Thème B');
    $theme2->setUpdatedat($now);

    $this->em->persist($theme1);
    $this->em->persist($theme2);
    $this->em->flush(); // Très important : on flush les thèmes pour qu'ils aient un ID

    // Création des émissions
    $emissionOld = (new Emission())
        ->setTitre('Ancienne')
        ->setKeyword('kw')
        ->setDatepub((clone $now)->modify('-2 days'))
        ->setRef('refOld')
        ->setDuree(60)
        ->setUrl('https://test.com/old')
        ->setDescriptif('ancienne émission')
        ->setThumbnail('old.jpg')
        ->setTheme($theme1)
        ->setUpdatedat($now);

    $emissionNew = (new Emission())
        ->setTitre('Récente')
        ->setKeyword('kw')
        ->setDatepub($now)
        ->setRef('refNew')
        ->setDuree(120)
        ->setUrl('https://test.com/new')
        ->setDescriptif('récente émission')
        ->setThumbnail('new.jpg')
        ->setTheme($theme1)
        ->setUpdatedat($now);

    $emissionTheme2 = (new Emission())
        ->setTitre('Thème 2')
        ->setKeyword('kw')
        ->setDatepub($now)
        ->setRef('refT2')
        ->setDuree(90)
        ->setUrl('https://test.com/t2')
        ->setDescriptif('émission t2')
        ->setThumbnail('t2.jpg')
        ->setTheme($theme2)
        ->setUpdatedat($now);

    $this->em->persist($emissionOld);
    $this->em->persist($emissionNew);
    $this->em->persist($emissionTheme2);
    $this->em->flush();

    $results = $this->repository->lastEmissionsByTheme('');

    $titles = array_map(fn ($row) => $row['emission_titre'], $results);

    $this->assertCount(2, $results);
    $this->assertContains('Récente', $titles);
    $this->assertContains('Thème 2', $titles);
}

public function testFindBySearchWithTitle(): void
{
    $now = new \DateTime();
    
    // Créer une émission de test
    $emission = (new Emission())
        ->setTitre('Test Recherche')
        ->setKeyword('test')
        ->setDatepub($now)
        ->setRef('REF123')
        ->setDuree(60)
        ->setUrl('https://test.com/search')
        ->setDescriptif('Description test')
        ->setUpdatedat($now);

    $this->em->persist($emission);
    $this->em->flush();

    // Test recherche par titre
    $criteria = ['titre' => 'Recherche'];
    $result = $this->repository->findBySearch($criteria);

    $this->assertInstanceOf(PaginationInterface::class, $result);
    $this->assertCount(1, $result->getItems());
    $this->assertEquals('Test Recherche', $result->getItems()[0]->getTitre());
}

public function testFindBySearchWithDateRange(): void
{
    $now = new \DateTime();
    $yesterday = (clone $now)->modify('-1 day');
    $tomorrow = (clone $now)->modify('+1 day');
    
    // Créer une émission de test
    $emission = (new Emission())
        ->setTitre('Test Date')
        ->setKeyword('test')
        ->setDatepub($now)
        ->setRef('REF124')
        ->setDuree(60)
        ->setUrl('https://test.com/date')
        ->setDescriptif('Test date range')
        ->setUpdatedat($now);

    $this->em->persist($emission);
    $this->em->flush();

    // Test recherche par plage de dates
    $criteria = [
        'dateDebut' => $yesterday,
        'dateFin' => $tomorrow
    ];
    
    $result = $this->repository->findBySearch($criteria);

    $this->assertInstanceOf(PaginationInterface::class, $result);
    $this->assertCount(1, $result->getItems());
    $this->assertEquals('Test Date', $result->getItems()[0]->getTitre());
}

public function testFindBySearchWithCategory(): void
{
    $now = new \DateTime();
    
    // Créer une catégorie
    $categorie = (new Categories())
        ->setTitre('Catégorie Test')
        ->setDescriptif('Description catégorie')
        ->setActive(true)
        ->setUpdatedat($now)
        ->setEditeur(1)
        ->setDuree(60);

    $this->em->persist($categorie);
    
    // Créer une émission
    $emission = (new Emission())
        ->setTitre('Test Catégorie')
        ->setKeyword('test')
        ->setDatepub($now)
        ->setRef('REF125')
        ->setDuree(60)
        ->setUrl('https://test.com/category')
        ->setDescriptif('Test catégorie')
        ->setCategorie($categorie)
        ->setUpdatedat($now);

    $this->em->persist($emission);
    $this->em->flush();

    // Test recherche par catégorie
    $criteria = ['categorie' => $categorie];
    $result = $this->repository->findBySearch($criteria);

    $this->assertInstanceOf(PaginationInterface::class, $result);
    $this->assertCount(1, $result->getItems());
    $this->assertEquals('Test Catégorie', $result->getItems()[0]->getTitre());
}

public function testFindBySearchWithMultipleCriteria(): void
{
    $now = new \DateTime();
    $yesterday = (clone $now)->modify('-1 day');
    $tomorrow = (clone $now)->modify('+1 day');
    
    // Créer une catégorie
    $categorie = (new Categories())
        ->setTitre('Multi Test')
        ->setDescriptif('Description multi')
        ->setActive(true)
        ->setUpdatedat($now)
        ->setEditeur(1)
        ->setDuree(60);

    $this->em->persist($categorie);
    
    // Créer une émission
    $emission = (new Emission())
        ->setTitre('Test Multiple')
        ->setKeyword('test')
        ->setDatepub($now)
        ->setRef('REF126')
        ->setDuree(60)
        ->setUrl('https://test.com/multiple')
        ->setDescriptif('Test critères multiples')
        ->setCategorie($categorie)
        ->setUpdatedat($now);

    $this->em->persist($emission);
    $this->em->flush();

    // Test avec plusieurs critères
    $criteria = [
        'titre' => 'Multiple',
        'categorie' => $categorie,
        'dateDebut' => $yesterday,
        'dateFin' => $tomorrow
    ];
    
    $result = $this->repository->findBySearch($criteria);

    $this->assertInstanceOf(PaginationInterface::class, $result);
    $this->assertCount(1, $result->getItems());
    $this->assertEquals('Test Multiple', $result->getItems()[0]->getTitre());
}


    protected function tearDown(): void
    {
        parent::tearDown();

        // Nettoyage de la table après le test
        $this->em->createQuery('DELETE FROM App\Entity\Emission e')->execute();
        $this->em->close();
    }
}
