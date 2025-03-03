<?php

namespace App\Tests\Entity;

use App\Entity\Emission;
use PHPUnit\Framework\TestCase;

class EmissionTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $emission = new Emission();

        $emission->setTitre('Test Title');
        $this->assertEquals('Test Title', $emission->getTitre());

        $emission->setKeyword('Test Keyword');
        $this->assertEquals('Test Keyword', $emission->getKeyword());

        $datepub = new \DateTime('2023-01-01');
        $emission->setDatepub($datepub);
        $this->assertEquals($datepub, $emission->getDatepub());

        $emission->setRef('Test Ref');
        $this->assertEquals('Test Ref', $emission->getRef());

        $emission->setDuree(120);
        $this->assertEquals(120, $emission->getDuree());

        $emission->setUrl('https://example.com');
        $this->assertEquals('https://example.com', $emission->getUrl());

        $emission->setDescriptif('Test Description');
        $this->assertEquals('Test Description', $emission->getDescriptif());

        $emission->setThumbnail('test-thumbnail.jpg');
        $this->assertEquals('test-thumbnail.jpg', $emission->getThumbnail());

        $emission->setThumbnailMp3('test-thumbnail.mp3');
        $this->assertEquals('test-thumbnail.mp3', $emission->getThumbnailMp3());

        $updatedAt = new \DateTime('2023-01-02');
        $emission->setUpdatedat($updatedAt);
        $this->assertEquals($updatedAt, $emission->getUpdatedat());
    }
}