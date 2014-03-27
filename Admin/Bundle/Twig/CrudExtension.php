<?php

namespace Visy\Visy\Admin\Bundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface as ContainerInterface;

class CrudExtension extends \Twig_Extension
{

    protected $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getGlobals()
    {
        return [

        ];
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getName()
    {
        return 'visy_admin_crud_extension';
    }
}