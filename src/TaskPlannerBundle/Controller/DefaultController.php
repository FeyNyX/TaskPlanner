<?php

namespace TaskPlannerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        // getting all user's categories
        $repo = $this->getDoctrine()->getRepository("TaskPlannerBundle:Category");
        $categories = $repo->findByUserIsDeletedAware($this->getUser());

        return array(
            "categories" => $categories
        );
    }
}
