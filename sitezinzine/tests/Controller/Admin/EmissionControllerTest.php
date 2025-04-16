<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Emission;
use App\Entity\Categories;
use App\Entity\Theme;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class EmissionControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testIndexPageIsSecured(): void
    {
        $this->client->request('GET', '/admin/emission/');
        $this->assertResponseRedirects('/login');
    }

    public function testIndexWithAdminUser(): void
    {
        $this->loginAsAdmin();
        $crawler = $this->client->request('GET', '/admin/emission/');
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('a.btn-create', 'Créer une nouvelle émission');
    }

    public function testCreateEmission(): void
    {
        $this->loginAsAdmin();

        // Créer un thème et une catégorie minimaux
    $theme = new \App\Entity\Theme();
    $theme->setName('Thème test');
    $theme->setUpdatedAt(new \DateTime());
    $this->entityManager->persist($theme);

    $categorie = new \App\Entity\Categories();
    $categorie->setTitre('Catégorie test');
    $categorie->setEditeur(1);
    $categorie->setDuree(60);
    $categorie->setActive(true);
    $categorie->setDescriptif('Description test');
    $categorie->setUpdatedAt(new \DateTime());
    $this->entityManager->persist($categorie);

    $this->entityManager->flush();
        
        $crawler = $this->client->request('GET', '/admin/emission/create');
        $this->assertResponseIsSuccessful();

        dump($crawler->filter('form')->html()); die;

        $form = $crawler->selectButton('Créer une nouvelle émission')->form([
            'emission[titre]' => 'Test Émission',
            'emission[descriptif]' => 'Description test',
            'emission[duree]' => 60,
            'emission[url]' => 'https://test.com/emission',
            'emission[keyword]' => 'test-keyword',
            
            'emission[ref]' => 'REF-' . uniqid(),  // Ajout de la référence unique
            'emission[datepub]' => (new \DateTime())->format('Y-m-d')  // Ajout de la date
            
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/admin/emission/');
        
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditEmission(): void
    {
        $this->loginAsAdmin();

        $theme = new Theme();
        $theme->setName('Thème test');
        $theme->setUpdatedAt(new \DateTime());

        $categorie = new Categories();
        $categorie->setTitre('Catégorie test');
        $categorie->setEditeur(1);
        $categorie->setDuree(60);
        $categorie->setActive(true);
        $categorie->setDescriptif('Description test');

        $categorie->setUpdatedAt(new \DateTime());

        $this->entityManager->persist($theme);
        $this->entityManager->persist($categorie);
        
        // Créer une émission de test
        $emission = new Emission();
        $emission->setTitre('À modifier')
                ->setDescriptif('Description initiale')
                ->setDuree(60)
                ->setUrl('https://test.com/old')
                ->setDatepub(new \DateTime())
                ->setUpdatedat(new \DateTime())
                ->setKeyword('test-keyword')
                ->setRef('REF-' . uniqid())
                ->setTheme($theme)
                ->setCategorie($categorie);

        $this->entityManager->persist($emission);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', "/admin/emission/{$emission->getId()}/edit");
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Sauvegarder')->form([
            'emission[titre]' => 'Titre modifié',
            'emission[keyword]' => 'test-keyword-modified'
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/admin/emission/');
    }

    public function testDeleteEmission(): void
    {
        $this->loginAsAdmin();
    
        $emission = new Emission();
        $emission->setTitre('À supprimer')
                 ->setDescriptif('Description')
                 ->setDuree(60)
                 ->setUrl('https://test.com/delete')
                 ->setDatepub(new \DateTime())
                 ->setUpdatedat(new \DateTime())
                 ->setKeyword('test-keyword')
                 ->setRef('REF-' . uniqid());
    
        $this->entityManager->persist($emission);
        $this->entityManager->flush();
    
        // 🔐 On garde l'ID de côté avant suppression
        $id = $emission->getId();
    
        $this->client->request('DELETE', "/admin/emission/$id");
    
        $this->assertResponseRedirects('/admin/emission/');
    
        // 🔍 On vérifie la suppression en utilisant l'ID sauvé
        $this->assertNull(
            $this->entityManager->getRepository(Emission::class)->find($id)
        );
    }
    

    private function loginAsAdmin(): void
    {
        // Générer un nom d'utilisateur unique avec un timestamp
        $uniqueUsername = 'admin_test_' . uniqid();
        $user = new User();
        $user->setUsername($uniqueUsername)
             ->setPassword('$2y$13$HOkKpaK.puMSLMKy1Kujqe1PzxE6fqjYPHXAyEtGvnvytEIIINBNi') // 'password'
             ->setRoles(['ROLE_ADMIN'])
             ->setEmail($uniqueUsername . '@test.com');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->loginUser($user);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}