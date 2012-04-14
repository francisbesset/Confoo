<?php

namespace Sensio\Bundle\HangmanBundle\Tests;

use Sensio\Bundle\HangmanBundle\Game\Game;

class GameTest extends \PHPUnit_Framework_TestCase
{
    public function testReset()
    {
        $game = new Game('php', 3, array('h', 'x', 'c', 'v'), array('h'));
        $game->reset();

        $this->assertEquals('php', $game->getWord());
        $this->assertEquals(0, $game->getAttempts());
        $this->assertEmpty($game->getFoundLetters());
        $this->assertEmpty($game->getTriedLetters());
    }

    public function testGetContext()
    {
        $game = new Game('php', 3, array('h', 'x', 'c', 'v'), array('h'));
        $context = $game->getContext();

        $this->assertArrayHasKey('word', $context);
        $this->assertArrayHasKey('attempts', $context);
        $this->assertArrayHasKey('tried_letters', $context);
        $this->assertArrayHasKey('found_letters', $context);
    }

    public function testGameIsHanged()
    {
        $game = new Game('php');

        for ($i = 1; $i <= Game::MAX_ATTEMPTS; $i++) {
            $this->assertFalse($game->isHanged());
            $game->tryLetter('X');
        }

        $this->assertTrue($game->isHanged());
    }

    public function testGuessWord()
    {
        $game = new Game('php');

        foreach (array('H', 'X', 'P') as $letter) {
            $this->assertFalse($game->isWon());
            $game->tryLetter($letter);
        }

        $this->assertTrue($game->isWon());
    }

    public function testTrySameLetterTwice()
    {
        $game = new Game('php');
        $game->tryLetter('H');

        $this->assertFalse($game->tryLetter('H'));
        $this->assertEquals(1, $game->getAttempts());
    }

    public function testTryInvalidLetter()
    {
        $this->setExpectedException('InvalidArgumentException');

        $game = new Game('php');
        $game->tryLetter('é');
    }

    public function testTryWrongLetter()
    {
        $game = new Game('php');

        $this->assertFalse($game->tryLetter('X'));
        $this->assertEquals(1, $game->getAttempts());
        $this->assertFalse($game->isLetterFound('X'));
        $this->assertContains('x', $game->getTriedLetters());
        $this->assertNotContains('x', $game->getFoundLetters());
    }

    public function testTryCorrectLetter()
    {
        $game = new Game('php');

        $this->assertTrue($game->tryLetter('H'));
        $this->assertEquals(0, $game->getAttempts());
        $this->assertTrue($game->isLetterFound('H'));
        $this->assertContains('h', $game->getTriedLetters());
        $this->assertContains('h', $game->getFoundLetters());
    }

    public function testTryWrongWord()
    {
        $game = new Game('php');

        $this->assertFalse($game->tryWord('foo'));
        $this->assertFalse($game->isWon());
        $this->assertTrue($game->isHanged());
        $this->assertTrue($game->isOver());
        $this->assertEquals(0, $game->getRemainingAttempts());
    }

    public function testTryCorrectWord()
    {
        $game = new Game('php');

        $this->assertTrue($game->tryWord('php'));
        $this->assertTrue($game->isWon());
        $this->assertFalse($game->isHanged());
        $this->assertTrue($game->isOver());
        $this->assertEquals(0, $game->getAttempts());
        $this->assertEquals(array('p', 'h'), $game->getFoundLetters());
    }
}