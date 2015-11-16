<?php
namespace TaskPlannerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use TaskPlannerBundle\Entity\Category;
use TaskPlannerBundle\Form\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

// @todo Remove all unnecesary actions and secure existing ones.
/**
 * Category controller.
 *
 * @Security("has_role('ROLE_USER')")
 * @Route("/category")
 */
class CategoryController extends Controller
{
    /**
     * Lists all Category entities.
     *
     * @Route("/", name="category")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        // findByUserIsDeletedAware is a custom method that lets you get only categories created by logged user (and not by all users).
        // It's aware of isDeleted status.
        $entities = $em->getRepository('TaskPlannerBundle:Category')->findByUserIsDeletedAware($this->getUser());

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Creates a new Category entity.
     *
     * @Route("/", name="category_create")
     * @Method("POST")
     * @Template("TaskPlannerBundle:Category:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Category();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            // setting currently logged user as an owner of the category
            $entity->setUser($this->getUser());

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            // Instead of showing an exception to the user when he tries to change a name of a category to one that already exists,
            // he will be redirected back to the edit page and info about what went wrong will we displayed.
            try {
                $em->flush();
            } catch (\Exception $e) {
                $error = $entity->getName();
                return $this->redirect($this->generateUrl('category_new', array('error' => $error)));
            }

            return $this->redirect($this->generateUrl('taskplanner_default_index'));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Category entity.
     *
     * @param Category $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Category $entity)
    {
        $form = $this->createForm(new CategoryType(), $entity, array(
            'action' => $this->generateUrl('category_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Category entity.
     *
     * @Route("/new", name="category_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Category();
        $form = $this->createCreateForm($entity);

        // If user was trying to create a category with name that already exist, he will be redirected to creation page
        // and an error explaining what went wrong will be displayed.
        if (isset($_GET["error"])) {
            return array(
                'entity' => $entity,
                'form' => $form->createView(),
                'error' => $_GET["error"]
            );
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Category entity.
     *
     * @Route("/{id}", name="category_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        // "findIsDeletedAware" is a variation of "find" that is aware of isDeleted status.
        $entity = $em->getRepository('TaskPlannerBundle:Category')->findIsDeletedAware($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        // This condition throws an exception when user tries to see a category that does not belong to him.
        if ($this->getUser() != $entity->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Category entity.
     *
     * @Route("/{id}/edit", name="category_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        // "findIsDeletedAware" makes sure that user won't be able to edit already deleted category.
        $entity = $em->getRepository('TaskPlannerBundle:Category')->findIsDeletedAware($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        // This condition throws an exception when user tries to edit a category that does not belong to him.
        if ($this->getUser() != $entity->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        // If user was trying to change a category's name to one that already exist, he will be redirected to edit page
        // and an error explaining what went wrong will be displayed.
        if (isset($_GET["error"])) {
            return array(
                'entity' => $entity,
                'edit_form' => $editForm->createView(),
                'delete_form' => $deleteForm->createView(),
                'error' => $_GET["error"]
            );
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Creates a form to edit a Category entity.
     *
     * @param Category $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Category $entity)
    {
        $form = $this->createForm(new CategoryType(), $entity, array(
            'action' => $this->generateUrl('category_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing Category entity.
     *
     * @Route("/{id}", name="category_update")
     * @Method("PUT")
     * @Template("TaskPlannerBundle:Category:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TaskPlannerBundle:Category')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            // Instead of showing an exception to the user when he tries to change a name of a category to one that already exists,
            // he will be redirected back to the edit page and info about what went wrong will we displayed.
            try {
                $em->flush();
            } catch (\Exception $e) {
                $error = $entity->getName();
                return $this->redirect($this->generateUrl('category_edit', array('id' => $id, 'error' => $error)));
            }

            return $this->redirect($this->generateUrl('category_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Category entity.
     *
     * @Route("/{id}", name="category_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            // "findIsDeletedAware" makes sure that user won't be able to delete already deleted category.
            $entity = $em->getRepository('TaskPlannerBundle:Category')->findIsDeletedAware($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Category entity.');
            }

            // This condition throws an exception when user tries to delete a category that does not belong to him.
            if ($this->getUser() != $entity->getUser()) {
                throw $this->createAccessDeniedException();
            }

            // This loop cascades deletion of tasks and comments that belong to the deleted category.
            foreach ($entity->getTasks() as $task) {
                foreach ($task->getComments() as $comment) {
                    $comment->setIsDeleted(1);
                }
                $task->setIsDeleted(1);
            }

            // Instead of really deleting it we set isDeleted status to 1 (true), for data protection.
            $entity->setIsDeleted(1);

            // changing name to avoid name collisions in future (name and user_id are unique pair)
            $name = $entity->getName();
            $date = new \DateTime();
            $formattedDate = $date->format('YmdHis');
            $entity->setName($name . "_deleted_" . $formattedDate);

            $em->flush();
        }

        return $this->redirect($this->generateUrl('category'));
    }

    /**
     * Creates a form to delete a Category entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('category_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm();
    }
}
