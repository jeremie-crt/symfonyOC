<?php
/**
 * Created by PhpStorm.
 * User: j.certain
 * Date: 13/04/2019
 * Time: 16:59
 */

namespace OC\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
	public function loginAction(Request $request)
	{
		//Si déjà authentifié, redirige
		if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			return $this->redirectToRoute('oc_platform_home');
		}

		//Service authentication_utils, récupère le nom user
		//et l'erreur si le form à déjà été soumis mais était invalide
		$authenticationUtils = $this->get('security.authentication_utils');

		return $this->render('OCUserBundle:Security:login.html.twig', array(
			'lastname' => $authenticationUtils->getLastUsername(),
			'error' => $authenticationUtils->getLastAuthenticationError()
		));
	}
}