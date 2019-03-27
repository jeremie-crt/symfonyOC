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

		/*$advert->setDate(new \DateTime());
		$advert = $this->getDoctrine()
			->getManager()
			->getRepository('OCPlatformBundle:Advert')
			->find($id)
		;*/

		//Crée le formbuilder grâce au service form factory
		$formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $advert)
			->add('date', DateType::class)
			->add('title', TextType::class)
			->add('email', TextType::class)
			->add('content', TextareaType::class)
			->add('author', TextType::class)
			->add('published', CheckboxType::class, array('required' => false))
			->add('save', SubmitType::class)
			->getForm()
		;

		if($request->isMethod('POST')) {
			//Fait le lien entre Variables POST et le FORM
			$formBuilder->handleRequest($request);

			if($formBuilder->isValid()) {

				$em = $this->getDoctrine()->getManager();
				$em->persist($advert);
				$em->flush();

				$request->getSession()->getFlashBag()->add('notice', 'Advert has been correctly saved.');

				return $this->redirectToRoute('oc_platform_view', array(
					'id' => $advert->getId()
				));
			}
		}

		//Donne la méthode createView du form à la vue pour qu'elle affiche le form toute seule
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
		$em = $this->getDoctrine()->getManager();

		$advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

		if (null === $advert) {
			throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
		}

		// Ici encore, il faudra mettre la gestion du formulaire

		if ($request->isMethod('POST')) {
			$request->getSession()->getFlashBag()->add('notice', 'Advert has been edited.');

			return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
		}

		return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
			'advert' => $advert
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
	public function deleteAction($id)
	{

		$em = $this->getDoctrine()->getManager();

		// On récupère l'annonce $id
		$advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

		if (null === $advert) {
			throw new NotFoundHttpException("L'annonce d'id ".$id." does not exist.");
		}

		// On boucle sur les catégories de l'annonce pour les supprimer
		foreach ($advert->getCategories() as $category) {
			$advert->removeCategory($category);
		}
		$em->flush();

		return $this->render('OCPlatformBundle:Advert:delete.html.twig');
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
}