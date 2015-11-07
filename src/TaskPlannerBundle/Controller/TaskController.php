<?php

namespace TaskPlannerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\DateTime;
use TaskPlannerBundle\Entity\Task;
use TaskPlannerBundle\Form\TaskType;

/**
 * Task controller.
 *
 * @Route("/task")
 */
class TaskController extends Controller
{

    /**
     * Lists all Task entities.
     *
     * @Route("/", name="task")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        // findByUserIsDeletedAware is a custom method that lets you get only tasks created by logged user (and not by all users).
        // It's aware of isDeleted status.
        $entities = $em->getRepository('TaskPlannerBundle:Task')->findByUserIsDeletedAware($this->getUser());

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Task entity.
     *
     * @Route("/", name="task_create")
     * @Method("POST")
     * @Template("TaskPlannerBundle:Task:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Task();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            // setting additional values not handled in form
            $entity->setUser($this->getUser());
            $entity->setCreatedAt(new \DateTime());

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('task_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Task entity.
     *
     * @param Task $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Task $entity)
    {
        // I'm passing user to the TaskType because we want to be able to select categories from logged user, not all users.
        $form = $this->createForm(new TaskType($this->getUser()), $entity, array(
            'action' => $this->generateUrl('task_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Task entity.
     *
     * @Route("/new", name="task_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $em = $this->getDoctrine()->getManager();
        // Gets all categories that belong to the logged in user. IsDeleted aware.
        $userCategories = $em->getRepository("TaskPlannerBundle:Task")->findUserCategoriesIsDeletedAware($this->getUser());
        // We don't want to let user create a task unless he has at least 1 category created.
        if (count($userCategories) < 1) {
            return $this->redirect($this->generateUrl('category_new', array('error' => "no_categories")));
        }

        $entity = new Task();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Task entity.
     *
     * @Route("/{id}", name="task_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        // "findIsDeletedAware" is a variation of "find" that is aware of isDeleted status.
        $entity = $em->getRepository('TaskPlannerBundle:Task')->findIsDeletedAware($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Task entity.');
        }

        // This condition throws an exception when user tries to see a task that does not belong to him.
        if ($this->getUser() != $entity->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Task entity.
     *
     * @Route("/{id}/edit", name="task_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        // "findIsDeletedAware" makes sure that user won't be able to edit already deleted task.
        $entity = $em->getRepository('TaskPlannerBundle:Task')->findIsDeletedAware($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Task entity.');
        }

        // This condition throws an exception when user tries to edit a task that does not belong to him.
        if ($this->getUser() != $entity->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Task entity.
    *
    * @param Task $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Task $entity)
    {
        $form = $this->createForm(new TaskType($entity->getUser()), $entity, array(
            'action' => $this->generateUrl('task_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Task entity.
     *
     * @Route("/{id}", name="task_update")
     * @Method("PUT")
     * @Template("TaskPlannerBundle:Task:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TaskPlannerBundle:Task')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Task entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('task_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Task entity.
     *
     * @Route("/{id}", name="task_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            // "findIsDeletedAware" makes sure that user won't be able to delete already deleted task.
            $entity = $em->getRepository('TaskPlannerBundle:Task')->findIsDeletedAware($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Task entity.');
            }

            // This condition throws an exception when user tries to delete a task that does not belong to him.
            if ($this->getUser() != $entity->getUser()) {
                throw $this->createAccessDeniedException();
            }

            // This loop cascades deletion of comments that belong to the deleted task.
            foreach ($entity->getComments() as $comment) {
                $comment->setIsDeleted(1);
            }

            // Instead of really deleting it we set isDeleted status to 1 (true), for data protection.
            $entity->setIsDeleted(1);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('task'));
    }

    /**
     * Creates a form to delete a Task entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('task_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
