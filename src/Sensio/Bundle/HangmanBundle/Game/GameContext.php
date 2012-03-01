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
    private $data;

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
        return $this->data;
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
        if (!$this->data = $this->repository->findOneBy(array('token' => $token))) {
            return false;
        }

        return $this->data->toGame();
    }

    public function save(Game $game)
    {
        if (null === $this->data) {
            $this->data = new GameData();
            $this->data->setPlayer($this->securityContext->getToken()->getUser());
            $this->data->setWord($game->getWord());
        }

        $this->data->fromGame($game);
        $this->em->persist($this->data);
        $this->em->flush();
    }
}