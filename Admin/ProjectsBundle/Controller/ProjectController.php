<?php

namespace Visy\Visy\Admin\ProjectsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Visy\Visy\Admin\Bundle\Controller\AbstractCrudController;
use Visy\Visy\ProjectsBundle\Entity\Project;
use Visy\Visy\Admin\ProjectsBundle\Form\ProjectType;
use Knp\Menu\MenuFactory;
use Knp\Menu\Renderer\ListRenderer;
use Visy\Visy\Admin\ProjectsBundle\Controller\MediaController as MediaController;

/**
 * Project controller.
 *
 * @Route("/")
 */
class ProjectController extends AbstractCrudController
{

    protected $routeBase = 'visy_admin_project';

    //---

    public function getEntityName()
    {
        return 'Project';
    }

    public function getBundleNamespace()
    {
        return 'Visy\Visy\Admin\ProjectsBundle';
    }

    public function getEntity()
    {
        return 'VisyVisyProjectsBundle:Project';
    }

    /**
     * Lists all Project entities.
     *
     * @Route("/", name="visy_admin_project")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        return parent::indexAction();
    }

    /**
     * Creates a new Project entity.
     *
     * @Route("/", name="visy_admin_project_create")
     * @Method("POST")
     * @Template("VisyVisyAdminProjectsBundle:Project:new.html.twig")
     */
    public function createAction(Request $request)
    {
        return parent::createAction($request);
    }

    /**
     * Displays a form to create a new Project entity.
     *
     * @Route("/new", name="visy_admin_project_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        return parent::newAction();
    }

    /**
     * Finds and displays a Project entity.
     *
     * @Route("/{id}", name="visy_admin_project_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        return parent::showAction($id);
    }

    /**
     * Displays a form to edit an existing Project entity.
     *
     * @Route("/{id}/edit", name="visy_admin_project_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        return parent::editAction($id);
    }

    /**
     * Edits an existing Project entity.
     *
     * @Route("/{id}", name="visy_admin_project_update")
     * @Method("PUT")
     * @Template("VisyVisyAdminProjectsBundle:Project:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        return parent::updateAction($request, $id);
    }

    /**
     * Deletes a Project entity.
     *
     * @Route("/{id}", name="visy_admin_project_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        return parent::deleteAction($request, $id);
    }

    public function getFormType()
    {
        return new ProjectType();
    }

    public function getTitle()
    {
        return 'Projekty';
    }

    public function getEntityTitle()
    {
        return $this->entity->getName();
    }

    protected function hasSubmenu()
    {
        return true;
    }

    protected function createSubmenu()
    {
        $submenu = (new MenuFactory())->createItem('My menu');
        $item = $submenu->addChild('Hlavní', [
            'uri' => $this->getEditUrl(),
        ]);
        if (1) {
            $item->setCurrent(true);
        }

        $submenu->addChild('Media', ['uri' => $this->getChildController('Media')->getIndexUrl()]);
        $submenu->addChild('Vize', ['uri' => '#visions']);
        //$submenu->addChild('Systémové', ['uri' => '#system']);
        return $submenu;
    }

    protected function initChildControllers()
    {
        $this->initChildController('Media', new MediaController());
    }

}
