<?php

namespace TaskPlannerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use TaskPlannerBundle\Entity\Category;
use TaskPlannerBundle\Entity\CategoryRepository;
use TaskPlannerBundle\Entity\User;
use TaskPlannerBundle\TaskPlannerBundle;

class TaskType extends AbstractType
{
    // Variable $user and construct below are created to enable getting current logged user by the TaskType class.
    // This will be used next for selecting categories that belongs to the currently logged user only.
    private $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('createdAt')
            ->add('description')
            ->add('toBeFinishedAt')
            ->add('priority')
            ->add('isFinished')
            ->add('isDeleted')
            ->add('user')
            ->add('category', 'entity', array(
                'class' => 'TaskPlannerBundle:Category',
                // by using query builder here I can select categories that belong to the currently logged user only
                'query_builder' => function(CategoryRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.user = :loggedUser')
                        ->orderBy('c.name', 'ASC')
                        ->setParameter("loggedUser", $this->user)
                        ;
                },
                'choice_label' => 'name',
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TaskPlannerBundle\Entity\Task'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'taskplannerbundle_task';
    }
}
