<?php

namespace FrontBundle\Controller\API;

use Sygefor\Bundle\ApiBundle\Controller\TrainingController as ApiTrainingController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/api/training")
 */
class TrainingController extends ApiTrainingController
{
    protected static $authorizedFields = array(
        'session' => array(
          'id',
          'name',
          'dateBegin',
          'dateEnd',
          'year',
          'semester',
          'semesterLabel',
          'limitRegistrationDate',
          'hourNumber',
          'dayNumber',
          'numberOfParticipants',
          'price',
          'maximumNumberOfRegistrations',
          'place',
          'registration',
          'displayOnline',
          'availablePlaces',
          'participations',
          'schedule',
          'promote',
          'status',
        ),
        'training' => array(
          'id',
          'type',
          'typeLabel',
          'typeLabel.source',
          'organization',
          'number',
          'serial',
          'theme',
          'tags',
          'name',
          'description',
          'program',
          'prerequisite',
          'publicType',
          'firstSessionPeriodSemester',
          'firstSessionPeriodYear',
        ),
    );
}
