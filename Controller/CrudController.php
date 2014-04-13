<?php

namespace Visy\Visy\Admin\Bundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Knp\Menu\MenuFactory;
use Knp\Menu\Renderer\ListRenderer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * CRUD controller.
 */
class CrudController extends DefaultController
{

    protected $limit = 20;

    protected $entity;

    protected $entityTitleProperty = 'id';

    /**
     * @var array
     * ['name' => '...', 'class' => '...', 'parameter' => '...']
     */
    protected $parentEntity;

    protected $em;

    /**
     * Lists all entities.
     */
    public function indexAction()
    {
        $this->_initAction('index', $this->_getMethodParameters(__FUNCTION__, func_get_args()));

        $this->em = $this->getDoctrine()->getManager();

        $qb = $this->em->createQueryBuilder();
        $qb->select('entity')
            ->from($this->getEntity(), 'entity')
            ->orderBy('entity.id');

        if ($this->hasParentEntity()) {
            $this->initIndexParentQuery($qb);
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $this->get('request')->query->get('page', 1),
            $this->getLimit()
        );

        // PHP console log
        $this->get('vitre_php_console')->log(
            $qb->getQuery()->getDQL(),
            $this->getName()
        );

        return [
            'title'      => $this->getTitle(),
            'pagination' => $pagination,
        ] + $this->createDefaultScope();
    }

    public function _initAction($actionName, $parameters = [])
    {
        $this->_initParameters($parameters);
        $this->get('visy_visy_admin.crud')->registerController($this);
        parent::_initAction($actionName);
    }

    protected function _initParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    protected function _getMethodParameters($method, $args)
    {
        $r = [];
        $ref = new \ReflectionMethod(get_called_class(), $method);
        $params = $ref->getParameters();
        foreach ($params as $i => $param) {
            if (isset($args[$i])) {
                $r[$param->name] = $args[$i];
            }
        }

        return $r;
    }

    public function getEntity()
    {
        return 'undefined';
    }

    public function hasParentEntity()
    {
        return !empty($this->parentEntity);
    }

    public function initIndexParentQuery($qb)
    {
        $qb
            ->where('entity.' . $this->parentEntity['name'] . ' = :parent')
            ->setParameter(':parent', $this->getParentParameter());
    }

    public function getParentParameter()
    {
        return $this->getParameter(isset($this->parentEntity['parameter']) ? $this->parentEntity['parameter'] : $this->parentEntity['name']);
    }

