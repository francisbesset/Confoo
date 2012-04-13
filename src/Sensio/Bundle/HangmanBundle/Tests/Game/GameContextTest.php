<?php

namespace Sensio\Bundle\HangmanBundle\Tests;

use Sensio\Bundle\HangmanBundle\Game\GameContext;

class GameContextTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadExistingGame()
    {
        $data = array(
            'word' => 'java',
            'attempts' => 2,
            'found_letters' => array('j'),
            'tried_letters' => array('j', 'x', 'h')
        );

        $game = $this->getMock(
            'Sensio\Bundle\HangmanBundle\Game\Game',
            array(),
            array(),
            '',
            false
        );

        $data = $this->getMock(
            'Sensio\Bundle\HangmanBundle\Entity\GameData',
            array(),
            array(),
            '',
            false
        );
        $data
            ->expects($this->once())
            ->method('toGame')
            ->will($this->returnValue($game))
        ;

        $repository = $this->getGameRepositoryMock();
        $repository
            ->expects($this->once())
            ->method('findGame')
            ->with($this->equalTo('abcdefghij'))
            ->will($this->returnValue($data))
        ;

        $context = new GameContext();
        $context->setGameRepository($repository);

        $this->assertInstanceOf(
            'Sensio\Bundle\HangmanBundle\Game\Game',
            $context->loadGame('abcdefghij')
        );
    }

    public function testGetRandomWord()
    {
        // Get a new WordList mock
        $wordList = $this->getWordListMock();
        $wordList
            ->expects($this->once())
            ->method('getRandomWord')
            ->will($this->returnValue('java'))
        ;

        $context = new GameContext();
        $context->setWordList($wordList);

        $this->assertEquals('java', $context->getRandomWord(4));
    }

    private function getWordListMock()
    {
        $wordList = $this->getMock(
            'Sensio\Bundle\HangmanBundle\Game\WordList',
            array(),
            array(),
            '',
            false
        );

        return $wordList;
    }

    private function getGameRepositoryMock()
    {
        $repository = $this
            ->getMock('Sensio\Bundle\HangmanBundle\Entity\GameRepositoryInterface', array(), array(), '', false)
        ;

        return $repository;
    }
}