<?php

namespace Visy\Visy\Admin\ProjectsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Visy\Visy\Admin\Bundle\Controller\AbstractCrudController;
use Visy\Visy\ProjectsBundle\Entity\Project;
use Visy\Visy\Admin\ProjectsBundle\Form\MediaType;
use Knp\Menu\MenuFactory;
use Knp\Menu\Renderer\ListRenderer;

/**
 * Media controller.
 *
 * @Route("/{project}/media")
 */
class MediaController extends AbstractCrudController
{

    protected $routeBase = 'visy_admin_project_media';

    //---

    public function getEntityName()
    {
        return 'Media';
    }

    public function getBundleNamespace()
    {
        return 'Visy\Visy\Admin\ProjectsBundle';
    }

    public function getEntity()
    {
        return 'VisyVisyProjectsBundle:Media';
    }

    /**
     * Lists all Media entities.
     *
     * @Route("/", name="visy_admin_project_media")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        return parent::indexAction();
    }

    /**
     * Creates a new Media entity.
     *
     * @Route("/", name="visy_admin_project_media_create")
     * @Method("POST")
     * @Template("VisyVisyAdminProjectsBundle:Media:new.html.twig")
     */
    public function createAction(Request $request)
    {
        return parent::createAction($request);
    }

    /**
     * Displays a form to create a new Media entity.
     *
     * @Route("/new", name="visy_admin_project_media_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        return parent::newAction();
    }

    /**
     * Finds and displays a Media entity.
     *
     * @Route("/{id}", name="visy_admin_project_media_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        return parent::showAction($id);
    }

    /**
     * Displays a form to edit an existing Media entity.
     *
     * @Route("/{id}/edit", name="visy_admin_project_media_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        return parent::editAction($id);
    }

    /**
     * Edits an existing Media entity.
     *
     * @Route("/{id}", name="visy_admin_project_media_update")
     * @Method("PUT")
     * @Template("VisyVisyAdminProjectsBundle:Media:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        return parent::updateAction($request, $id);
    }

    /**
     * Deletes a Media entity.
     *
     * @Route("/{id}", name="visy_admin_project_media_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        return parent::deleteAction($request, $id);
    }

    public function getFormType()
    {
        return new MediaType();
    }

    public function getTitle()
    {
        return 'Media';
    }

    public function getEntityTitle()
    {
        return $this->entity->getName();
    }


    protected function hasSubmenu()
    {
        return false;
    }

    public function getBaseParameters()
    {
        return ['project' => 1];
    }

}
