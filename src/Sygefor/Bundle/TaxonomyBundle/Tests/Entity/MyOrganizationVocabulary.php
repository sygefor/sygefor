<?php
namespace Sygefor\Bundle\TaxonomyBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractOrganizationTerm;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\LocalVocabularyInterface;
/**
 * OrganizationVocabulary
 *
 * @ORM\Table(name="test_organization_vocabulary")
 * @ORM\Entity
 */
class MyOrganizationVocabulary extends AbstractOrganizationTerm implements LocalVocabularyInterface
{
    /**
     * @return mixed
     */
    public function getVocabularyName(){
        return "Vocabulaire 2 dedie a une URFIST";
    }

}
