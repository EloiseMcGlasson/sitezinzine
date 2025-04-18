<?php

namespace App\Tests\Controller\Admin;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;

class AdminControllerTest extends WebTestCase
{
    public function testAdminPageAccessibleToLoggedInUser(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $user = new User();
        $user->setUsername('admin_' . uniqid());
        $user->setEmail('admin_' . uniqid() . '@example.com');
        $user->setPassword('fakehashedpassword');
        $user->setRoles(['ROLE_USER']);

        $entityManager = $container->get('doctrine')->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        // Simuler une connexion
        $session = $container->get('session');
        $firewallName = 'main';
        $token = new UsernamePasswordToken(
            $user,
            'dummy_password',
            $user->getRoles(),   // 3e argument : les rôles !
            $firewallName        // 4e argument : nom du firewall
        );

        $session->set('_security_' . $firewallName, serialize($token));
        $session->save();

        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));

        // Accès à la page admin
        $client->request('GET', '/admin/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body'); // Adapte ce sélecteur selon ton HTML

        // Tu peux aussi tester des éléments spécifiques du template ici, comme un tableau avec les utilisateurs
    }
}
