<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 04/04/19
 * Time: 17:03
 */

namespace AppBundle\Command;

use Doctrine\ORM\Events;
use AppBundle\Entity\Trainer;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Inscription;
use AppBundle\Entity\Trainee\Trainee;
use AppBundle\Entity\CoordinatesTrait;
use FOS\ElasticaBundle\Doctrine\Listener;
use AppBundle\Entity\Evaluation\Evaluation;
use AppBundle\Entity\Session\Participation;
use AppBundle\Entity\Evaluation\NotedCriterion;
use AppBundle\Entity\Evaluation\EvaluatedTheme;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\ProfessionalSituationTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sygefor\Bundle\ApiBundle\Repository\AccountRepository;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Sygefor\Bundle\CoreBundle\EventListener\ElasticaCascadeUpdateListener;

/**
 * Class AnonymizePeopleCommand.
 */
class AnonymizePeopleCommand extends ContainerAwareCommand
{
	/** @var EntityManager */
	private $em;

	/** @var InputInterface */
	private $input;

	/** @var OutputInterface */
	private $output;

	/** @var EncoderFactory */
	private $factory;

	/** @var int */
	private $nbrEntitiesToLoad = 50;

	protected function configure()
	{
		$this->setName('sygefor:anonymize:people');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->input = $input;
		$this->output = $output;
		$this->factory = $this->getContainer()->get('security.encoder_factory');

		// disabled some listeners
		$this->em = $this->getContainer()->get('doctrine')->getEntityManager('default');
		$this->em->getConnection()->getConfiguration()->setSQLLogger(null);
		$this->removeElasticaListeners();

		// do the job
//		$this->alertTrainees();
		$this->anonymizePeople();
	}

	protected function alertTrainees()
	{
		$this->output->writeln("Alert trainees");
		$oneYeartraineeIds = $this->getEntityIds('trainee', 'alert', '-4 years');
		$threeMonthtraineeIds = $this->getEntityIds('trainee', 'alert', '-57 month');
//		$traineeIds = $this->removeAlreadyAlertedTrainees($traineeIds);

		foreach ([$oneYeartraineeIds, $threeMonthtraineeIds] as $key => $traineeIds) {
			$this->output->writeln(count($traineeIds) . ' to be alerted.');

			if (count($traineeIds) > 0) {
				$this->output->writeln('Sending ' . count($traineeIds) . ' emails...');
			}

			$nbrEmails = $this->getContainer()->get('sygefor_core.batch.email')->execute($traineeIds, [
				'organization' => 1,
				'forceEmailSending' => true,
				'targetClass' => Trainee::class,
				'notification_template' => 'trainee.not_used',
				'additionalParams' => [
					'period' => $key === 0 ? 'un an' : 'trois mois',
				],
			]);
			$spool = $this->getContainer()->get('swiftmailer.mailer.default.spool');
			$transport = $this->getContainer()->get('swiftmailer.transport.real');
			$spool->flushQueue($transport);

			if ($nbrEmails > 0) {
				$this->output->writeln($nbrEmails . " trainees alerted.");
			}
		}
	}

	/**
	 * Anonymize trainees and trainers
	 */
	protected function anonymizePeople()
	{
		$this->anonymizeTrainees();
		$this->anonymizeTrainers();
	}

	protected function anonymizeTrainees()
	{
		$this->output->writeln("Anonymize trainees");
		$traineeIds = $this->getEntityIds('trainee', 'remove');
		$this->output->writeln(count($traineeIds) . ' trainees to be removed.');
		$spool = $this->getContainer()->get('swiftmailer.mailer.default.spool');
		$transport = $this->getContainer()->get('swiftmailer.transport.real');

		/** @var QueryBuilder $qb */
		$qb = $this->em->getRepository(Trainee::class)->createQueryBuilder('t')
			->select('t')
			->where('t.id in (:traineeIds)')
			->setParameter('traineeIds', $traineeIds)
			->setMaxResults($this->nbrEntitiesToLoad);
		$trainees = $qb->getQuery()->execute();
		$countTrainees = count($trainees);

		while (count($trainees) > 0) {
			/** @var Trainee $oldTrainee */
			foreach ($trainees as $oldTrainee) {
				$this->getContainer()->get('sygefor_core.batch.email')->execute([$oldTrainee->getId()], [
					'organization' => $oldTrainee->getOrganization(),
					'forceEmailSending' => true,
					'targetClass' => Trainee::class,
					'notification_template' => 'trainee.anonymized',
					'additionalParams' => [
						'storeEmail' => false,
					],
				]);

				$newTrainee = new Trainee();
				$this->em->persist($newTrainee);
				$this->changeTraineeInfos($oldTrainee, $newTrainee);
				$this->changeTraineeInscriptionInfos($oldTrainee, $newTrainee);
				$this->em->remove($oldTrainee);
			}
			$this->em->flush();
			$this->em->clear();
			gc_collect_cycles();
			$spool->flushQueue($transport);
			$this->output->writeln(strval($countTrainees) . " trainees anomymized");
			$trainees = $qb->getQuery()->execute();
			$countTrainees += $this->nbrEntitiesToLoad;
		}
	}

