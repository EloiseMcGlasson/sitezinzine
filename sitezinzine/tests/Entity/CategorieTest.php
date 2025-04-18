<?php

namespace App\Tests\Entity;

use App\Entity\Categories;
use App\Entity\Emission;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

class CategorieTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $categories = new Categories();
        $date = new \DateTime();

        $categories
            ->setTitre('Catégorie Libre')
            ->setOldid(10)
            ->setEditeur(5)
            ->setDuree(45)
            ->setDescriptif('Une catégorie intéressante')
            ->setThumbnail('thumb.jpg')
            ->setUpdatedAt($date)
            ->setActive(true);

        $this->assertEquals('Catégorie Libre', $categories->getTitre());
        $this->assertEquals(10, $categories->getOldid());
        $this->assertEquals(5, $categories->getEditeur());
        $this->assertEquals(45, $categories->getDuree());
        $this->assertEquals('Une catégorie intéressante', $categories->getDescriptif());
        $this->assertEquals('thumb.jpg', $categories->getThumbnail());
        $this->assertEquals($date, $categories->getUpdatedAt());
        $this->assertTrue($categories->isActive());
    }

    public function testSetThumbnailFileSetsUpdatedAt(): void
    {
        /** @var File&\PHPUnit\Framework\MockObject\MockObject $fileMock */

        $fileMock = $this->createMock(File::class);
        $categorie = new Categories();

        $categorie->setThumbnailFile($fileMock);

        $this->assertSame($fileMock, $categorie->getThumbnailFile());
        $this->assertInstanceOf(\DateTime::class, $categorie->getUpdatedAt());
    }

    public function testEmissionsCollection(): void
    {
        $categorie = new Categories();
        /** @var Emission&\PHPUnit\Framework\MockObject\MockObject $emission */
        $emission = $this->createMock(Emission::class);

        $emission->expects($this->once())
                 ->method('setCategorie')
                 ->with($categorie);

        $categorie->addEmission($emission);

        $this->assertCount(1, $categorie->getEmissions());
        $this->assertTrue($categorie->getEmissions()->contains($emission));

        $categorie->removeEmission($emission);
        $this->assertCount(0, $categorie->getEmissions());
    }
}
