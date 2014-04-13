<?php

namespace Visy\Visy\Admin\Bundle;

use Symfony\Component\DependencyInjection\ContainerInterface as ContainerInterface;

class Crud
{

    protected $container;
    protected $controllers = [];

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function registerController($controller)
    {
        $this->controllers[$controller->getName()] = $controller;
        return $this;
    }

    public function getController($name)
    {
        if (isset($this->controllers[$name])) {
            return $this->controllers[$name];
        } else {
            trigger_error('Visy admin CRUD controller "' . $name . '" not registered.', \E_USER_ERROR);
        }
    }
}