	protected function anonymizeTrainers()
	{
		$this->output->writeln("Anonymize trainers");
		$trainerIds = $this->getEntityIds('trainer', 'remove');
		$this->output->writeln(count($trainerIds) . ' trainers to be removed.');

		/** @var QueryBuilder $qb */
		$qb = $this->em->getRepository(Trainer::class)->createQueryBuilder('t')
			->select('t')
			->where('t.id in (:trainerIds)')
			->setParameter('trainerIds', $trainerIds)
			->setMaxResults($this->nbrEntitiesToLoad);
		$trainers = $qb->getQuery()->execute();
		$countTrainers = count($trainers);
		$trainerIds = array_keys($trainerIds);

		while (count($trainers) > 0) {
			/** @var Trainer $oldTrainer */
			foreach ($trainers as $oldTrainer) {
				$newTrainer = new Trainer();
				$this->em->persist($newTrainer);
				$this->changeTrainerInfos($oldTrainer, $newTrainer);
				$this->em->remove($oldTrainer);
				unset($trainerIds[$oldTrainer->getId()]);
			}

			$this->em->flush();
			$this->em->clear();
			gc_collect_cycles();
			$this->output->writeln(strval($countTrainers) . " trainers anomymized");
			$trainerIds = array_keys($trainerIds);
			$trainers = $qb->getQuery()->execute();
			$countTrainers += $this->nbrEntitiesToLoad;
		}
	}

	/**
	 * @param Trainee        $oldTrainee
	 * @param Trainee        $trainee
	 */
	protected function changeTraineeInfos($oldTrainee, $trainee)
	{
		// Trainee
		$trainee->setOrganization($oldTrainee->getOrganization());
		$encoder = $this->factory->getEncoder($trainee);
		$trainee->setPassword($encoder->encodePassword(AccountRepository::generatePassword(), $trainee->getSalt()));
		$trainee->setCreatedAt($oldTrainee->getCreatedAt());
		$trainee->setUpdatedAt($oldTrainee->getUpdatedAt());
		$trainee->setIsActive($oldTrainee->getIsActive());
		$trainee->setArchived(true);
		$trainee->setNewsletter(false);

		// PersonTrait
		$trainee->setTitle($oldTrainee->getTitle());
		$trainee->setFirstName('Stagiaire');
		$trainee->setLastName('ANONYMISÉ');

		$trainee->setDisciplinaryDomain($oldTrainee->getDisciplinaryDomain());
		$this->anonymizeProfessionalInfos($trainee, $oldTrainee);
		$this->anonymizeCoordinatesInfos($trainee);
	}

