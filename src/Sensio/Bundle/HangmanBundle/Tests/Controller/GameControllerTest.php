<?php

namespace Sensio\Bundle\HangmanBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Sensio\Bundle\HangmanBundle\Game\Game;
use Sensio\Bundle\HangmanBundle\Entity\GameData;
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

        /*
        $game = new GameData();
        $game->setPlayer($player);
        $game->setWord('azerty');
        $game->setToken('1234567890');
        */

        $this->em = $container->get('doctrine.orm.default_entity_manager');
        $this->em->persist($player);
//        $this->em->persist($game);
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

    private function authenticate($username, $password)
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('login')->form();

        return $this->client->submit($form, array(
            '_username' => $username,
            '_password' => $password,
        ));
    }

    private function playWord($word)
    {
        $crawler = $this->client->getCrawler();
        $form = $crawler->selectButton('Let me guess...')->form();

        return $this->client->submit($form, array('word' => $word));
    }

    public function testGuessWord()
    {
        $crawler = $this->authenticate('hhamon', 'secret');

        foreach (array('H', 'X', 'P') as $letter) {
            $link = $crawler->selectLink($letter)->link();
            $crawler = $this->client->click($link);
        }

        $this->assertEquals(
            'Congratulations!',
            $crawler->filter('#content > h2:first-child')->text()
        );
    }

    public function testForbidWordAction()
    {
        $this->client->followRedirects(false);

        $this->authenticate('hhamon', 'secret');
        $this->client->request('POST', '/game/1234567890/word');
        $this->assertTrue($this->client->getResponse()->isNotFound());
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
}
