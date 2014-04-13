<?php

namespace Visy\Visy\Admin\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as FrameworkController;

/**
 * Class AbstractController
 * @package Visy\Visy\Admin\Bundle\Controller
 */
class DefaultController extends FrameworkController
{

    protected $controllers = [];

    protected $level = 0;

    protected $parent;

    protected $routeBase;

    protected $actionName;

    protected $title = 'untitled';

    protected $parameters = [];

    //---

    public function indexAction()
    {
        return $this->render('VisyVisyAdminBundle:Default:index.html.twig', array());
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
        $parameters += $this->getBaseParameters();

        return $this->generateUrl($this->getIndexRoute(), $parameters);
    }

    public function getBaseParameters()
    {
        return [];
    }

    public function getIndexRoute()
    {
        return $this->getRouteBase();
    }

    protected function getRouteBase()
    {
        return $this->routeBase;
    }

    public function getBundleNamespace()
    {
        return 'undefined';
    }

    public function getName()
    {
        return static::staticGetName();
    }

    public static function staticGetName()
    {
        return get_called_class();
    }

    protected function _initAction($actionName)
    {
        $this->setActionName($actionName);
        $this->initChildControllers();
        $this->initParent();
    }

    protected function initParent()
    {
    }

    protected function initChildControllers()
    {
    }

    protected function isAction($actionName)
    {
        return $this->getActionName() == $actionName;
    }

    protected function getActionName()
    {
        return $this->actionName;
    }

    protected function setActionName($name)
    {
        $this->actionName = $name;

        return $this;
    }

    protected function createDefaultScope()
    {
        return [];
    }

    protected function hasBreadcrumbs()
    {
        return false;
    }

    protected function hasSubMenu()
    {
        return false;
    }

    protected function getParentRouteVariable()
    {
        return false;
    }

    protected function createSubMenu()
    {
        return false;
    }

    protected function getChildController($controller)
    {
//ld($controller, $this->controllers);
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
        $this->setChildController($name, $controller);

        return $controller;
    }

    protected function getContainer()
    {
        return $this->container;
    }

    protected function setChildController($name, $controller)
    {
        $this->controllers[$name] = $controller;
//ld($this->controllers);
        return $this;
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

    protected function getParent()
    {
        return $this->parent;
    }

    protected function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    protected function getTitle()
    {
        return $this->title;
    }

    protected function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    protected function mergeParameters(array $parameters)
    {
        $this->parameters += $parameters;

        return $this;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function join($controller)
    {
        $controller->mergeParameters($this->getParameters());

        return $this;
    }

    protected function getParameter($parameter)
    {
        if (isset($this->parameters[$parameter])) {
            return $this->parameters[$parameter];
        }
    }

    protected function setParameter($parameter, $value)
    {
        $this->parameters[$parameter] = $value;

        return $this;
    }

    public function getParentBreadcrumbs()
    {
        return [
            'home' => [
                'title' => 'Home',
                'uri'   => $this->generateUrl('visy_admin_homepage')
            ]
        ];
    }

}
