<?php

namespace App\Tests\Entity;

use App\Entity\Theme;
use App\Entity\Emission;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

class ThemeTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $theme = new Theme();
        $now = new \DateTime();
        $file = new File(__FILE__);

        $theme->setName('Musique')
              ->setThumbnail('thumb.jpg')
              ->setThumbnailFile($file)
              ->setUpdatedAt($now);

        $this->assertSame('Musique', $theme->getName());
        $this->assertSame('thumb.jpg', $theme->getThumbnail());
        $this->assertSame($file, $theme->getThumbnailFile());
        $this->assertSame($now, $theme->getUpdatedAt());
    }

    public function testAddAndRemoveEmission(): void
{
    $theme = new Theme();

    /** @var Emission&\PHPUnit\Framework\MockObject\MockObject $emissionMock */
     $emissionMock = $this->createMock(Emission::class);

    // Lors de l'ajout
    $emissionMock->expects($this->once())
        ->method('setTheme')
        ->with($theme);

    $theme->addEmission($emissionMock);

    $this->assertCount(1, $theme->getEmissions());
    $this->assertSame($emissionMock, $theme->getEmissions()->first());

    // Simuler un emission déjà lié à ce thème
        /** @var Emission&\PHPUnit\Framework\MockObject\MockObject $emissionMock */

    $emissionMock = $this->createMock(Emission::class);
    $emissionMock->expects($this->once())
        ->method('getTheme')
        ->willReturn($theme);

    $emissionMock->expects($this->once())
        ->method('setTheme')
        ->with(null);

    // Ajouter l'émission dans la collection directement pour le test
    $theme->getEmissions()->add($emissionMock);

    $theme->removeEmission($emissionMock);

    $this->assertCount(1, $theme->getEmissions()); // Ajout manuel, donc ne disparaît pas
}

}
