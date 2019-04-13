<?php
/**
 * Created by PhpStorm.
 * User: j.certain
 * Date: 13/04/2019
 * Time: 12:40
 */

namespace OC\PlatformBundle\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class AntifloodValidator
 * @package OC\PlatformBundle\Validator
 */
class AntifloodValidator extends ConstraintValidator
{

	private $requestStack;
	private $em;

	/**
	 * AntifloodValidator constructor.
	 * @param RequestStack $requestStack
	 * @param EntityManagerInterface $em
	 * Les arguments déclarés dans le service arrivent au construct
	 * On doit les enregistrer dans l'objet pour les utiliser dans la méthode validate()
	 */
	public function __construct(RequestStack $requestStack, EntityManagerInterface $em)
	{
		$this->requestStack = $requestStack;
		$this->em = $em;
	}

	/**
	 * @param mixed $value
	 * @param Constraint $constraint
	 */
	public function validate($value, Constraint $constraint)
	{
		/*
		 $this->context
  ->buildViolation($constraint->message)
  ->setParameters(array('%string%' => $value))
  ->addViolation();
		 */


		// Pour récupérer l'objet Request tel qu'on le connait, il faut utiliser getCurrentRequest du service request_stack
		$request = $this->requestStack->getCurrentRequest();

		$ip = $request->getClientIp();

		$isFlood = $this->em->getRepository('OCPlatformBundle:Application')->isFlood($ip, 15);

		if ($isFlood) {
			$this->context->addViolation($constraint->message);
		}
	}
}