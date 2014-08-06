<?php
namespace OroCRM\Bundle\AccountBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * Class IndividualAccount
 * @package OroCRM\Bundle\AccountBundle\Entity
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeName="duon_individual_account_index",
 *      routeView="duon_individual_account_view",
 *      defaultValues={
 *          "entity"={
 *              "label"="Individual Account",
 *              "plural_label"="Individual Accounts",
 *              "icon"="icon-suitcase"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="user_owner_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"=""
 *          },
 *          "merge"={
 *              "enable"=true
 *          },
 *          "form"={
 *              "form_type"="orocrm_account_select"
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          }
 *      }
 * )
 */
class IndividualAccount extends Account
{
    protected $type = 'Individual';

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->account_type = 2;
        parent::beforeSave();
    }
}