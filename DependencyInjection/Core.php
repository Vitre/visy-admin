<?php

namespace Visy\Visy\Admin\Bundle\DependencyInjection;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class Core {

    protected $session;

    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $this->sessionStart();
    }

    public function sessionStart() {
        $this->session = $this->container->get('session');
        $this->session->start();
    }

    public function getSession() {
        return $this->session;
    }

}