<?php

namespace App\Tests\Entity;

use App\Entity\InviteOldAnimateur;
use App\Entity\Emission;
use PHPUnit\Framework\TestCase;

class InviteOldAnimateurTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $invite = new InviteOldAnimateur();

        $invite->setFirstName('Alice')
            ->setLastName('Dupont')
            ->setPhoneNumber('0612345678')
            ->setMail('alice@example.com')
            ->setAncienanimateur(true);

        $this->assertSame('Alice', $invite->getFirstName());
        $this->assertSame('Dupont', $invite->getLastName());
        $this->assertSame('0612345678', $invite->getPhoneNumber());
        $this->assertSame('alice@example.com', $invite->getMail());
        $this->assertTrue($invite->isAncienanimateur());
    }

    public function testAddAndRemoveEmission(): void
    {
        $invite = new InviteOldAnimateur();
        /** @var Emission&\PHPUnit\Framework\MockObject\MockObject $emissionMock */
        $emissionMock = $this->createMock(Emission::class);
        $emissionMock->expects($this->once())
            ->method('addInviteOldAnimateur')
            ->with($invite);

        $invite->addEmission($emissionMock);

        $this->assertCount(1, $invite->getEmissions());
        $this->assertSame($emissionMock, $invite->getEmissions()->first());

        $emissionMock->expects($this->once())
            ->method('removeInviteOldAnimateur')
            ->with($invite);

        $invite->removeEmission($emissionMock);

        $this->assertCount(0, $invite->getEmissions());
    }
}
