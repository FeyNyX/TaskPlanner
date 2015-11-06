<?php
// src/TaskPlannerBundle/Entity/User.php

namespace TaskPlannerBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @OneToMany(targetEntity="Category", mappedBy="user")
     **/
    protected $categories;

    /**
     * Add category
     *
     * @param \TaskPlannerBundle\Entity\Category $category
     *
     * @return Category
     */
    public function addCategory(\TaskPlannerBundle\Entity\Category $category)
    {
        $this->categories[] = $category;

        return $this;
    }

    /**
     * Remove category
     *
     * @param \TaskPlannerBundle\Entity\Category $category
     */
    public function removeCategory(\TaskPlannerBundle\Entity\Category $category)
    {
        $this->categories->removeElement($category);
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @OneToMany(targetEntity="Task", mappedBy="user")
     **/
    protected $tasks;

    public function __construct()
    {
        parent::__construct();
        $this->categories = new ArrayCollection();
        $this->tasks = new ArrayCollection();
    }

    /**
     * Add task
     *
     * @param \TaskPlannerBundle\Entity\Task $task
     *
     * @return Task
     */
    public function addTask(\TaskPlannerBundle\Entity\Task $task)
    {
        $this->tasks[] = $task;

        return $this;
    }

    /**
     * Remove task
     *
     * @param \TaskPlannerBundle\Entity\Task $task
     */
    public function removeTask(\TaskPlannerBundle\Entity\Task $task)
    {
        $this->tasks->removeElement($task);
    }

    /**
     * Get tasks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTasks()
    {
        return $this->tasks;
    }
}