<?php

namespace Visy\Visy\Admin\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class AbstractController
 * @package Visy\Visy\Admin\Bundle\Controller
 */
abstract class AbstractController extends Controller
{

    protected $controllers = [];

    protected $level = 0;

    protected $parent;

    public function indexAction()
    {
        return $this->render('VisyVisyAdminBundle:Default:index.html.twig', array());
    }

    public function getTitle()
    {
        return 'untitled';
    }

    public function hasParent()
    {
        return isset($this->parent);
    }

    public function getParentUrlParameters()
    {
        return [];
    }

    public function getIndexUrl(array $parameters = [])
    {
        return $this->generateUrl($this->getIndexRoute(), $parameters);
    }

    public function getIndexRoute()
    {
        return $this->getRouteBase();
    }

    public function getRouteBase()
    {
        return 'undefined';
    }

    public function getBundleNamespace()
    {
        return 'undefined';
    }

    protected function initAction()
    {
        $this->initChildControllers();
    }

    protected function initChildControllers()
    {
    }

    protected function createDefaultScope()
    {
        return [];
    }

    protected function hasBreadcrumbs()
    {
        return false;
    }

    protected function hasSubmenu()
    {
        return false;
    }

    protected function getParentRouteVariable()
    {
        return false;
    }

    protected function createSubmenu()
    {
        return false;
    }

    protected function getChildController($controller)
    {
        if (isset($this->controllers[$controller])) {
            return $this->controllers[$controller];
        } else {
            return false;
        }
    }

    protected function initChildController($name, $controller)
    {
        $controller->setParent($this);
        $controller->setLevel($this->level + 1);
        $controller->setContainer($this->getContainer());
        $this->controllers[$name] = $controller;
    }

    protected function getContainer()
    {
        return $this->container;
    }

    protected function getLevel()
    {
        return $this->level;
    }

    protected function setLevel($level)
    {
        $this->level = $level;
        return $this;
    }

    protected function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

}