    public function getLimit()
    {
        return $this->limit;
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
            'has_sub_menu'     => $this->hasSubMenu(),
            'has_sup_menu'     => $this->hasSupMenu(),
            'sup_menu'         => $this->hasSupMenu() ? $this->createSupMenu() : false,
        ];
    }

    protected function hasBreadcrumbs()
    {
        return false;
    }

    public function getBreadcrumbs()
    {
        $r = $this->getParentBreadcrumbs();
        if ($this->hasParent()) {
            $r += [$this->getParent()->getEntityName() => $this->getParent()->getBaseBreadcrumb()];
        }
        $r += [$this->getEntityName() => $this->getBaseBreadcrumb()];

        return $r;
    }

    public function getEntityName()
    {
        return 'undefined';
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

    protected function hasSubMenu()
    {
        return false;
    }

    protected function hasSupMenu()
    {
        $r = $this->hasParent() && $this->getParent()->hasSubMenu();

        return $r;
    }

    protected function createSupMenu()
    {
        if ($this->hasParent() && $this->getParent()->hasSubMenu()) {
            return $this->getParent()->createSubMenu();
        }
    }

    public function getNewUrl(array $parameters = [])
    {
        $parameters += $this->getBaseParameters();

        return $this->generateUrl($this->getNewRoute(), $parameters);
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

    /**
     * Creates a new entity.
     */
    public function createAction(Request $request)
    {
        $this->_initAction('create', $this->_getMethodParameters(__FUNCTION__, func_get_args()));

        $this->em = $this->getDoctrine()->getManager();
        $this->entity = $this->getNewEntity();
        $form = $this->createCreateForm($this->entity);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->touchEntity();

            $this->em->persist($this->entity);
            $this->em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Entity created!'
            );

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
        $entity = new $class;

        if ($this->hasParentEntity()) {
            $entity->{'set' . ucfirst($this->parentEntity['name'])}($this->getParentEntity());
        }

        return $entity;
    }

    public function getEntityClass()
    {
        return $this->getDoctrine()->getManager()->getRepository($this->getEntity())->getClassName();
    }

    public function getParentEntity()
    {
        return $this->getDoctrine()->getRepository($this->parentEntity['class'])->find($this->getParentParameter());
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

    public function getCreateUrl(array $parameters = [])
    {
        $parameters += $this->getBaseParameters();

        return $this->generateUrl($this->getCreateRoute(), $parameters);
    }

    public function getCreateRoute()
    {
        return $this->getRouteBase() . '_create';
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

    public function getEntityId($entity = false)
    {
        if ($entity === false) {
            $entity = $this->entity;
        }
        if ($entity) {
            $class = $this->getEntityClass();
            $meta = $this->getDoctrine()->getManager()->getClassMetadata($class);
            $identifier = $meta->getSingleIdentifierFieldName();

            return $entity->{'get' . ucfirst($identifier)}();
        }
    }

    /**
     * Finds and displays an entity.
     */
    public function showAction($id)
    {
        $this->_initAction('show', $this->_getMethodParameters(__FUNCTION__, func_get_args()));

        $this->em = $this->getDoctrine()->getManager();

        $this->entity = $this->em->getRepository($this->getEntity())->find($id);

        if (!$this->entity) {
            throw $this->createNotFoundException('Unable to find entity.');
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
        if ($this->entityTitleProperty) {
            return $this->entity->{'get' . ucfirst($this->entityTitleProperty)}();
        }

        return '#' . $this->getEntityId();
    }

    /**
     * Displays a form to create a new entity.
     */
    public function newAction()
    {
        $this->_initAction('new', $this->_getMethodParameters(__FUNCTION__, func_get_args()));

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
        $this->_initAction('edit', $this->_getMethodParameters(__FUNCTION__, func_get_args()));

        $this->em = $this->getDoctrine()->getManager();

        $this->entity = $this->em->getRepository($this->getEntity())->find($id);

        if (!$this->entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $editForm = $this->createEditForm($this->entity);
        $deleteForm = $this->createDeleteForm($id);

        $subMenu = $this->hasSubMenu() ? $this->createSubMenu() : false;

        return [
            'entity'      => $this->entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'sub_menu'    => $subMenu
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

    public function getUpdateRoute()
    {
        return $this->getRouteBase() . '_update';
    }

    protected function createSubMenu()
    {
        return (new MenuFactory())->createItem('SubMenu' . $this->getName());
    }

    /**
     * Edits an existing entity.
     *
     * @Template("VisyVisyAdminBundle:Crud:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $this->_initAction('update', $this->_getMethodParameters(__FUNCTION__, func_get_args()));

        $this->em = $this->getDoctrine()->getManager();

        $this->entity = $this->em->getRepository($this->getEntity())->find($id);

        if (!$this->entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($this->entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {

            $this->touchEntity();

            $this->em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Changes saved!'
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

    /**
     * Deletes a entity.
     *
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $this->_initAction('delete', $this->_getMethodParameters(__FUNCTION__, func_get_args()));

        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->em = $this->getDoctrine()->getManager();
            $this->entity = $this->em->getRepository($this->getEntity())->find($id);

            if (!$this->entity) {
                throw $this->createNotFoundException('Unable to find entity.');
            }

            $this->em->remove($this->entity);
            $this->em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Entity #' . $id . ' deleted!'
            );
        }

        return $this->redirect($this->generateUrl($this->getIndexRoute()));
    }

    public function initEntity($id)
    {
        $this->em = $this->getDoctrine()->getManager();
        $this->entity = $this->em->getRepository($this->getEntity())->find($id);

        return $this;
    }

}