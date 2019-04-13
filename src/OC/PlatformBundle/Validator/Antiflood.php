<?php
/**
 * Created by PhpStorm.
 * User: j.certain
 * Date: 13/04/2019
 * Time: 12:31
 */

namespace OC\PlatformBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class Antiflood
 * @package OC\PlatformBundle\Validator
 * @Annotation
 */
class Antiflood extends Constraint
{
	public $message = "You've already posted an announce, you must wait 15sd before adding another one.";

	/**
	 * @return string
	 * demande à se faire valider par le service d'alias renseigner (et non plus par l'objet classique AntifloodValidator)
	 */
	public function validatedBy()
	{
		return 'oc_platform_antiflood';
	}
}