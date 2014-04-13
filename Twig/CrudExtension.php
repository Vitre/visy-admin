<?php

namespace Visy\Visy\Admin\Bundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface as ContainerInterface;

class CrudExtension extends \Twig_Extension
{

    protected $container;
    protected $request;
    protected $controller;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function setController($controller)
    {
        $this->controller = $controller;
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

    public function getFunctions()
    {
        return array(
            'crud_path'       => new \Twig_Function_Method($this, 'getCrudPath'),
            'crud_show_path'  => new \Twig_Function_Method($this, 'getCrudShowPath'),
            'crud_edit_path'  => new \Twig_Function_Method($this, 'getCrudEditPath'),
            'crud_new_path'   => new \Twig_Function_Method($this, 'getCrudNewPath'),
            'crud_index_path' => new \Twig_Function_Method($this, 'getCrudIndexPath'),
        );
    }

    protected function getUrl($controller, $action, $parameters = [])
    {
        $this->setController($controller);
        $getter = 'get' . ucfirst($action) . 'Url';
        return $this->container->get('visy_visy_admin.crud')->getController($controller)->$getter($parameters);
    }

    public function getCrudNewPath($controller, $parameters = [])
    {
        return $this->getUrl($controller, 'new', $parameters);
    }

    public function getCrudShowPath($controller, $entity = false, $parameters = [])
    {
        return $this->container->get('visy_visy_admin.crud')->getController($controller)->getShowUrl($entity, $parameters);
    }

    public function getCrudEditPath($controller, $entity = false, $parameters = [])
    {
        return $this->container->get('visy_visy_admin.crud')->getController($controller)->getEditUrl($entity, $parameters);
    }

    public function getCrudIndexPath($controller, $parameters = [])
    {
        return $this->getUrl($controller, 'index', $parameters);
    }

    public function getCrudPath($route, $parameters = [])
    {
        return $this->container->get('router')->generate($route, $parameters);
    }

}