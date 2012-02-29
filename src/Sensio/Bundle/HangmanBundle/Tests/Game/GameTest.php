<?php

namespace Sensio\Bundle\HangmanBundle\Tests;

use Sensio\Bundle\HangmanBundle\Game\Game;

class GameTest extends \PHPUnit_Framework_TestCase
{
    public function testTryWordWithExpectedWord()
    {
        $game = new Game('php');
        $this->assertTrue($game->tryWord('php'));
        $this->assertTrue($game->isWon());
        $this->assertFalse($game->isHanged());
    }

    public function testTryWordWithInvalidWord()
    {
        $game = new Game('php');
        $this->assertFalse($game->tryWord('foo'));
        $this->assertFalse($game->isWon());
        $this->assertTrue($game->isHanged());
        $this->assertEquals(0, $game->getRemainingAttempts());
    }

    public function testTryLetter()
    {
        $game = new Game('php');
        $this->assertFalse($game->tryLetter('X'));
        $this->assertFalse($game->isLetterFound('X'));
        $this->assertFalse($game->isWon());

        $this->assertTrue($game->tryLetter('P'));
        $this->assertTrue($game->isLetterFound('P'));
        $this->assertFalse($game->isWon());

        $this->assertTrue($game->tryLetter('H'));
        $this->assertTrue($game->isLetterFound('H'));
        $this->assertTrue($game->isWon());
    }

    public function testTryLetterTwiceInARow()
    {
        $game = new Game('php');
        $this->assertTrue($game->tryLetter('P'));
        $this->assertTrue($game->isLetterFound('P'));

        $this->assertFalse($game->tryLetter('P'));
        $this->assertTrue($game->isLetterFound('P'));
        
    }

    public function testResetGame()
    {
        $game = new Game('php', 3, array('x', 'h'), array('h'));
        $this->assertEquals(3, $game->getAttempts());
        $this->assertTrue($game->isLetterFound('H'));
        $this->assertFalse($game->isLetterFound('X'));

        $game->reset();
        $this->assertEquals(0, $game->getAttempts());
        $this->assertFalse($game->isLetterFound('H'));
        $this->assertFalse($game->isLetterFound('X'));
    }

    public function testTryInvalidLetter()
    {
        $this->setExpectedException('InvalidArgumentException');
        $game = new Game('php');
        $this->assertFalse($game->tryLetter('0'));
    }
}