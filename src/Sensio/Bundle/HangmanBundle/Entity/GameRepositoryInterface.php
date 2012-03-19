<?php

namespace Sensio\Bundle\HangmanBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

interface GameRepositoryInterface
{
    /**
     * Finds a user's game with a token value.
     *
     * @param string $token The unique game token
     * @return GameData
     * @throws NoResultException
     */
    function findGame($token);

    /**
     * Sets the current authenticated UserInterface object.
     *
     * @param UserInterface $user A User object
     */
    function setUser(UserInterface $user);

    /**
     * Persists a GameData entity to the database.
     *
     * @param GameData $data A GameData entity
     */
    function save(GameData $data);
}