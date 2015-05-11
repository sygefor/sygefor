<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 02/06/14
 * Time: 09:50
 */

namespace Sygefor\Bundle\TrainerBundle\Entity\Term;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractOrganizationTerm;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\LocalVocabularyInterface;

/**
 * Institution
 *
 * @ORM\Table(name="competence_field")
 * @ORM\Entity
 */
class CompetenceField extends AbstractOrganizationTerm implements LocalVocabularyInterface
{
    /**
     * @return mixed
     */
    public function getVocabularyName(){
        return "Domaines de compétence";
    }

    /**
     * @return mixed
     * This static method is used to set a specific order field
     * when fetch terms (api)
     */
    public static function orderBy()
    {
        return 'name';
    }
}
