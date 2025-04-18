<?php

namespace App\Tests\Entity;

use App\Entity\Editeur;
use App\Entity\Emission;
use PHPUnit\Framework\TestCase;

class EditeurTest extends TestCase
{
    public function testName()
    {
        $editeur = new Editeur();
        $editeur->setName('Nom de test');
        $this->assertEquals('Nom de test', $editeur->getName());
    }

    public function testMail()
    {
        $editeur = new Editeur();
        $editeur->setMail('test@example.com');
        $this->assertEquals('test@example.com', $editeur->getMail());
    }

    public function testPhone()
    {
        $editeur = new Editeur();
        $editeur->setPhone('0601020304');
        $this->assertEquals('0601020304', $editeur->getPhone());
    }

    public function testUpdateAt()
    {
        $editeur = new Editeur();
        $date = new \DateTime();
        $editeur->setUpdateAt($date);
        $this->assertSame($date, $editeur->getUpdateAt());
    }

    public function testAddEmission()
    {
        $editeur = new Editeur();
        $emission = new Emission();

        $this->assertCount(0, $editeur->getEmissions());
        $editeur->addEmission($emission);
        $this->assertCount(1, $editeur->getEmissions());
        $this->assertSame($editeur, $emission->getEditeur());
    }

    public function testRemoveEmission()
    {
        $editeur = new Editeur();
        $emission = new Emission();

        $editeur->addEmission($emission);
        $this->assertCount(1, $editeur->getEmissions());

        $editeur->removeEmission($emission);
        $this->assertCount(0, $editeur->getEmissions());
        $this->assertNull($emission->getEditeur());
    }
}
