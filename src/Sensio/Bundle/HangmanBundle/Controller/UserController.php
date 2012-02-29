<?php

namespace Sensio\Bundle\HangmanBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\HangmanBundle\Entity\User;
use Sensio\Bundle\HangmanBundle\Form\UserType;

class UserController extends Controller
{
    /**
     * @Template()
     * @Cache(smaxage=120)
     */
    public function recentUsersAction($limit)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $table = $em->getRepository('SensioHangmanBundle:User');

        return array(
            'users' => $table->getMostRecentUsers($limit)
        );
    }

    /** @Template() */
    public function loginAction(Request $request)
    {
        $session = $request->getSession();

        return array(
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error' => $session->get(SecurityContext::AUTHENTICATION_ERROR),
        );
    }

    /**
     * @Route("/registration", name="registration")
     * @Template()
     */
    public function registrationAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(new UserType(), $user);

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);
            if ($form->isValid()) {

                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);

                $user->updatePassword($encoder);

                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($user);
                $em->flush();

                $token = new UsernamePasswordToken(
                    $user,
                    $user->getPassword(),
                    'players',
                    $user->getRoles()
                );

                $this->get('security.context')->setToken($token);

                return $this->redirect($this->generateUrl('registration_success'));
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/registration/success", name="registration_success")
     * @Template()
     */
    public function successAction()
    {
        return array();
    }
}