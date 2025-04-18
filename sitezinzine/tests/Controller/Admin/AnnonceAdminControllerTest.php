<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Annonce;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class AnnonceAdminControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
{
    parent::setUp();

    $this->client = static::createClient(); // Une seule fois ici
    $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
}

public function testIndexPageIsAccessible(): void
{
    // Créer un utilisateur avec un rôle suffisant
    $user = new User();
    $user->setEmail('admin' . uniqid() . '@test.com');
    $user->setUsername('username'. uniqid());
    $user->setRoles(['ROLE_ADMIN']);
    $user->setPassword(password_hash('password', PASSWORD_BCRYPT));

    $this->entityManager->persist($user);
    $this->entityManager->flush();

    // Connexion de l'utilisateur
    $this->client->loginUser($user);

    // Accès à la page d’index
    $this->client->request('GET', '/admin/annonce/');
    
    // Vérifie que la réponse est bien 200
    $this->assertResponseIsSuccessful();
}


public function testCanEditAnnonce(): void
{
    // Création d’un utilisateur avec un rôle suffisant
    $user = new User();
    $user->setEmail('admin' . uniqid() . '@test.com');
    $user->setUsername('username'. uniqid());
    $user->setRoles(['ROLE_EDITOR']);
    $user->setPassword(password_hash('password', PASSWORD_BCRYPT));
    $this->entityManager->persist($user);

    // Création de l’annonce
    $annonce = new Annonce();
    $annonce->setTitre('Annonce à modifier');
    $annonce->setPresentation('Description de l\'annonce');
    $annonce->setType('Concert');
    $annonce->setVille('Paris');
    $annonce->setDateDebut(new \DateTime());
    $annonce->setDateFin(new \DateTime('+7 days'));
    $annonce->setDepartement('04');
    $annonce->setAdresse('123 rue de Paris');
    $annonce->setHoraire('9h-18h');
    $annonce->setPrix('Gratuit');
    $annonce->setContact('exemple@exemple.fr');
    $annonce->setValid(false);
    $annonce->setUpdateAt(new \DateTime());
    $annonce->setOrganisateur('Organisateur de l\'annonce');
    $annonce->setSoftDelete(false);

    $this->entityManager->persist($annonce);
    $this->entityManager->flush();

    // Connexion
    $this->client->loginUser($user);

    // Accès au formulaire d’édition
    $crawler = $this->client->request('GET', '/admin/annonce/' . $annonce->getId() . '/edit');

    // Remplir et soumettre le formulaire
    $form = $crawler->selectButton('Sauvegarder')->form([
        'annonce[titre]' => 'Annonce modifiée',
        'annonce[presentation]' => 'Nouvelle description',
        'annonce[valid]' => true,
        'annonce[dateDebut]' => (new \DateTime())->format('Y-m-d H:i'),
        'annonce[dateFin]' => (new \DateTime('+7 days'))->format('Y-m-d H:i'),
        'annonce[horaire]' => '10h-20h',
        'annonce[prix]' => '10€',
        'annonce[contact]' => 'nouvellemail@example.com',
        'annonce[type]' => 'concert',
        'annonce[departement]' => '04',
        'annonce[ville]' => 'New York',
        'annonce[adresse]' => '123 Main St',
    ]);

    $this->client->submit($form);

    // Vérifications
    $this->assertResponseRedirects('/admin/annonce/');
    $this->client->followRedirect();
    $this->assertSelectorExists('success');
}


public function testCanValidateAnnonce(): void
{
    $annonce = $this->createTestAnnonce(false);

    // Correction ici : s'assurer que le champ obligatoire est bien défini
    $annonce->setOrganisateur('Organisateur test');

    $this->entityManager->flush();

    $this->client->request('POST', '/admin/annonce/' . $annonce->getId() . '/valid');
    $this->assertResponseRedirects('/admin/annonce/');

    $annonceValid = $this->entityManager->getRepository(Annonce::class)->find($annonce->getId());
    $this->assertTrue($annonceValid->isValid());
}


    public function testCanUnvalidateAnnonce(): void
    {
        $annonce = $this->createTestAnnonce(true);

        $this->client->request('POST', '/admin/annonce/' . $annonce->getId() . '/unvalid');
        $this->assertResponseRedirects('/admin/annonce/');

        $annonceValid = $this->entityManager->getRepository(Annonce::class)->find($annonce->getId());
        $this->assertFalse($annonceValid->isValid());
    }

    public function testCanSoftDeleteAnnonce(): void
    {
        $annonce = $this->createTestAnnonce();

        $this->client->request('DELETE', '/admin/annonce/' . $annonce->getId());
        $this->assertResponseRedirects('/admin/annonce/');

        $deletedAnnonce = $this->entityManager->getRepository(Annonce::class)->find($annonce->getId());
        $this->assertTrue($deletedAnnonce->isSoftDelete());
    }

    private function createTestAnnonce(bool $valid = true): Annonce
    {
        $annonce = new Annonce();
        $annonce->setTitre('Test annonce');
        $annonce->setPresentation('Description test');
        $annonce->setType('offre');
        $annonce->setValid($valid);
        $annonce->setSoftDelete(false);
        $annonce->setUpdateAt(new \DateTime());
        $annonce->setDateDebut(new \DateTime('+1 day'));
        $annonce->setDateFin(new \DateTime('+7 days'));
        $annonce->setHoraire('9h-18h');
        $annonce->setPrix('Gratuit');
        $annonce->setContact('test@example.com');
        $annonce->setOrganisateur('Organisateur test');
        $annonce->setDepartement('75');
        $annonce->setVille('Paris');
        $annonce->setAdresse('1 rue du Test');

        $this->entityManager->persist($annonce);
        $this->entityManager->flush();

        return $annonce;
    }

    protected function tearDown(): void
{
    parent::tearDown();
    $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
    $this->entityManager->createQuery('DELETE FROM App\Entity\Annonce')->execute();
}

}
