<?php
namespace Sygefor\Bundle\FrontBundle\DataFixtures\ORM;

use Sygefor\Bundle\MyCompanyBundle\Entity\Term\EvaluationCriterion;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;

/**
 * Class LoadEvaluationCriterion.
 */
class LoadEvaluationCriterion extends AbstractTermLoad
{
    static $class = EvaluationCriterion::class;

    public function getTerms()
    {
        return array(
            "Les objectifs de la formation ont-ils été atteints ?",
            "Le niveau de la formation était-il approprié ?",
            "Les contenus du programme ont-ils répondus à vos attentes ?",
            'Avez-vous trouvé les contenus utiles pour vos activités ?',
            'Ont-ils permis des échanges, des confrontations d’idées ?',
            'Y-a-il eu interaction efficace avec le(s) formateur(s) ?',
            'Le rythme de travail était-il adapté ?',
            'Le rapport intervention/travail en groupe ou exercices vous a-t-il paru adapté ?',
            "L’enchainement des contenus vous a-t-il permis de progresser ?",
            'La dynamique de groupe était-elle propice aux échanges ?',
            "Les supports utilisés et/ou fournis vous paraissent-ils utiles ?",
            'Avant de venir en formation, aviez-vous suffisamment d’informations ?',
            "L’organisation matérielle de cette formation était-elle satisfaisante ?",
            'Pensez-vous pouvoir mettre en pratique les différents apports de cette formation ?',
            'Au final, êtes-vous globalement satisfait de la formation suivie ?',
            'La conseilleriez-vous à l’un de vos collègues ?',
        );
    }
}
