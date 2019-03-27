<?php

namespace OC\PlatformBundle\Purge;

use Doctrine\ORM\EntityManager;

class Purger
{

	protected $em;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	public function checkAdvertForPurge($days)
	{
		$advertRepo = $this->em->getRepository('OCPlatformBundle:Advert');
		$advertSkillRepo = $this->em->getRepository('OCPlatformBundle:AdvertSkill');

		$advertsToPurge = $advertRepo->checkAdvertsForPurgerService(new \DateTime('-' . $days . ' day'));

		$result = null;

		foreach ($advertsToPurge as $advert) {

			$advertSkills = $advertSkillRepo->findByAdvert($advert);
			foreach ($advertSkills as $advertSkill) {
				$this->em->remove($advertSkill);

				if($advertSkill) {
					$result = true;
				}
			}

			$this->em->remove($advert);


		}

		$this->em->flush();

		return $result;
	}
}