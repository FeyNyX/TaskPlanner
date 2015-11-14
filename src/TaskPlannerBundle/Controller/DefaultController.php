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
        $repo = $this->getDoctrine()->getRepository("TaskPlannerBundle:Category");
        // getting all user's categories if user is logged in
        if (null == ($this->getUser())) {
            return array();
        }

        $categories = $repo->findByUserIsDeletedAware($this->getUser());

        return array(
            "categories" => $categories
        );
    }
}
