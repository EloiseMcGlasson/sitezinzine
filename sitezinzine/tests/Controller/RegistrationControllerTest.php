<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegisterPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="registration_form"]');
    }

    public function testSuccessfulRegistrationFlow(): void
{
    $client = static::createClient();
    $crawler = $client->request('GET', '/register');

    $form = $crawler->filter('form[name="registration_form"]')->form([
        'registration_form[username]' => 'testuser_' . uniqid(),
        'registration_form[email]' => 'test_' . uniqid() . '@example.com',
        'registration_form[plainPassword]' => 'TestPassword123!',
        'registration_form[agreeTerms]' => true, // Important : le champ est requis
    ]);

    $client->submit($form);
    $this->assertResponseRedirects();
}


public function testEmailVerificationFailsGracefully(): void
{
    $client = static::createClient();

    // ⚠️ On fait une requête pour initialiser la session
    $client->request('GET', '/register');

    $container = $client->getContainer();
    $entityManager = $container->get('doctrine')->getManager();

    $user = new User();
    $user->setUsername('verifyuser_' . uniqid());
    $user->setEmail('verify_' . uniqid() . '@example.com');
    $user->setPassword('fakehashedpassword');

    $entityManager->persist($user);
    $entityManager->flush();

    $firewallName = 'main';

    $token = new UsernamePasswordToken(
        $user,
        'dummy_password',
        $user->getRoles(),   // 3e argument : les rôles !
        $firewallName        // 4e argument : nom du firewall
    );
    

    // ✅ Maintenant qu’une requête a été faite, la session est disponible
    $session = $client->getRequest()->getSession();
    $session->set('_security_' . $firewallName, serialize($token));
    $session->save();

    $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));

    $client->request('GET', '/verify/email?badToken=true');
    $this->assertResponseRedirects('/login');
    $client->followRedirect();
    $this->assertSelectorExists('.alert');
}


}
