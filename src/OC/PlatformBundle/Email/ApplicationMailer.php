<?php

namespace OC\PlatformBundle\Email;

use OC\PlatformBundle\Entity\Application;

class ApplicationMailer
{
	/**
	 * @var \Swift_Mailer
	 */
	private $mailer;

	public function __construct(\Swift_Mailer $mailer)
	{
		$this->mailer = $mailer;
	}

	public function sendNewNotification(Application $application)
	{
		$message = new \Swift_Message(
			'New Application',
			'You have received a new application.'
		);


		$message
			->addTo($application->getAdvert()->getEmail())
			->addFrom('local@live.com');

		$this->mailer->send($message);
	}
}