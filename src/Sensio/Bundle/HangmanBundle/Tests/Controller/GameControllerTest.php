<?php

namespace Sensio\Bundle\HangmanBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Sensio\Bundle\HangmanBundle\Game\Game;
use Sensio\Bundle\HangmanBundle\Entity\User;

class GameControllerTest extends WebTestCase
{
    private $client;

    private $em;

    private $user;

    private function authenticate()
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Log-in')->form();

        $this->client->submit($form, array(
            '_username' => 'hhamon',
            '_password' => 'secret'
        ));

        $this->client->followRedirect();
    }

    public function testTryInvalidLetterAction()
    {
        $this->client->request('GET', '/game/hangman/');

        for ($i = 1; $i <= Game::MAX_ATTEMPTS; $i++) {
            $this->playLetter('X');
        }

        $response = $this->client->getResponse();
        $this->assertRegexp("#Oops, you're hanged#", $response->getContent());
    }

    public function testTryLetterAction()
    {
        $crawler = $this->client->request('GET', '/game/hangman/');
        $crawler = $this->playLetter('P');

        $this->assertEquals(2, $crawler->filter('#content .word_letters .guessed')->count());
        $this->assertEquals(1, $crawler->filter('#content .word_letters .hidden')->count());

        $crawler = $this->playLetter('H');
        $response = $this->client->getResponse();

        $this->assertTrue($response->isSuccessful());
        $this->assertRegexp(
            '#You found the word <strong>php<\/strong>#',
            $response->getContent()
        );
    }

    private function playLetter($letter)
    {
        $crawler = $this->client->getCrawler();

        $link = $crawler->selectLink($letter)->link();

        $this->client->click($link);

        return $this->client->followRedirect();
    }

    public function testTryWrongWordAction()
    {
        $this->playWord('foo');

        $response = $this->client->getResponse();

        $this->assertTrue($response->isSuccessful());
        $this->assertRegexp("#Oops, you're hanged#", $response->getContent());
    }

    public function testTryWordAction()
    {
        $this->playWord('php');

        $response = $this->client->getResponse();

        $this->assertTrue($response->isSuccessful());
        $this->assertRegexp(
            '#You found the word <strong>php<\/strong>#',
            $response->getContent()
        );
    }

    public function testResetGameAction()
    {
        $crawler = $this->client->request('GET', '/game/hangman/');
        $crawler = $this->playLetter('P');

        $link = $crawler->selectLink('Reset the game')->link();
        $this->client->click($link);
        $crawler = $this->client->followRedirect();

        $this->assertEquals(0, $crawler->filter('#content .word_letters .guessed')->count());
        $this->assertEquals(3, $crawler->filter('#content .word_letters .hidden')->count());
    }

    private function playWord($word)
    {
        $crawler = $this->client->request('GET', '/game/hangman/');

        $form = $crawler->selectButton('Let me guess...')->form();
        $this->client->submit($form, array('word' => $word));

        $this->client->followRedirect();
    }

    public function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        
        $this->em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $user = new User();
        $user->setUsername('hhamon');
        $user->setSalt('47e92fd76bc1bf364382fc483a7bbdaf080aab51');
        $user->setPassword('XshcSZd2zFJ8632vMPpu+XLecz6GiTXuqxvIs54j0qm5V2+NKs1zEyybV1009nEdJleV/u75KP6qKLaUQ3PmeQ==');
        $user->setEmail('hugo.hamon@sensio.com');

        $this->em->persist($user);
        $this->em->flush();

        $this->user   = $user;
        $this->client = static::createClient();

        $this->authenticate();
    }

    public function tearDown()
    {
        $this->em->remove($this->user);
        $this->em->flush();

        $this->em     = null;
        $this->user   = null;
        $this->client = null;
    }
}
