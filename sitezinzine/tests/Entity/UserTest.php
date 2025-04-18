<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Emission;
use App\Entity\Evenement;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetSetUsername(): void
    {
        $user = new User();
        $this->assertNull($user->getUsername());

        $user->setUsername('testuser');
        $this->assertEquals('testuser', $user->getUsername());
        $this->assertEquals('testuser', $user->getUserIdentifier());
    }

    public function testGetSetEmail(): void
    {
        $user = new User();
        $this->assertNull($user->getEmail());

        $user->setEmail('test@example.com');
        $this->assertEquals('test@example.com', $user->getEmail());
    }

    public function testGetSetPassword(): void
    {
        $user = new User();
        $user->setPassword('secure_password');
        $this->assertEquals('secure_password', $user->getPassword());
    }

    public function testRoles(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles());

        $user->setVerified(true);
        $this->assertContains('ROLE_VERIFIED', $user->getRoles());
    }

    public function testIsVerified(): void
    {
        $user = new User();
        $this->assertFalse($user->isVerified());

        $user->setVerified(true);
        $this->assertTrue($user->isVerified());
    }

    public function testAddRemoveEmission(): void
    {
        $user = new User();
        $emission = new Emission();

        $this->assertEmpty($user->getEmissions());

        $user->addEmission($emission);
        $this->assertCount(1, $user->getEmissions());
        $this->assertTrue($user->getEmissions()->contains($emission));

        $user->removeEmission($emission);
        $this->assertFalse($user->getEmissions()->contains($emission));
    }

    public function testAddRemoveEvenement(): void
    {
        $user = new User();
        $evenement = new Evenement();

        $this->assertEmpty($user->getEvenements());

        $user->addEvenement($evenement);
        $this->assertCount(1, $user->getEvenements());
        $this->assertTrue($user->getEvenements()->contains($evenement));

        $user->removeEvenement($evenement);
        $this->assertFalse($user->getEvenements()->contains($evenement));
    }
}