	/**
	 * @param Trainee $trainee
	 * @param Trainee $newTrainee
	 */
	protected function changeTraineeInscriptionInfos($trainee, $newTrainee)
	{
		$inscriptionsCorrespondence = [];
		$collection = new ArrayCollection();
		$inscriptions = $trainee->getInscriptions();
		/** @var Inscription $oldInscription */
		foreach ($inscriptions as $oldInscription) {
			$newInscription = new Inscription();
			$newInscription->setTrainee($newTrainee);
			$newInscription->setSession($oldInscription->getSession());
			$newInscription->setInscriptionStatus($oldInscription->getInscriptionStatus());
			$newInscription->setPresenceStatus($oldInscription->getPresenceStatus());
			if ($oldInscription->getEvaluation()) {
				$inscriptionsCorrespondence[$oldInscription->getId()] = $newInscription;
			}
			$newInscription->setCreatedAt($oldInscription->getCreatedAt());
			$newInscription->setUpdatedAt($oldInscription->getUpdatedAt());
			$this->em->persist($newInscription);
			$collection->add($newInscription);
		}
		$newTrainee->setInscriptions($collection);

		// create new evaluation
		if (count($inscriptionsCorrespondence) > 0) {
			$this->em->flush();
			/** @var Inscription $oldInscription */
			foreach ($inscriptions as $oldInscription) {
				if ($evaluation = $oldInscription->getEvaluation()) {
					$newInscription = $inscriptionsCorrespondence[$oldInscription->getId()];
					$newEvaluation = new Evaluation($newInscription);
					$this->em->persist($newEvaluation);
					/** @var EvaluatedTheme $theme */
					foreach ($evaluation->getThemes() as $theme) {
						$newTheme = new EvaluatedTheme($newEvaluation, $theme->getTheme(), null, $theme->getComments());
						$this->em->persist($newTheme);
						/** @var NotedCriterion $criterion */
						foreach ($theme->getCriteria() as $criterion) {
							$criteria = (new NotedCriterion($criterion->getTheme(), $criterion->getCriterion(), $criterion->getNote()));
							$newTheme->addCriterion($criteria);
							$this->em->persist($criteria);
						}
						$newEvaluation->addTheme($newTheme);
					}
					$newEvaluation->setBadPoints($evaluation->getBadPoints());
					$newEvaluation->setGoodPoints($evaluation->getGoodPoints());
					$newEvaluation->setSuggestions($evaluation->getSuggestions());
					$newInscription->setEvaluation($newEvaluation);
				}
			}
		}
	}

	/**
	 * @param Trainer     $oldTrainer
	 * @param Trainer     $trainer
	 */
	protected function changeTrainerInfos($oldTrainer, $trainer)
	{
		// PersonTrait
		$trainer->setTitle($oldTrainer->getTitle());
		$trainer->setFirstName("Formateur");
		$trainer->setLastName("ANONYMISÉ");

		// CoordinatesTrait
		$this->anonymizeCoordinatesInfos($trainer);
		$this->anonymizeProfessionalInfos($trainer, $oldTrainer);

		// Trainer
		$trainer->setIsArchived(true);
		$trainer->setIsAllowSendMail(false);
		$trainer->setIsOrganization($oldTrainer->getIsOrganization());
		$trainer->setOrganization($oldTrainer->getOrganization());
		$trainer->setIsPublic(false);
		$trainer->setResponsabilities(null);
		$trainer->setComments(null);
		$trainer->setCreatedAt($oldTrainer->getCreatedAt());
		$trainer->setUpdatedAt($oldTrainer->getUpdatedAt());
		
		/** @var Participation $participation */
		foreach ($oldTrainer->getParticipations() as $participation) {
			$participation->setTrainer($trainer);
			$oldTrainer->removeParticipation($participation);
			$trainer->addParticipation($participation);
		}
	}

	/**
	 * @param $entity
	 */
	protected function anonymizeCoordinatesInfos($entity)
	{
		/** @var CoordinatesTrait $entity */
		/** @var CoordinatesTrait $oldEntity */
		$entity->setAddressType(false);
		$entity->setAddress(null);
		$entity->setZip(null);
		$entity->setCity(null);
		$entity->setPhoneNumber(null);
		$email = uniqid()."@sygefor.com";
		$entity->setEmail($email);
		$entity->setWebsite(null);
	}

	/**
	 * @param $entity
	 * @param $oldEntity
	 */
	protected function anonymizeProfessionalInfos($entity, $oldEntity)
	{
		/** @var ProfessionalSituationTrait $entity */
		/** @var ProfessionalSituationTrait $oldEntity */
		$entity->setPublicType($oldEntity->getPublicType());
		$entity->setOtherPublicType($oldEntity->getOtherPublicType());
		$entity->setPublicCategory($oldEntity->getPublicCategory());
		$entity->setPosition($oldEntity->getPosition());
		$entity->setService($oldEntity->getService());
		$entity->setIsPaying($oldEntity->getIsPaying());
	}

	/**
	 * @param $entity
	 * @param $type
	 * @param $period
	 *
	 * @return array
	 */
	protected function getEntityIds($entity, $type, $period = null)
	{
		$sql = $this->getSqlQuery($entity, $type, $period);
		$q = $this->em->getConnection();
		$results = $q->fetchAll($sql);
		$entityIds = [];
		foreach ($results as $res) {
			$entityIds[$res['entityId']] = $res['entityId'];
		}

		return $entityIds;
	}

