<?php

namespace Sensio\Bundle\HangmanBundle\Game;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\HangmanBundle\Entity\GameData;

class GameContext
{
    private $em;

    private $securityContext;

    private $repository;

    private $wordList;

    /**
     * @var GameData
     */
    private $game;

    public function __construct(ObjectManager $em, SecurityContext $securityContext, WordList $list)
    {
        $this->em = $em;
        $this->repository = $em->getRepository('SensioHangmanBundle:GameData');
        $this->securityContext = $securityContext;
        $this->wordList = $list;
    }

    public function getWordList()
    {
        return $this->wordList;
    }

    public function getGameData()
    {
        return $this->game;
    }

    public function newGame($length)
    {
        return new Game($this->getRandomWord($length));
    }

    public function getRandomWord($length)
    {
        return $this->wordList->getRandomWord($length);
    }

    public function loadGame($token)
    {
        if (!$this->game = $this->repository->findOneBy(array('token' => $token))) {
            return false;
        }

        return new Game(
            $this->game->getWord(),
            $this->game->getAttempts(),
            $this->game->getTriedLetters(),
            $this->game->getFoundLetters()
        );
    }

    public function save(Game $game)
    {
        if (null === $this->game) {
            $this->game = new GameData();
            $this->game->setPlayer($this->securityContext->getToken()->getUser());
            $this->game->setWord($game->getWord());
        }

        $this->game->setAttempts($game->getAttempts());
        $this->game->setFoundLetters($game->getFoundLetters());
        $this->game->setTriedLetters($game->getTriedLetters());

        $this->em->persist($this->game);
        $this->em->flush();
    }
}