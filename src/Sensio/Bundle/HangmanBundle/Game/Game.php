<?php

namespace Sensio\Bundle\HangmanBundle\Game;

class Game 
{
    const MAX_ATTEMPTS = 11;

    private $word;

    private $attempts;

    private $triedLetters;

    private $foundLetters;

    public function __construct(Word $word, $attempts = 0, $triedLetters = array(), $foundLetters = array())
    {
        $this->word = $word;
        $this->attempts = (int) $attempts;
        $this->triedLetters = $triedLetters;
        $this->foundLetters = $foundLetters;
    }

    public function getRemainingAttempts()
    {
        return static::MAX_ATTEMPTS - $this->attempts;
    }

    public function isLetterFound($letter)
    {
        return in_array($letter, $this->getFoundLetters());
    }

    public function isHanged()
    {
        return static::MAX_ATTEMPTS === $this->attempts;
    }

    public function isWon()
    {
        return $this->word->isGuessed();
    }

    public function getWord()
    {
        return $this->word;
    }

    public function getWordLetters()
    {
        return $this->word->split();
    }

    public function getAttempts()
    {
        return $this->attempts;
    }

    public function getTriedLetters()
    {
        return $this->triedLetters;
    }

    public function getFoundLetters()
    {
        return $this->foundLetters;
    }

    public function reset()
    {
        $this->attempts = 0;
        $this->triedLetters = array();
        $this->foundLetters = array();
    }

    public function tryWord($word)
    {
        if ($word === $this->word->getWord()) {
            $this->word->guessed();

            return true;
        }

        $this->attempts = self::MAX_ATTEMPTS;

        return false;
    }

    public function tryLetter($letter)
    {
        try {
            $result = $this->word->tryLetter($letter);
        } catch (\InvalidArgumentException $e) {
            $result = false;
        }

        $this->triedLetters[] = $letter;

        if (false === $result) {
            $this->attempts++;
        } else {
            $this->foundLetters[] = $letter;
        }

        return $result;
    }
}