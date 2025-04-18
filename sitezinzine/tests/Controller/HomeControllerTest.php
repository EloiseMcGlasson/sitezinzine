<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Evenement;

class HomeControllerTest extends WebTestCase
{

    private EntityManagerInterface $entityManager;
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
   
    }

    public function testIndex(): void
    {
        
        $crawler = $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains('Radio Zinzine, radio libre !');

        // Check if the partials are included
        $this->assertSelectorExists('div.titrelast'); // Assuming there's a div with this class in lastEmissions.html.twig
        $this->assertSelectorExists('div.bodyondes'); // Assuming there's a div with this class in ondes.html.twig
        $this->assertSelectorExists('div.vagues'); // Assuming there's a div with this class in vagues.html.twig
        $this->assertSelectorExists('article.evenements'); // Assuming there's a div with this class in evenement.html.twig
    }


    public function testShowEvenement(): void
    {
        // Create a test event with all required fields
        $evenement = new Evenement();
        $evenement->setTitre('Test Event')
            ->setOrganisateur('Test Organisateur')
            ->setVille('Test Ville')
            ->setDepartement('01')
            ->setAdresse('123 Test Street')
            ->setDateDebut(new \DateTime('now'))
            ->setDateFin(new \DateTime('tomorrow'))
            ->setHoraire('10:00 AM')
            ->setPrix('Free')
            ->setPresentation('This is a test event.')
            ->setContact('contact@test.com')
            ->setType('Public')
            ->setValid(true)
            ->setUpdateAt(new \DateTime('now'))
            ->setSoftDelete(false);

        $this->entityManager->persist($evenement);
        $this->entityManager->flush();

        
        $this->client->request('GET', '/' . $evenement->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('div.bodyevenement'); // Adjust the selector to match your template
    }



    public function testRadio(): void
    {
        $this->client->request('GET', '/radio');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('div.bodyradio'); // Adjust the text to match your radio page
    }

    public function testProgramme(): void
    {
        $this->client->request('GET', '/programme');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('div.bodyprogramme'); // Adjust the text to match your programme page
    }

    public function testInfos(): void
    {
        $this->client->request('GET', '/infos');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('div.bodyinfos');
    }

    public function testZone(): void
    {
        $this->client->request('GET', '/zone');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('div.bodyzoneecoute');
        
    }

    public function testAide(): void
    {
        $this->client->request('GET', '/aide');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('div.bodyaide');

    }

    public function testAmis(): void
    {
        $this->client->request('GET', '/amis');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('div.bodyamis');

    }

    public function testMentions(): void
    {
        $this->client->request('GET', '/mentions');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('div.bodymentions');

    }

    public function testContacts(): void
    {
        $this->client->request('GET', '/contacts');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('div.bodycontacts');    }

    public function testDon(): void
    {
        $this->client->request('GET', '/don');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('div.bodydon');
    }

   
}
