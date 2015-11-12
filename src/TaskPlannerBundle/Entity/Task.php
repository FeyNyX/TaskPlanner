<?php

namespace TaskPlannerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * Task
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TaskPlannerBundle\Entity\TaskRepository")
 */
class Task
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=90)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="to_be_finished_at", type="datetime")
     */
    private $toBeFinishedAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="priority", type="integer")
     */
    private $priority;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_finished", type="boolean")
     */
    private $isFinished = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_deleted", type="boolean")
     */
    private $isDeleted = 0;

    /**
     * @ManyToOne(targetEntity="User", inversedBy="tasks")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     **/
    private $user;

    /**
     * Set user
     *
     * @param integer $user
     *
     * @return Task
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return integer
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @ManyToOne(targetEntity="Category", inversedBy="tasks")
     * @JoinColumn(name="category_id", referencedColumnName="id")
     **/
    private $category;

    /**
     * Set category
     *
     * @param integer $category
     *
     * @return Task
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return integer
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Task
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Task
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Task
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set toBeFinishedAt
     *
     * @param \DateTime $toBeFinishedAt
     *
     * @return Task
     */
    public function setToBeFinishedAt($toBeFinishedAt)
    {
        $this->toBeFinishedAt = $toBeFinishedAt;

        return $this;
    }

    /**
     * Get toBeFinishedAt
     *
     * @return \DateTime
     */
    public function getToBeFinishedAt()
    {
        return $this->toBeFinishedAt;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     *
     * @return Task
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return integer
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set isFinished
     *
     * @param boolean $isFinished
     *
     * @return Task
     */
    public function setIsFinished($isFinished)
    {
        $this->isFinished = $isFinished;

        return $this;
    }

    /**
     * Get isFinished
     *
     * @return boolean
     */
    public function getIsFinished()
    {
        return $this->isFinished;
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return Task
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return boolean
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * @OneToMany(targetEntity="Comment", mappedBy="task")
     **/
    protected $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    /**
     * Add comment
     *
     * @param \TaskPlannerBundle\Entity\Comment $comment
     *
     * @return Comment
     */
    public function addComment(\TaskPlannerBundle\Entity\Comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \TaskPlannerBundle\Entity\Comment $comment
     */
    public function removeComment(\TaskPlannerBundle\Entity\Comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }
}

