<?php
/**
 * Created by PhpStorm.
 * User: j.certain
 * Date: 24/01/2019
 * Time: 12:45
 */

namespace OC\PlatformBundle\Controller;

use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\AdvertSkill;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Form\AdvertEditType;
use OC\PlatformBundle\Form\AdvertType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AdvertController extends Controller
{

	/**
	 * @param $page
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function indexAction($page)
	{
		if ($page < 1) {
			throw new NotFoundHttpException('Page "'.$page.'" does not exist.');
		}

		$repository = $this->getDoctrine()->getManager()->getRepository('OCPlatformBundle:Advert');
		$nbPerPage = 2;
		$listAdverts = $repository->getAdverts($page, $nbPerPage);

		$nbPages = ceil(count($listAdverts) / $nbPerPage);

		if($page > $nbPages) {
			throw $this->createNotFoundException('The page '. $page . ' does not exist');
		}

		return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
			'listAdverts' => $listAdverts,
			'nbPages' => $nbPages,
			'page' => $page,
		));
	}

	/**
	 * @param $id
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function viewAction($id)
	{
		$em = $this->getDoctrine()->getManager();

		$advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

		if (null === $advert) {
			throw new NotFoundHttpException("Announce with " . $id . "doen't exist.");
		}

		$listApplications = $em
			->getRepository('OCPlatformBundle:Application')->findBy(array('advert' => $advert));

		$listAdvertSkills = $em->getRepository('OCPlatformBundle:AdvertSkill')->findBy(array('advert' => $advert));
		return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
			'advert'           => $advert,
			'listApplications' => $listApplications,
			'listAdvertSkills' => $listAdvertSkills
		));
	}

	/**
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function addAction(Request $request)
	{
		//Instancie l'objet pour le Form
		$advert = new Advert();
		$formBuilder = $this->get('form.factory')->create(AdvertType::class, $advert);
		//Raccourcie avec le CTL
		//$form = $this->createForm(AdvertType::class, $advert)

		if($request->isMethod('POST') && $formBuilder->handleRequest($request)->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->persist($advert);
			$em->flush();

			$request->getSession()->getFlashBag()->add('notice', 'Advert has been correctly saved.');

		return $this->redirectToRoute('oc_platform_view', array(
			'id' => $advert->getId()
		));
	}

		return $this->render('OCPlatformBundle:Advert:add.html.twig', array(
			'form' => $formBuilder->createView(),
		));
	}

	/**
	 * @param $id
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function editAction($id, Request $request)
	{
		$advert = $this->getDoctrine()->getManager()->getRepository('OCPlatformBundle:Advert')->find($id);

		$formBuilder = $this->get('form.factory')->create(AdvertEditType::class, $advert);

		if (null === $advert) {
			throw new NotFoundHttpException("Advert with this ".$id." does not exist.");
		}

		if($request->isMethod('POST') && $formBuilder->handleRequest($request)->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->flush();

			$request->getSession()->getFlashBag()->add('notice', 'Advert has been edited.');

			return $this->redirectToRoute('oc_platform_view', array(
				'id' => $advert->getId()
			));
		}

		return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
			'advert' => $advert,
			'form'   => $formBuilder->createView()
		));
	}

	/**
	 * @param $advertId
	 * @return Response
	 */
	public function editImageAction($advertId)
	{
		$em = $this->getDoctrine()->getManager();

		// On récupère l'annonce
		$advert = $em->getRepository('OCPlatformBundle:Advert')->find($advertId);

		// On modifie l'URL de l'image par exemple
		$advert->getImage()->setUrl('test.png');

		$em->flush();

		return new Response('OK');
	}

	/**
	 * @param $id
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function deleteAction($id, Request $request)
	{

		$em = $this->getDoctrine()->getManager();

		// On récupère l'annonce $id
		$advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

		if (null === $advert) {
			throw new NotFoundHttpException("L'annonce d'id ".$id." does not exist.");
		}

		$form = $this->get('form.factory')->create();
		if ($request->isMethod('POST') && $form->handleRequest($request)->isValid() ) {
			$em->remove($advert);
			$em->flush();

			$request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée.");

			return $this->redirectToRoute('oc_platform_home');
		}

		return $this->render('OCPlatformBundle:Advert:delete.html.twig', array(
			'advert' => $advert,
			'form'   => $form->createView()
		));
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 * Sends data to template
	 */
	public function menuAction($limit)
	{
		$repository = $this->getDoctrine()->getManager()->getRepository('OCPlatformBundle:Advert');

		$listAdverts = $repository->findBy(
			array(),
			array('date' => 'desc'),
			$limit,
			0
		);

		return $this->render('OCPlatformBundle:Advert:menu.html.twig', array('listAdverts' => $listAdverts));
	}

	/**
	 * @param $days
	 */
	public function purgeAction($days, Request $request)
	{
		//Call the service
		$purger = $this->container->get('oc_platform.purger.advert');
		$purger->checkAdvertForPurge($days);

		$request->getSession()->getFlashBag()->add('info', 'Adverts have been purged.');

		return $this->redirectToRoute('oc_platform_home');

	}

	public function testAction()
	{
		$advert = new Advert;

		$advert->setDate(new \Datetime());  // Champ « date » OK
		$advert->setTitle('abc');           // Champ « title » incorrect : moins de 10 caractères
		//$advert->setContent('blabla');    // Champ « content » incorrect : on ne le définit pas
		$advert->setAuthor('A');            // Champ « author » incorrect : moins de 2 caractères

		// On récupère le service validator
		$validator = $this->get('validator');

		// On déclenche la validation sur notre object
		$listErrors = $validator->validate($advert);

		// Si $listErrors n'est pas vide, on affiche les erreurs
		if(count($listErrors) > 0) {
			// $listErrors est un objet, sa méthode __toString permet de lister joliement les erreurs
			return new Response((string) $listErrors);
		} else {
			return new Response("L'annonce est valide !");
		}
	}
}