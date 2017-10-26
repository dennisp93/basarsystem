<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Participant;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="entrance")
     */
    public function entranceAction()
    {
        /** @var Participant $user */
        $user = $this->getUser();
        $role = $user->getRole();

        if ($role === 'ROLE_USER' && $user->isIncomplete()) {
            return $this->redirectToRoute('completeData');
        }

        else if ($role === 'ROLE_USER') {
            return $this->redirectToRoute('overview');
        }

        else if ($role === 'ROLE_ADMIN' && $user->isIncomplete()) {
            return $this->redirectToRoute('completeData');
        }

        else if ($role === 'ROLE_ADMIN') {
            return $this->redirectToRoute('adminOverview');
        }

        else {
            return $this->redirectToRoute('logout');
        }
    }

    /**
     * @Route("/login", name="login")
     */
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('AppBundle:default:login.html.twig', [
            'error' => $error,
            'lastUsername' => $lastUsername
        ]);
    }
}
