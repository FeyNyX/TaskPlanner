<?php

namespace TaskPlannerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use TaskPlannerBundle\Entity\Comment;
use TaskPlannerBundle\Form\CommentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Comment controller.
 *
 * @Security("has_role('ROLE_USER')")
 * @Route("/comment")
 */
class CommentController extends Controller
{
    /**
     * Lists all Comment entities.
     *
     * @Route("/", name="comment")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        // findByUserIsDeletedAware is a custom method that lets you get only tasks created by logged user (and not by all users).
        // It's aware of isDeleted status.
        $entities = $em->getRepository('TaskPlannerBundle:Comment')->findByUserIsDeletedAware($this->getTask());

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Creates a new Comment entity.
     *
     * @Route("/{taskId}", name="comment_create")
     * @Method("POST")
     * @Template("TaskPlannerBundle:Comment:new.html.twig")
     */
    public function createAction(Request $request, $taskId)
    {
        $entity = new Comment();
        $form = $this->createCreateForm($entity, $taskId);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // setting creation time
            $entity->setCreatedAt(new \DateTime());

            $task = $em->getRepository("TaskPlannerBundle:Task")->findOneBy(array("id" => $taskId));
            // checking if user is owner of the task and that the task id not deleted
            $checkOwnership = $em->getRepository("TaskPlannerBundle:Comment")->isTaskOwnerIsDeletedAware($this->getUser(), $task);
            if ($checkOwnership == false) {
                throw $this->createAccessDeniedException('You can not create a comment to this task. Either it is deleted and/or it doesn\'t belong to you.');
            }

            // task is being redirected through all actions and views that take part in creating a new comment.
            $entity->setTask($task);

            $em->persist($entity);
            $em->flush();

            // redirecting back to the task that the comment was being created for
            return $this->redirect($this->generateUrl('task_show', array('id' => $taskId)));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Comment entity.
     *
     * @param Comment $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Comment $entity, $taskId)
    {
        $form = $this->createForm(new CommentType(), $entity, array(
            'action' => $this->generateUrl("comment_create", array("taskId" => $taskId)),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Comment entity.
     *
     * @Route("/new/{taskId}", name="comment_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction($taskId)
    {
        $entity = new Comment();
        $form = $this->createCreateForm($entity, $taskId);

        return array(
            'taskId' => $taskId,
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Comment entity.
     *
     * @Route("/{id}", name="comment_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TaskPlannerBundle:Comment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Comment entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Comment entity.
     *
     * @Route("/{id}/edit", name="comment_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TaskPlannerBundle:Comment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Comment entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Creates a form to edit a Comment entity.
     *
     * @param Comment $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Comment $entity)
    {
        $form = $this->createForm(new CommentType(), $entity, array(
            'action' => $this->generateUrl('comment_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing Comment entity.
     *
     * @Route("/{id}", name="comment_update")
     * @Method("PUT")
     * @Template("TaskPlannerBundle:Comment:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TaskPlannerBundle:Comment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Comment entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('comment_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Comment entity.
     *
     * @Route("/{id}", name="comment_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            // "findIsDeletedAware" makes sure that user won't be able to delete already deleted comment.
            $entity = $em->getRepository('TaskPlannerBundle:Comment')->findIsDeletedAware($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Comment entity.');
            }

            // This condition throws an exception when user tries to delete a comment that does not belong to him.
            if ($this->getUser() != $entity->getUser()) {
                throw $this->createAccessDeniedException();
            }

            // Instead of really deleting it we set isDeleted status to 1 (true), for data protection.
            $entity->setIsDeleted(1);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('comment'));
    }

    /**
     * Creates a form to delete a Comment entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('comment_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm();
    }
}
