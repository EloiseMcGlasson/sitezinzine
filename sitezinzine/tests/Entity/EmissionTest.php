<?php

namespace App\Tests\Entity;

use App\Entity\Emission;
use App\Entity\Categories;
use App\Entity\Theme;
use App\Entity\User;
use App\Entity\Editeur;
use App\Entity\InviteOldAnimateur;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\MockObjectTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\CollectionTrait;
use Doctrine\Common\Collections\ArrayCollectionTrait;



class EmissionTest extends TestCase
{
    protected function createMock(string $originalClassName): MockObject
    {
        return parent::createMock($originalClassName);
    }

    public function testInitialValues(): void
    {
        $emission = new Emission();

        $this->assertNull($emission->getId());
        $this->assertSame('', $emission->getTitre());
        $this->assertNull($emission->getKeyword());
        $this->assertNull($emission->getDatepub());
        $this->assertNull($emission->getRef());
        $this->assertNull($emission->getDuree());
        $this->assertSame('', $emission->getUrl());
        $this->assertSame('', $emission->getDescriptif());
        $this->assertNull($emission->getCategorie());
        $this->assertNull($emission->getThumbnail());
        $this->assertNull($emission->getThumbnailFile());
        $this->assertNull($emission->getThumbnailMp3());
        $this->assertNull($emission->getThumbnailFileMp3());
        $this->assertNull($emission->getUpdatedat());
        $this->assertNull($emission->getTheme());
        $this->assertNull($emission->getUser());
        $this->assertNull($emission->getEditeur());
        $this->assertCount(0, $emission->getInviteOldAnimateurs());
    }

    public function testSetters(): void
    {
        $emission = new Emission();

        $datepub = new \DateTime('2023-01-01');
        $updatedAt = new \DateTime('2023-02-01');

        $emission
            ->setTitre('Titre test')
            ->setKeyword('motclé')
            ->setDatepub($datepub)
            ->setRef('REF-123')
            ->setDuree(150)
            ->setUrl('https://example.com')
            ->setDescriptif('Une belle description')
            ->setThumbnail('image.jpg')
            ->setThumbnailMp3('audio.mp3')
            ->setUpdatedat($updatedAt);

        $this->assertEquals('Titre test', $emission->getTitre());
        $this->assertEquals('motclé', $emission->getKeyword());
        $this->assertSame($datepub, $emission->getDatepub());
        $this->assertEquals('REF-123', $emission->getRef());
        $this->assertEquals(150, $emission->getDuree());
        $this->assertEquals('https://example.com', $emission->getUrl());
        $this->assertEquals('Une belle description', $emission->getDescriptif());
        $this->assertEquals('image.jpg', $emission->getThumbnail());
        $this->assertEquals('audio.mp3', $emission->getThumbnailMp3());
        $this->assertSame($updatedAt, $emission->getUpdatedat());
    }

    public function testSetThumbnailFile(): void
    {
        $fileMock = $this->createMock(File::class);

        $emission = new Emission();
        /** @var File&\PHPUnit\Framework\MockObject\MockObject $fileMock */
        $emission->setThumbnailFile($fileMock);

        $this->assertSame($fileMock, $emission->getThumbnailFile());
        $this->assertInstanceOf(\DateTimeInterface::class, $emission->getUpdatedat());
    }

    public function testSetThumbnailFileMp3(): void
    {
        $fileMock = $this->createMock(File::class);

        $emission = new Emission();
        $emission->setThumbnailFileMp3($fileMock);

        $this->assertSame($fileMock, $emission->getThumbnailFileMp3());
    }

    public function testRelations(): void
    {
      

        $emission = new Emission();

        /** @var User&\PHPUnit\Framework\MockObject\MockObject $userMock */
        $userMock = $this->createMock(User::class);

        /** @var Theme&\PHPUnit\Framework\MockObject\MockObject $themeMock */
        $themeMock = $this->createMock(Theme::class);

        /** @var Categories&\PHPUnit\Framework\MockObject\MockObject $categorieMock */
        $categorieMock = $this->createMock(Categories::class);

        /** @var Editeur&\PHPUnit\Framework\MockObject\MockObject $editeurMock */
        $editeurMock = $this->createMock(Editeur::class);

        $emission
        
            ->setUser($userMock)
            ->setTheme($themeMock)
            ->setCategorie($categorieMock)
            ->setEditeur($editeurMock);

        $this->assertSame($userMock, $emission->getUser());
        $this->assertSame($themeMock, $emission->getTheme());
        $this->assertSame($categorieMock, $emission->getCategorie());
        $this->assertSame($editeurMock, $emission->getEditeur());
    }

    public function testAddAndRemoveInviteOldAnimateur(): void
    {
        /** @var InviteOldAnimateur&\PHPUnit\Framework\MockObject\MockObject $inviteMock */
        $inviteMock = $this->createMock(InviteOldAnimateur::class);

      

        $emission = new Emission();
        $this->assertCount(0, $emission->getInviteOldAnimateurs());

        $emission->addInviteOldAnimateur($inviteMock);
        $this->assertCount(1, $emission->getInviteOldAnimateurs());
        $this->assertTrue($emission->getInviteOldAnimateurs()->contains($inviteMock));

        $emission->removeInviteOldAnimateur($inviteMock);
        $this->assertCount(0, $emission->getInviteOldAnimateurs());
    }
}
