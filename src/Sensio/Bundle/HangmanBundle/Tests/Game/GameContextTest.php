<?php

namespace Sensio\Bundle\HangmanBundle\Tests;

use Sensio\Bundle\HangmanBundle\Game\GameContext;

class GameContextTest extends \PHPUnit_Framework_TestCase
{
    public function testUnableToLoadGame()
    {
        $exception = $this->getMock('Doctrine\ORM\NoResultException');

        $repository = $this->getGameRepositoryMock();
        $repository
            ->expects($this->once())
            ->method('findGame')
            ->with($this->equalTo('abcdefghij'))
            ->will($this->throwException($exception));
        ;

        $context = new GameContext();
        $context->setGameRepository($repository);
        $this->assertFalse($context->loadGame('abcdefghij'));
    }

    public function testLoadExistingGame()
    {
        $data = $this->getGameDataMock();
        $data
            ->expects($this->once())
            ->method('toGame')
            ->will($this->returnValue($this->getGameMock()))
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
            'Sensio\Bundle\HangmanBundle\Entity\GameRepositoryInterface',
            $context->getGameRepository()
        );

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

        $this->assertInstanceOf(
            'Sensio\Bundle\HangmanBundle\Game\WordList',
            $context->getWordList()
        );
        $this->assertEquals('java', $context->getRandomWord(4));
    }

    private function getGameMock()
    {
        $game = $this->getMock(
            'Sensio\Bundle\HangmanBundle\Game\Game',
            array(),
            array(),
            '',
            false
        );

        return $game;
    }

    private function getGameDataMock()
    {
        $data = $this->getMock(
            'Sensio\Bundle\HangmanBundle\Entity\GameData',
            array(),
            array(),
            '',
            false
        );

        return $data;
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
        $repository = $this->getMock(
            'Sensio\Bundle\HangmanBundle\Entity\GameRepositoryInterface',
            array(),
            array(),
            '',
            false
        );

        return $repository;
    }
}