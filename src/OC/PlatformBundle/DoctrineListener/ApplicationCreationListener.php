<?php
/**
 * Created by PhpStorm.
 * User: j.certain
 * Date: 25/03/2019
 * Time: 16:52
 */

namespace OC\PlatformBundle\DoctrineListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use OC\PlatformBundle\Email\ApplicationMailer;
use OC\PlatformBundle\Entity\Application;

class ApplicationCreationListener
{

	/**
	 * @var ApplicationMailer
	 */
	private $applicationMailer;

	public function __construct(ApplicationMailer $applicationMailer)
	{
		$this->applicationMailer = $applicationMailer;
	}

	public function postPersist(LifecycleEventArgs $args)
	{
		$entity = $args->getObject();

		if(!$entity instanceof Application) {
			return;
		}

		$this->applicationMailer->sendNewNotification($entity);
	}
}