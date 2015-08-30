<?php

namespace Weemen\BlogPostBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('WeemenLeonWeemenNLBundle:Default:index.html.twig', array('name' => $name));
    }
}