	protected function getSqlQuery($entity, $type, $period = null)
	{
		$from = "";
		switch ($entity) {
			case 'trainee':
				$maxDate = new \DateTime();
				$from = "FROM (
					SELECT 
						trainee.id AS entityId, 
						MAX(inscription.createdAt) AS maxJoinedEntityDate, 
						trainee.createdAt AS entityCreationDate,
						trainee.is_archived AS is_archived
					FROM trainee
					LEFT JOIN inscription ON inscription.trainee_id = trainee.id
					WHERE is_archived IS FALSE
					GROUP BY trainee.id)";
				switch ($type) {
					case 'remove':
						$dateDiff = "-5 years";
						$maxDate->modify($dateDiff);
						$maxDate = $maxDate->format('Y-m-d H:i:s');
						$where = "sub.maxJoinedEntityDate < '$maxDate' OR (sub.entityCreationDate < '$maxDate' AND sub.maxJoinedEntityDate IS NULL)";
						break;
					case 'alert':
						$minDate = clone $maxDate;
						$dateDiff = $period ? $period : "-4 years";
						$minDate->modify($dateDiff)->modify('-1 month');
						$minDate = $minDate->format('Y-m-d H:i:s');
						$maxDate->modify($dateDiff);
						$maxDate = $maxDate->format('Y-m-d H:i:s');
						$where = "sub.maxJoinedEntityDate >= '$minDate'  AND sub.maxJoinedEntityDate < '$maxDate'
									 OR (sub.entityCreationDate >= '$minDate' AND sub.entityCreationDate < '$maxDate' AND sub.maxJoinedEntityDate IS NULL)";
						break;
					default:
						break;
				}
				break;
			case 'trainer':
				$maxDate = new \DateTime();
				$dateDiff = "-0 years";
				$maxDate->modify($dateDiff);
				$maxDate = $maxDate->format('Y-m-d H:i:s');
				$from = "FROM (
					SELECT 
						trainer.id AS entityId, 
						MAX(session.createdAt) AS maxJoinedEntityDate, 
						trainer.createdAt AS entityCreationDate,
						trainer.is_archived AS is_archived
					FROM trainer
					LEFT JOIN participation ON participation.trainer_id = trainer.id
					LEFT JOIN session ON participation.session_id = session.id
					WHERE is_archived IS FALSE
					GROUP BY trainer.id)";
				$where = "sub.maxJoinedEntityDate < '$maxDate' OR (sub.entityCreationDate < '$maxDate' AND sub.maxJoinedEntityDate IS NULL)";
				break;
			default:
				break;
		}

		return "SELECT entityId $from sub WHERE $where";
	}

	protected function removeAlreadyAlertedTrainees($traineeIds)
	{
		// get trainee already alerted
		$sql = "SELECT trainee_id FROM email WHERE LOWER(subject) LIKE '%votre compte est inactif%'";
		$q = $this->em->getConnection();
		$results = $q->fetchAll($sql);
		$entityIds = [];
		foreach ($results as $res) {
			$entityIds[$res['trainee_id']] = $res['trainee_id'];
		}
		$removed = [];
		foreach ($entityIds as $traineeId) {
			if (isset($traineeIds[$traineeId])) {
				$removed[$traineeId] = $traineeId;
				unset($traineeIds[$traineeId]);
			}
		}
		if (count($removed) > 0) {
			$this->output->writeln(count($removed) . ' trainees already alerted.');
		}

		return $traineeIds;
	}

	/**
	 * Remove elasticsearch indexing during the anonymization by removing elastica listeners
	 */
	protected function removeElasticaListeners()
	{
		$listeners = $this->em->getEventManager()->getListeners(Events::postFlush);
		foreach ($listeners as $listener) {
			if ($listener instanceof Listener || $listener instanceof ElasticaCascadeUpdateListener) {
				$this->em->getEventManager()->removeEventListener(Events::postFlush, $listener);
			}
		}
	}

	/**
	 * Replace special chars by non special chars
	 * 
	 * @param $string
	 * @return string
	 */
	protected function removeSpecialChars($string)
	{
		$replace = [
			'&lt;' => '', '&gt;' => '', '&#039;' => '', '&amp;' => '',
			'&quot;' => '', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'Ae',
			'&Auml;' => 'A', 'Å' => 'A', 'Ā' => 'A', 'Ą' => 'A', 'Ă' => 'A', 'Æ' => 'Ae',
			'Ç' => 'C', 'Ć' => 'C', 'Č' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C', 'Ď' => 'D', 'Đ' => 'D',
			'Ð' => 'D', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E',
			'Ę' => 'E', 'Ě' => 'E', 'Ĕ' => 'E', 'Ė' => 'E', 'Ĝ' => 'G', 'Ğ' => 'G',
			'Ġ' => 'G', 'Ģ' => 'G', 'Ĥ' => 'H', 'Ħ' => 'H', 'Ì' => 'I', 'Í' => 'I',
			'Î' => 'I', 'Ï' => 'I', 'Ī' => 'I', 'Ĩ' => 'I', 'Ĭ' => 'I', 'Į' => 'I',
			'İ' => 'I', 'Ĳ' => 'IJ', 'Ĵ' => 'J', 'Ķ' => 'K', 'Ł' => 'K', 'Ľ' => 'K',
			'Ĺ' => 'K', 'Ļ' => 'K', 'Ŀ' => 'K', 'Ñ' => 'N', 'Ń' => 'N', 'Ň' => 'N',
			'Ņ' => 'N', 'Ŋ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',
			'Ö' => 'Oe', '&Ouml;' => 'Oe', 'Ø' => 'O', 'Ō' => 'O', 'Ő' => 'O', 'Ŏ' => 'O',
			'Œ' => 'OE', 'Ŕ' => 'R', 'Ř' => 'R', 'Ŗ' => 'R', 'Ś' => 'S', 'Š' => 'S',
			'Ş' => 'S', 'Ŝ' => 'S', 'Ș' => 'S', 'Ť' => 'T', 'Ţ' => 'T', 'Ŧ' => 'T',
			'Ț' => 'T', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'Ue', 'Ū' => 'U',
			'&Uuml;' => 'Ue', 'Ů' => 'U', 'Ű' => 'U', 'Ŭ' => 'U', 'Ũ' => 'U', 'Ų' => 'U',
			'Ŵ' => 'W', 'Ý' => 'Y', 'Ŷ' => 'Y', 'Ÿ' => 'Y', 'Ź' => 'Z', 'Ž' => 'Z',
			'Ż' => 'Z', 'Þ' => 'T', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a',
			'ä' => 'ae', '&auml;' => 'ae', 'å' => 'a', 'ā' => 'a', 'ą' => 'a', 'ă' => 'a',
			'æ' => 'ae', 'ç' => 'c', 'ć' => 'c', 'č' => 'c', 'ĉ' => 'c', 'ċ' => 'c',
			'ď' => 'd', 'đ' => 'd', 'ð' => 'd', 'è' => 'e', 'é' => 'e', 'ê' => 'e',
			'ë' => 'e', 'ē' => 'e', 'ę' => 'e', 'ě' => 'e', 'ĕ' => 'e', 'ė' => 'e',
			'ƒ' => 'f', 'ĝ' => 'g', 'ğ' => 'g', 'ġ' => 'g', 'ģ' => 'g', 'ĥ' => 'h',
			'ħ' => 'h', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i',
			'ĩ' => 'i', 'ĭ' => 'i', 'į' => 'i', 'ı' => 'i', 'ĳ' => 'ij', 'ĵ' => 'j',
			'ķ' => 'k', 'ĸ' => 'k', 'ł' => 'l', 'ľ' => 'l', 'ĺ' => 'l', 'ļ' => 'l',
			'ŀ' => 'l', 'ñ' => 'n', 'ń' => 'n', 'ň' => 'n', 'ņ' => 'n', 'ŉ' => 'n',
			'ŋ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'oe',
			'&ouml;' => 'oe', 'ø' => 'o', 'ō' => 'o', 'ő' => 'o', 'ŏ' => 'o', 'œ' => 'oe',
			'ŕ' => 'r', 'ř' => 'r', 'ŗ' => 'r', 'š' => 's', 'ù' => 'u', 'ú' => 'u',
			'û' => 'u', 'ü' => 'ue', 'ū' => 'u', '&uuml;' => 'ue', 'ů' => 'u', 'ű' => 'u',
			'ŭ' => 'u', 'ũ' => 'u', 'ų' => 'u', 'ŵ' => 'w', 'ý' => 'y', 'ÿ' => 'y',
			'ŷ' => 'y', 'ž' => 'z', 'ż' => 'z', 'ź' => 'z', 'þ' => 't', 'ß' => 'ss',
			'ſ' => 'ss', 'ый' => 'iy', 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G',
			'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I',
			'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
			'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F',
			'Х' => 'H', 'Ц' => 'C', 'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'SCH', 'Ъ' => '',
			'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA', 'а' => 'a',
			'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
			'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l',
			'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's',
			'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
			'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e',
			'ю' => 'yu', 'я' => 'ya',
		];

		return mb_strtolower(str_replace(array_keys($replace), $replace, $string), "UTF-8");
	}
}