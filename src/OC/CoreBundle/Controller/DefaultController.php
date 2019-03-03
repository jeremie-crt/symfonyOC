<?php

namespace OC\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class DefaultController
 * @package OC\CoreBundle\Controller
 */
class DefaultController extends Controller
{

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function indexAction()
    {
        return $this->render('OCCoreBundle:Default:index.html.twig');
    }


	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function contactAction()
    {
		//Add flash message
	    $this->addFlash('advise', 'La page de contact n’est pas encore disponible');

        return $this->render('OCCoreBundle:Default:index.html.twig');
    }
}
