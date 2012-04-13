<?php

namespace Sensio\Bundle\HangmanBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/game")
 *
 */
class GameController extends Controller
{
    /**
     * This action handles the list of most recent games in the sidebar.
     *
     * @Template()
     * @Cache(smaxage=60)
     * @return array Template variables
     */
    public function gamesAction($limit)
    {
        $em    = $this->getDoctrine()->getEntityManager();
        $table = $em->getRepository('SensioHangmanBundle:GameData');

        return array(
            'games' => $table->getMostRecentGames($limit)
        );
    }

    /**
     * This action handles the homepage of the Hangman game.
     *
     * @Route("/", name="hangman_game")
     * @Template()
     *
     * @param Request $request The request object
     * @return array Template variables
     */
    public function indexAction(Request $request)
    {
        $context = $this->get('hangman.game_context');
        $length  = $request->query->get('length', $this->container->getParameter('hangman.word_length'));

        if (!$game = $context->loadGame($request->get('token'))) {
            $game = $context->newGame($length);
            $context->save($game);
        }

        return array(
            'game' => $game,
            'token' => $context->getGameData()->getToken()
        );
    }

    /**
     * This action allows the player to try to guess a letter.
     *
     * @Route("/{token}/letter/{letter}", name="play_letter", requirements={ 
     *     "letter"="[A-Z]",
     *     "token"="[a-f0-9]{10}"
     * })
     *
     * @param string $letter The letter the user wants to try
     * @return RedirectResponse
     */
    public function letterAction($token, $letter)
    {
        $context = $this->get('hangman.game_context');

        if (!$game = $context->loadGame($token)) {
            throw $this->createNotFoundException('Unable to load the previous game context.');
        }

        $game->tryLetter($letter);
        $context->save($game);

        if ($game->isWon()) {
            return $this->redirect($this->generateUrl('game_won', array(
                'token' => $token
            )));
        }

        if ($game->isHanged()) {
            return $this->redirect($this->generateUrl('game_hanged', array(
                'token' => $token
            )));
        }

        return $this->redirect($this->generateUrl('hangman_game', array(
            'token' => $token
        )));
    }

    /**
     * This action allows the player to try to guess the word.
     *
     * @Route("/{token}/word", name="play_word", requirements={ 
     *     "token"="[a-f0-9]{10}"
     * })
     * @Method("POST")
     *
     * @param Request $request The Request object
     * @return RedirectResponse
     */
    public function wordAction($token, Request $request)
    {
        $context = $this->get('hangman.game_context');

        if (!$game = $context->loadGame($token)) {
            throw $this->createNotFoundException('Unable to load the previous game context.');
        }

        $game->tryWord($request->request->get('word'));
        $context->save($game);

        if ($game->isWon()) {
            return $this->redirect($this->generateUrl('game_won', array(
                'token' => $token
            )));
        }

        return $this->redirect($this->generateUrl('game_hanged', array(
            'token' => $token
        )));
    }

    /**
     * This action displays the hanged page.
     *
     * @Route("/{token}/hanged", name="game_hanged", requirements={ 
     *     "token"="[a-f0-9]{10}"
     * })
     * @Template()
     *
     * @return array Template variables
     * @throws NotFoundHttpException
     */
    public function hangedAction($token)
    {
        $context = $this->get('hangman.game_context');

        if (!$game = $context->loadGame($token)) {
            throw $this->createNotFoundException('Unable to load the previous game context.');
        }

        if (!$game->isHanged()) {
            throw $this->createNotFoundException('User is not yet hanged.');
        }

        return array('word' => $game->getWord());
    }

    /**
     * This action displays the winning page.
     *
     * @Route("/{token}/won", name="game_won", requirements={ 
     *     "token"="[a-f0-9]{10}"
     * })
     * @Template()
     *
     * @return array Template variables
     * @throws NotFoundHttpException
     */
    public function wonAction($token)
    {
        $context = $this->get('hangman.game_context');

        if (!$game = $context->loadGame($token)) {
            throw $this->createNotFoundException('Unable to load the previous game context.');
        }

        if (!$game->isWon()) {
            throw $this->createNotFoundException('Game is not yet won.');
        }

        return array('word' => $game->getWord());
    }

    /**
     * This action allows the user to reset the hangman game.
     *
     * @Route("/{token}/reset", name="game_reset", requirements={ 
     *     "token"="[a-f0-9]{10}"
     * })
     *
     * @return RedirectResponse
     */
    public function resetAction($token)
    {
        $context = $this->get('hangman.game_context');

        if (!$game = $context->loadGame($token)) {
            throw $this->createNotFoundException('Unable to load the previous game context.');
        }

        $game->reset();
        $context->save($game);

        return $this->redirect($this->generateUrl('hangman_game'));
    }
}
