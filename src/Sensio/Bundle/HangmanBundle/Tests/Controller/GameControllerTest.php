<?php

namespace Sensio\Bundle\HangmanBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Sensio\Bundle\HangmanBundle\Game\Game;
use Sensio\Bundle\HangmanBundle\Entity\Player;

class GameControllerTest extends WebTestCase
{
    private $client;

    private $em;

    private $user;

    public function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $container = $kernel->getContainer();
        $factory = $container->get('security.encoder_factory');

        $player = new Player();
        $player->setUsername('hhamon');
        $player->setRawPassword('secret');
        $player->setEmail('hugo@example.com');
        $player->encodePassword($factory->getEncoder($player));

        $this->em = $container->get('doctrine.orm.default_entity_manager');
        $this->em->persist($player);
        $this->em->flush();
        $this->user = $player;

        $this->client = static::createClient();
        $this->client->followRedirects(true);
    }

    public function tearDown()
    {
        $this->em->remove($this->user);
        $this->em->flush();

        // Tip to avoid "Too Many Connections"
        $doctrine = $this->client->getContainer()->get('doctrine');
        $doctrine->getConnection()->close();

        $this->em     = null;
        $this->user   = null;
        $this->client = null;
    }

    public function testLooseGame()
    {
        $crawler = $this->authenticate('hhamon', 'secret');

        for ($i = 1; $i <= Game::MAX_ATTEMPTS; $i++) {
            $crawler = $this->playLetter('X');
        }

        $this->assertEquals(
            'Game Over!',
            $crawler->filter('#content > h2:first-child')->text()
        );
    }

    public function testGuessWord()
    {
        $crawler = $this->authenticate('hhamon', 'secret');

        foreach (array('H', 'X', 'P') as $letter) {
            $crawler = $this->playLetter($letter);
        }

        $this->assertEquals(
            'Congratulations!',
            $crawler->filter('#content > h2:first-child')->text()
        );
    }

    /**
     * @dataProvider provideUris
     *
     */
    public function testForbidAction($uri)
    {
        $this->client->followRedirects(false);

        $this->authenticate('hhamon', 'secret');
        $this->client->request('POST', $uri);
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }

    public function provideUris()
    {
        return array(
            array('/game/1234567890/word'),
            array('/game/1234567890/letter/X'),
            array('/game/1234567890/won'),
            array('/game/1234567890/hanged'),
        );
    }

    public function testInvalidWord()
    {
        $this->authenticate('hhamon', 'secret');

        $crawler = $this->playWord('foo');

        $this->assertEquals(
            'Game Over!',
            $crawler->filter('#content > h2:first-child')->text()
        );
    }

    public function testWordAction()
    {
        $this->authenticate('hhamon', 'secret');

        $crawler = $this->playWord('php');

        $this->assertEquals(
            'Congratulations!',
            $crawler->filter('#content > h2:first-child')->text()
        );
    }

    private function authenticate($username, $password)
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('login')->form();

        return $this->client->submit($form, array(
            '_username' => $username,
            '_password' => $password,
        ));
    }

    private function playLetter($letter)
    {
        $crawler = $this->client->getCrawler();
        $link = $crawler->selectLink($letter)->link();

        return $this->client->click($link);
    }

    private function playWord($word)
    {
        $crawler = $this->client->getCrawler();
        $form = $crawler->selectButton('Let me guess...')->form();

        return $this->client->submit($form, array('word' => $word));
    }
}
