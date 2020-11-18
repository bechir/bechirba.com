<?php

declare(strict_types=1);

/*
 * This file is part of the Bechir's Blog application.
 *
 * (c) Bechir Ba
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class LoginTest extends WebTestCase
{
    /**
     * @testdox User is successfuly logged in
     */
    public function testLoginIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var RouterInterface $router */
        $router = $client->getContainer()->get('router');

        $crawler = $client->request(Request::METHOD_GET, $router->generate('app_login'));

        $form = $crawler->filter('form[name=login]')->form([
            'email' => 'test@email.com',
            'password' => 'password',
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();
        $this->assertRouteSame('index');
    }

    /**
     * @todo Implement this test
     * @testdox Ensure logged in user are redirected to refer
     */
    public function testUserAlreadySignedIn(): void
    {
    }

    public function testInvalidCsrfToken(): void
    {
        $client = static::createClient();

        /** @var RouterInterface */
        $router = $client->getContainer()->get('router');

        $crawler = $client->request(Request::METHOD_GET, $router->generate('app_login'));

        $form = $crawler->filter('form[name=login]')->form([
            '_csrf_token' => 'Invalid token',
            'email' => 'test@email.com',
            'password' => 'password',
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();
        $this->assertRouteSame('app_login');
        $this->assertSelectorTextContains('form[name=login] div.alert', 'Jeton CSRF invalide.');
    }

    /**
     * @dataProvider provideInvalidCredentials
     */
    public function testCredentialsAreInvalid(string $email, string $password): void
    {
        $client = static::createClient();

        /** @var RouterInterface */
        $router = $client->getContainer()->get('router');

        $crawler = $client->request(Request::METHOD_GET, $router->generate('app_login'));

        $form = $crawler->filter('form[name=login]')->form([
            'email' => $email,
            'password' => $password,
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();
        $this->assertRouteSame('app_login');
        $this->assertSelectorTextContains('form[name=login] div.alert', 'Identifiants invalides.');
    }

    public function provideInvalidCredentials(): iterable
    {
        yield ['bad@email.com', 'wrong'];
        yield ['fake@email.com', 'invalid'];
        yield ['wrong@email.com', 'fakepass'];
    }
}
