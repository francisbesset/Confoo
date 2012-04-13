<?php

namespace Sensio\Bundle\HangmanBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\HangmanBundle\Entity\Player;
use Sensio\Bundle\HangmanBundle\Form\PlayerType;

class PlayerController extends Controller
{
    /**
     * @Route("/login", name="login")
     * @Template()
     *
     */
    public function loginAction(Request $request)
    {
        $session = $request->getSession();

        if ($error = $session->get(SecurityContext::AUTHENTICATION_ERROR)) {
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return array(
            'error' => $error,
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
        );
    }

    /**
     * @Template()
     * @Cache(smaxage=120)
     *
     */
    public function recentAction($limit)
    {
        $em    = $this->getDoctrine()->getEntityManager();
        $table = $em->getRepository('SensioHangmanBundle:Player');

        return array(
            'players' => $table->getMostRecentPlayers($limit),
        );
    }

    /**
     * @Route("/registration", name="registration")
     * @Template()
     *
     */
    public function registrationAction(Request $request)
    {
        $player = new Player();
        $form   = $this->createForm(new PlayerType(), $player);

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);
            if ($form->isValid()) {

                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($player);

                $player->encodePassword($encoder);

                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($player);
                $em->flush();

                $token = new UsernamePasswordToken(
                    $player,
                    $player->getPassword(),
                    'players',
                    $player->getRoles()
                );

                $this->get('security.context')->setToken($token);

                return $this->redirect($this->generateUrl('hangman_game'));
            }
        }

        return array('form' => $form->createView());
    }
}