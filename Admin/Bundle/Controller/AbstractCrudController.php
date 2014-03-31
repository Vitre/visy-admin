<?php

namespace Visy\Visy\Admin\Bundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Knp\Menu\MenuFactory;
use Knp\Menu\Renderer\ListRenderer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * CRUD controller.
 */
abstract class AbstractCrudController extends AbstractController
{

    protected $limit = 20;

    protected $entity;

    public function initAction()
    {
        $this->get('visy_visy_admin.crud')->registerController($this);
        parent::initAction();
    }

    /**
     * Lists all entities.
     */
    public function indexAction()
    {
        $this->initAction();

        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();
        $qb->select('entity')
            ->from($this->getEntity(), 'entity')
            ->orderBy('entity.id');

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $this->get('request')->query->get('page', 1),
            $this->getLimit()
        );

        return [
            'title'      => $this->getTitle(),
            'pagination' => $pagination
        ] + $this->createDefaultScope();
    }

    public function getEntity()
    {
        return 'undefined';
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getTitle()
    {
        return $this->getEntity();
    }

    protected function createDefaultScope()
    {
        return parent::createDefaultScope() + [
            'controller'       => $this->getName(),
            'title'            => $this->getTitle(),
            'has_breadcrumbs'  => $this->hasBreadcrumbs(),
            'breadcrumbs'      => $this->getBreadcrumbs(),
            'crud_index_route' => $this->getIndexRoute(),
            'crud_new_route'   => $this->getNewRoute(),
            'crud_edit_route'  => $this->getEditRoute(),
            'crud_show_route'  => $this->getShowRoute(),
        ];
    }

    protected function hasBreadcrumbs()
    {
        return !$this->hasSubmenu();
    }

    protected function hasSubmenu()
    {
        return false;
    }

    public function getBreadcrumbs()
    {
        return $this->getParentBreadcrumbs() + ['base' => $this->getBaseBreadcrumb()];
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

    public function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        if ($this->hasParent()) {
            $parameters += $this->getParentUrlParameters();
        }
        return parent::generateUrl($route, $parameters, $referenceType);
    }

    public function getParentUrlParameters()
    {
        $r = [];
        if ($this->hasParent()) {
            $r[$this->getParentRouteVariable()] = $this->parent->getEntityId();
        }
        return $r;
    }

    protected function getParentRouteVariable()
    {
        return strtolower($this->parent->getEntityName());
    }

    public function getBaseBreadcrumb()
    {
        return [
            'title' => $this->getTitle(),
            'uri'   => $this->getIndexUrl()
        ];
    }

    public function getNewRoute()
    {
        return $this->getRouteBase() . '_new';
    }

    public function getEditRoute()
    {
        return $this->getRouteBase() . '_edit';
    }

    public function getShowRoute()
    {
        return $this->getRouteBase() . '_show';
    }

    public function getNewUrl(array $parameters = [])
    {
        $parameters += $this->getBaseParameters();
        return $this->generateUrl($this->getNewRoute(), $parameters);
    }

    public function getCreateUrl(array $parameters = [])
    {
        $parameters += $this->getBaseParameters();
        return $this->generateUrl($this->getCreateRoute(), $parameters);
    }

    public function getCreateRoute()
    {
        return $this->getRouteBase() . '_create';
    }

    /**
     * Creates a new entity.
     */
    public function createAction(Request $request)
    {
        $this->initAction();

        $this->entity = $this->getNewEntity();
        $form = $this->createCreateForm($this->entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $this->touchEntity();

            $em->persist($this->entity);

            $em->flush();

            return $this->redirect($this->getShowUrl());
        }

        return [
            'entity' => $this->entity,
            'form'   => $form->createView(),
        ] + $this->createDefaultScope();
    }

    public function getNewEntity()
    {
        $class = $this->getEntityClass();
        return new $class;
    }

    public function getEntityClass()
    {
        return $this->getDoctrine()->getManager()->getRepository($this->getEntity())->getClassName();
    }

    /**
     * Creates a form to create an entity.
     *
     * @param Project $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    protected function createCreateForm($entity)
    {
        $form = $this->createForm($this->getFormType(), $entity, array(
            'action' => $this->getCreateUrl(),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    public function getFormType()
    {
        $class = $this->getBundleNamespace . '\\Form\\' . $this->getEntityName() . 'Type';
        return new $class;
    }

    public function getEntityName()
    {
        return 'undefined';
    }

    protected function touchEntity()
    {
        if (method_exists($this->entity, 'touch')) {
            $this->entity->touch();
        }
        return $this;
    }

    public function getShowUrl($entity = false, array $parameters = [])
    {
        $parameters += $this->getBaseParameters();
        if ($entity === false) {
            $entity = $this->entity;
        }
        if (is_object($entity)) {
            $id = $this->getEntityId($entity);
        } else {
            $id = 'DUMMY';
        }
        $parameters['id'] = $id;
        return $this->generateUrl($this->getShowRoute(), $parameters);
    }

    /**
     * Finds and displays an entity.
     */
    public function showAction($id)
    {
        $this->initAction();

        $em = $this->getDoctrine()->getManager();

        $this->entity = $em->getRepository($this->getEntity())->find($id);

        if (!$this->entity) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return [
            'entity'      => $this->entity,
            'delete_form' => $deleteForm->createView(),
        ] + $this->createDefaultScope() + $this->createEntityScope();
    }

    /**
     * Creates a form to delete a entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    protected function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->getDeleteUrl(false, array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm();
    }

    public function getDeleteRoute()
    {
        return $this->getRouteBase() . '_delete';
    }

    public function createEntityScope()
    {
        return [
            'entity_title'    => $this->getEntityTitle(),
            'crud_edit_route' => $this->getEditRoute()
        ];
    }

    public function getEntityTitle()
    {
        return '#' . $this->getEntityId();
    }

    public function getEntityId($entity = false)
    {
        if ($entity === false) {
            $entity = $this->entity;
        }
        $class = $this->getEntityClass();
        $meta = $this->getDoctrine()->getManager()->getClassMetadata($class);
        $identifier = $meta->getSingleIdentifierFieldName();
        return $entity->{'get' . $identifier}();
    }

    /**
     * Displays a form to create a new entity.
     */
    public function newAction()
    {
        $this->initAction();

        $this->entity = $this->getNewEntity();
        $form = $this->createCreateForm($this->entity);

        return [
            'entity' => $this->entity,
            'form'   => $form->createView(),
        ] + $this->createDefaultScope();
    }

    /**
     * Displays a form to edit an existing entity.
     */
    public function editAction($id)
    {
        $this->initAction();

        $em = $this->getDoctrine()->getManager();

        $this->entity = $em->getRepository($this->getEntity())->find($id);

        if (!$this->entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $editForm = $this->createEditForm($this->entity);
        $deleteForm = $this->createDeleteForm($id);

        $submenu = $this->hasSubmenu() ? $this->createSubmenu() : false;

        return [
            'entity'      => $this->entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'submenu'     => $submenu
        ] + $this->createDefaultScope() + $this->createEntityScope();
    }

    /**
     * Creates a form to edit a entity.
     *
     * @param $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    protected function createEditForm($entity)
    {
        $form = $this->createForm($this->getFormType(), $entity, array(
            'action' => $this->getUpdateUrl($entity),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Save'));

        /*
        $form->add('actions', 'form_actions', [
            'buttons' => [
                'save' => ['type' => 'submit', 'options' => ['label' => 'button.save']],
                'cancel' => ['type' => 'button', 'options' => ['label' => 'button.cancel']],
            ]
        ]);
        */

        return $form;
    }

    public function getUpdateRoute()
    {
        return $this->getRouteBase() . '_update';
    }

    /**
     * Edits an existing entity.
     *
     * @Template("VisyVisyAdminBundle:Crud:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $this->initAction();

        $em = $this->getDoctrine()->getManager();

        $this->entity = $em->getRepository($this->getEntity())->find($id);

        if (!$this->entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($this->entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {

            $this->touchEntity();

            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Změny byly uloženy!'
            );

            return $this->redirect($this->getEditUrl(false, ['id' => $id]));
        }

        return [
            'entity'      => $this->entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ] + $this->createDefaultScope();
    }

    public function getEditUrl($entity = false, array $parameters = [])
    {
        $parameters += $this->getBaseParameters();
        if ($entity === false) {
            $entity = $this->entity;
        }
        if (is_object($entity)) {
            $parameters['id'] = $this->getEntityId($entity);
        }
        return $this->generateUrl($this->getEditRoute(), $parameters);
    }

    public function getDeleteUrl($entity = false, array $parameters = [])
    {
        $parameters += $this->getBaseParameters();
        if ($entity === false) {
            $entity = $this->entity;
        }
        if (is_object($entity)) {
            $parameters['id'] = $this->getEntityId($entity);
        }
        return $this->generateUrl($this->getDeleteRoute(), $parameters);
    }

    public function getUpdateUrl($entity = false, array $parameters = [])
    {
        $parameters += $this->getBaseParameters();
        if ($entity === false) {
            $entity = $this->entity;
        }
        if (is_object($entity)) {
            $parameters['id'] = $this->getEntityId($entity);
        }
        return $this->generateUrl($this->getUpdateRoute(), $parameters);
    }

    /**
     * Deletes a entity.
     *
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $this->initAction();

        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $this->entity = $em->getRepository($this->getEntity())->find($id);

            if (!$this->entity) {
                throw $this->createNotFoundException('Unable to find entity.');
            }

            $em->remove($this->entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Entity #' . $id . ' deleted!'
            );
        }

        return $this->redirect($this->generateUrl($this->getIndexRoute()));
    }

}