<?php

namespace TaskPlannerBundle\Entity;

/**
 * TaskRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TaskRepository extends \Doctrine\ORM\EntityRepository
{
    // This method is essential for filtering tasks by user that created them. It is aware of isDeleted status.
    public function findByUserIsDeletedAware(User $user)
    {
        $em = $this->getEntityManager();
        return $em->createQuery("SELECT t FROM TaskPlannerBundle:Task t WHERE t.user = :user AND NOT t.isDeleted = 1")->setParameter("user", $user)->getResult();
    }

    // This method is a variation of "find" that is aware of isDeleted status.
    // "getOneOrNullResult" prevents getting no results that could lead to an unwanted exception.
    public function findIsDeletedAware($id)
    {
        $em = $this->getEntityManager();
        return $em->createQuery("SELECT t FROM TaskPlannerBundle:Task t WHERE t.id = :id AND NOT t.isDeleted = 1")->setParameter("id", $id)->getOneOrNullResult();
    }

    public function findUserCategoriesIsDeletedAware(User $user)
    {
        $em = $this->getEntityManager();
        return $em->createQuery("SELECT c FROM TaskPlannerBundle:Category c WHERE c.user = :user AND NOT c.isDeleted = 1")->setParameter("user", $user)->getResult();
    }

    //@todo Find tasks that are not finished and that are not deleted.
    public function findTasksToRemind($date)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT t FROM TaskPlannerBundle:Task t WHERE t.toBeFinishedAt < :date ORDER BY t.user ASC'
            )
            ->setParameter('date', $date)
            ->getResult();
    }
}
