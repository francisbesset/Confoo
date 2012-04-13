<?php

namespace Sensio\Bundle\HangmanBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * GameDataRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class GameDataRepository extends EntityRepository implements GameRepositoryInterface
{
    /**
     * A UserInterface instance.
     *
     * @var UserInterface
     */
    private $player;

    /**
     * Sets a UserInterface object.
     *
     * @param UserInterface $user A User object
     */
    public function setPlayer(UserInterface $player)
    {
        $this->player = $player;
    }

    public function save(GameData $data)
    {
        $this->_em->persist($data);
        $this->_em->flush();
    }

    /**
     * Finds a user's game with a token value.
     *
     * @param string $token The unique game token
     * @return GameData
     * @throws NoResultException
     */
    public function findGame($token)
    {
        $q = $this
            ->createQueryBuilder('g')
            ->leftJoin('g.player', 'p')
            ->where('g.token = :token')
            ->andWhere('p.username = :username')
            ->setParameter('token', $token)
            ->setParameter('username', $this->player->getUsername())
            ->getQuery()
        ;

        return $q->getSingleResult();
    }

    /**
     * Returns the list of the X most recent games.
     *
     * @param integer $limit The number of games to retrieve
     * @return array
     */
    public function getMostRecentGames($limit)
    {
        $q = $this
            ->createQueryBuilder('g')
            ->select('g, p')
            ->leftJoin('g.player', 'p')
            ->orderBy('g.startAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
        ;

        return $q->getResult();
    }
}