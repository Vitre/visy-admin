<?php

namespace Visy\Visy\Admin\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('VisyVisyAdminBundle:Default:index.html.twig', array());
    }
}
