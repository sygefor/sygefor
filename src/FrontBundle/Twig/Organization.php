<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 3/18/19
 * Time: 12:42 PM
 */

namespace FrontBundle\Twig;

use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Organization as OrganizationEntity;

/**
 * Class Organization.
 */
class Organization extends \Twig_Extension
{
	/** @var EntityManager */
	protected $em;

	/**
	 * Organization constructor.
	 *
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 * @return array|\Twig_SimpleFunction[]
	 */
	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction('allOrganizations', array($this, 'getAllOrganizations')),
		];
	}

	/**
	 * @return array|Organization[]
	 */
	public function getAllOrganizations()
	{
		return $this->em->getRepository(OrganizationEntity::class)->findBy([], ['name' => 'ASC']);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'organizations';
	}
}