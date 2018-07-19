<?php

namespace rontBundle\DataFixtures\ORM;

use AppBundle\Entity\Term\Evaluation\Criterion;
use AppBundle\Entity\Term\Evaluation\Theme;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;

/**
 * Class LoadEvaluationCriterion.
 */
class LoadEvaluationCriterion extends AbstractTermLoad
{
    public static $class = Criterion::class;

    public function getTerms()
    {
        $themes = $this->manager->getRepository(Theme::class)->findAll();

        return [
            [
                'name' => 'Le respect du programme et des objectifs',
                'theme' => $themes[0],
            ],
            [
                'name' => 'L\'équilibre entre les apports théoriques et les apports pratiques',
                'theme' => $themes[0],
            ],
            [
                'name' => 'L\'intérêt des contenus',
                'theme' => $themes[0],
            ],
            [
                'name' => 'La prise en compte de vos attentes',
                'theme' => $themes[1],
            ],
            [
                'name' => 'L\'expertise du formateur sur la thématique',
                'theme' => $themes[1],
            ],
            [
                'name' => 'La clarté des explications',
                'theme' => $themes[1],
            ],
            [
                'name' => 'La relation du formateur avec le groupe',
                'theme' => $themes[1],
            ],
            [
                'name' => 'Le rythme de travail',
                'theme' => $themes[2],
            ],
            [
                'name' => 'L\'alternance entre la théorie et la pratique',
                'theme' => $themes[2],
            ],
            [
                'name' => 'Les méthodes pédagogiques utilisées (exercices, jeux de rôles, études de cas…)',
                'theme' => $themes[2],
            ],
            [
                'name' => 'La documentation et/ou les contenus numériques mis à votre disposition',
                'theme' => $themes[3],
            ],
            [
                'name' => 'L\'accueil, la salle, le matériel utilisé (vidéoprojecteur, paperboard, etc.)',
                'theme' => $themes[3],
            ],
        ];
    }

    public function getOrder()
    {
        return 2;
    }
}
