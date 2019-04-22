<?php

namespace OC\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class DefaultController
 * @package OC\CoreBundle\Controller
 */
class CoreController extends Controller
{
	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function indexAction()
    {
        return $this->render('OCCoreBundle:Core:index.html.twig');
    }


	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function contactAction(Request $request)
    {
	    $session = $request->getSession();
	    $session->getFlashBag()->add('info', 'Contact page is not available yet;');
	    return $this->redirectToRoute('oc_core_home');
    }
}
