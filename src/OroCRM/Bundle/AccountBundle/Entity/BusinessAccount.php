<?php

namespace OroCRM\Bundle\AccountBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;

/**
 * Class BusinessAccount
 * @package OroCRM\Bundle\AccountBundle\Entity
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeName="duon_business_account_index",
 *      routeView="duon_business_account_view",
 *      defaultValues={
 *          "entity"={
 *              "label"="Business Account",
 *              "plural_label"="Business Accounts",
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
class BusinessAccount extends Account{

    protected $account_type_disc = 1;

}