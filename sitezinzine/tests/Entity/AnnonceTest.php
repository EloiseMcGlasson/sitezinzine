<?php

namespace App\Tests\Entity;

use App\Entity\Annonce;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

class AnnonceTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $annonce = new Annonce();
        $dateDebut = new \DateTime('2024-01-01');
        $dateFin = new \DateTime('2024-01-02');
        $updateAt = new \DateTime('2024-01-03');

        $annonce
            ->setTitre('Concert Libre')
            ->setOrganisateur('Zinzine Prod')
            ->setVille('Forcalquier')
            ->setDepartement('04')
            ->setAdresse('La Borie')
            ->setDateDebut($dateDebut)
            ->setDateFin($dateFin)
            ->setHoraire('20h')
            ->setPrix('10€')
            ->setPresentation('Présentation de test')
            ->setContact('contact@zinzine.org')
            ->setType('concert')
            ->setValid(true)
            ->setUpdateAt($updateAt)
            ->setThumbnail('annonce.jpg')
            ->setSoftDelete(true);

        $this->assertEquals('Concert Libre', $annonce->getTitre());
        $this->assertEquals('Zinzine Prod', $annonce->getOrganisateur());
        $this->assertEquals('Forcalquier', $annonce->getVille());
        $this->assertEquals('04', $annonce->getDepartement());
        $this->assertEquals('La Borie', $annonce->getAdresse());
        $this->assertEquals($dateDebut, $annonce->getDateDebut());
        $this->assertEquals($dateFin, $annonce->getDateFin());
        $this->assertEquals('20h', $annonce->getHoraire());
        $this->assertEquals('10€', $annonce->getPrix());
        $this->assertEquals('Présentation de test', $annonce->getPresentation());
        $this->assertEquals('contact@zinzine.org', $annonce->getContact());
        $this->assertEquals('concert', $annonce->getType());
        $this->assertTrue($annonce->isValid());
        $this->assertEquals($updateAt, $annonce->getUpdateAt());
        $this->assertEquals('annonce.jpg', $annonce->getThumbnail());
        $this->assertTrue($annonce->isSoftDelete());
    }

    public function testSetThumbnailFileSetsUpdateAt(): void
    {
        /** @var File&\PHPUnit\Framework\MockObject\MockObject $fileMock */
        $fileMock = $this->createMock(File::class);
        $annonce = new Annonce();

    
        $annonce->setThumbnailFile($fileMock);

        $this->assertSame($fileMock, $annonce->getThumbnailFile());
        $this->assertInstanceOf(\DateTime::class, $annonce->getUpdateAt());
    }
}
