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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdvertController extends Controller
{
	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 * Sends data to template 
	 */
	public function menuAction($limit)
	{
		$listAdverts = array(
			array('id' => 2, 'title' => 'Recherche développeur Symfony'),
			array('id' => 5, 'title' => 'Mission de webmaster'),
			array('id' => 9, 'title' => 'Offre developpeur React')
		);

		return $this->render('OCPlatformBundle:Advert:menu.html.twig', array('listAdverts' => $listAdverts));
	}

	/**
	 * @param $page
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function indexAction($page)
	{
		if ($page < 1) {

			throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
		}
// Dans l'action indexAction() :
		return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
			'listAdverts' => array(
				array(
					'title'   => 'Recherche développpeur Symfony',
					'id'      => 1,
					'author'  => 'Alexandre',
					'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
					'date'    => new \Datetime()),
				array(
					'title'   => 'Mission de webmaster',
					'id'      => 2,
					'author'  => 'Hugo',
					'content' => 'Nous recherchons un webmaster capable de maintenir notre site internet. Blabla…',
					'date'    => new \Datetime()),
				array(
					'title'   => 'Offre de stage webdesigner',
					'id'      => 3,
					'author'  => 'Mathieu',
					'content' => 'Nous proposons un poste pour webdesigner. Blabla…',
					'date'    => new \Datetime())
			)
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
			->getRepository('OCPlatformBundle:Application')->findBy(array('advert' => $advert))
		;

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
		/*$antispam = $this->container->get('oc_platform.antispam');
		$text = "...";
		if ($antispam->isSpam($text)) {
			throw new \Exception('Detected as spam!');
		}*/
		//Create Entity Advert
		$advert = new Advert();
		$advert->setTitle('Looking for Symfony Developer');
		$advert->setAuthor('Recruiter');
		$advert->setEmail('recruiter@gmail.com');
		$advert->setContent('We are looking for a fresh Developer on Symfony');

		//Create Image Entity
		$image = new Image();
		$image->setUrl("http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg");
		$image->setAlt("amazing job");
		//Links Image to Advert
		$advert->setImage($image);

		// Création d'une première candidature
		$application1 = new Application();
		$application1->setAuthor('Marine');
		$application1->setContent("J'ai toutes les qualités requises.");

		// Création d'une deuxième candidature par exemple
		$application2 = new Application();
		$application2->setAuthor('Pierre');
		$application2->setContent("Je suis très motivé.");

		// On lie les candidatures à l'annonce
		$application1->setAdvert($advert);
		$application2->setAdvert($advert);

		//Get the EntityManager
		$em = $this->getDoctrine()->getManager();
		//Persist Entity: Tells that it goes with Doctrine now
		$em->persist($advert);
		$em->persist($application1);
		$em->persist($application2);

		$listSkills = $em->getRepository('OCPlatformBundle:Skill')->findAll();
		foreach ($listSkills as $skill) {
			$advertSkill = new AdvertSkill();

			// On la lie à l'annonce, qui est ici toujours la même
			$advertSkill->setAdvert($advert);
			// On la lie à la compétence, qui change ici dans la boucle foreach
			$advertSkill->setSkill($skill);

			$advertSkill->setLevel("Expert");

			$em->persist($advertSkill);
		}


		// Doctrine ne connait pas encore l'entité $advert. Si vous n'avez pas défini la relation AdvertSkill
		// avec un cascade persist alors on doit persister $advert
		$em->persist($advert);

		//Flush everything persisted: executes all requests
		$em->flush();

		if ($request->isMethod('POST')) {
			$request->getSession()->getFlashBag()->add('notice', 'Your announce has been saved.');

			return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId() ));
		}

		//If not POST Method, render the form
		return $this->render('OCPlatformBundle:Advert:add.html.twig', array('advert' => $advert));
	}

	/**
	 * @param $id
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function editAction($id, Request $request)
	{
		$em = $this->getDoctrine()->getManager();

		// On récupère l'annonce $id
		$advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

		if (null === $advert) {
			throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
		}
		//Get ALL categories saved
		$listCategories = $em->getRepository('OCPlatformBundle:Category')->findAll();
		//Loop to link advert with one category
		foreach ($listCategories as $category) {
			$advert->addCategory($category);
		}

		$em->flush();

		return $this->render('OCPlatformBundle:Advert:edit.html.twig');

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
	public function deleteAction($id)
	{

		$em = $this->getDoctrine()->getManager();

		// On récupère l'annonce $id
		$advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

		if (null === $advert) {
			throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
		}

		// On boucle sur les catégories de l'annonce pour les supprimer
		foreach ($advert->getCategories() as $category) {
			$advert->removeCategory($category);
		}
		$em->flush();

		return $this->render('OCPlatformBundle:Advert:delete.html.twig');
	}
}