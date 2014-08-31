<?php

/**
 * This file is part of Gitcolab.
 *
 * (c) Mbechezi mlanawo <mlanawo@mbechezi.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gitcolab\Bundle\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Gitcolab\Bundle\AppBundle\Form\Type\ProfileType;

class UserController extends Controller
{
    public function settingsAction()
    {
        $view = $this->view(array(), 200)
            ->setTemplate("GitcolabAppBundle:User:settings.html.twig")
            ->setTemplateVar('users')
        ;

        return $this->handleView($view);
    }

    public function sshAction()
    {

        $keys = $this->getDoctrine()
            ->getManager()
                ->getRepository('GitcolabAppBundle:Key')
                    ->findAll(array('user' =>$this->getuser()));

        $view = $this->view($keys, 200)
            ->setTemplate("GitcolabAppBundle:User:ssh.html.twig")
            ->setTemplateVar('keys')
        ;

        return $this->handleView($view);
    }

    public function profileAction(Request $request)
    {
        $profile = $this->getUser();
        $form = $this->createForm(new ProfileType());

        if ($form->handleRequest($request)->isValid()) {
            $this->getDoctrine()->getManager()->persist($profile);
            $this->getDoctrine()->getManager()->flush();
        }

        $view = $this->view(array('form' => $form ), 200)
            ->setTemplate("GitcolabAppBundle:User:profile.html.twig")
            ->setTemplateVar('user')
        ;

        return $this->handleView($view);
    }

    public function accountAction()
    {
        $view = $this->view(array(), 200)
            ->setTemplate("GitcolabAppBundle:User:account.html.twig")
        ;

        return $this->handleView($view);
    }
}