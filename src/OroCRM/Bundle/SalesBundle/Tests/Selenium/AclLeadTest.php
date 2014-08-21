<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Sales;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\Leads;

class AclLeadTest extends Selenium2TestCase
{
    public function testCreateRole()
    {
        $randomPrefix = mt_rand();
        $login = $this->login();
        $login->openRoles('Oro\Bundle\UserBundle')
            ->add()
            ->setLabel('Label_' . $randomPrefix)
            ->setOwner('Main')
            ->setEntity('Lead', array('Create', 'Edit', 'Delete', 'View', 'Assign'), 'System')
            ->assertTitle('Create Role - Roles - User Management - System')
            ->save()
            ->assertMessage('Role saved')
            ->assertTitle('Roles - User Management - System')
            ->close();

        return ($randomPrefix);
    }

    /**
     * @depends testCreateRole
     * @param $role
     * @return string
     */
    public function testCreateUser($role)
    {
        $username = 'User_'.mt_rand();

        $login = $this->login();
        $login->openUsers('Oro\Bundle\UserBundle')
            ->add()
            ->assertTitle('Create User - Users - User Management - System')
            ->setUsername($username)
            ->enable()
            ->setOwner('Main')
            ->setFirstpassword('123123q')
            ->setSecondpassword('123123q')
            ->setFirstName('First_'.$username)
            ->setLastName('Last_'.$username)
            ->setEmail($username.'@mail.com')
            ->setRoles(array('Label_' . $role))
            ->uncheckInviteUser()
            ->save()
            ->assertMessage('User saved')
            ->toGrid()
            ->close()
            ->assertTitle('Users - User Management - System');

        return $username;
    }

    /**
     * @return string
     */
    public function testCreateLead()
    {
        $name = 'Lead_'.mt_rand();

        $login = $this->login();
        /** @var Leads $login */
        $login->openLeads('OroCRM\Bundle\SalesBundle')
            ->add()
            ->setName($name)
            ->setFirstName($name . '_first_name')
            ->setLastName($name . '_last_name')
            ->assertTitle('Create Lead - Leads - Sales')
            ->save()
            ->assertMessage('Lead saved')
            ->toGrid()
            ->assertTitle('Leads - Sales');

        return $name;
    }


    /**
     * @depends testCreateUser
     * @depends testCreateRole
     * @depends testCreateLead
     *
     * @param $aclCase
     * @param $username
     * @param $role
     * @param $leadName
     *
     * @dataProvider columnTitle
     */
    public function testTaskAcl($aclCase, $username, $role, $leadName)
    {
        $roleName = 'Label_' . $role;
        $login = $this->login();
        switch ($aclCase) {
            case 'delete':
                $this->deleteAcl($login, $roleName, $username, $leadName);
                break;
            case 'update':
                $this->updateAcl($login, $roleName, $username, $leadName);
                break;
            case 'create':
                $this->createAcl($login, $roleName, $username);
                break;
            case 'view':
                $this->viewAcl($login, $username, $roleName, $leadName);
                break;
        }
    }

    public function deleteAcl($login, $roleName, $username, $leadName)
    {
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Lead', array('Delete'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openLeads('OroCRM\Bundle\SalesBundle')
            ->filterBy('Lead name', $leadName)
            ->checkActionMenu('Delete')
            ->open(array($leadName))
            ->assertElementNotPresent("//div[@class='pull-left btn-group icons-holder']/a[@title='Delete Lead']");
    }

    public function updateAcl($login, $roleName, $username, $leadName)
    {
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Lead', array('Edit'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openLeads('OroCRM\Bundle\SalesBundle')
            ->filterBy('Lead name', $leadName)
            ->checkActionMenu('Update')
            ->open(array($leadName))
            ->assertElementNotPresent("//div[@class='pull-left btn-group icons-holder']/a[@title='Edit Lead']");
    }

    public function createAcl($login, $roleName, $username)
    {
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Lead', array('Create'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openLeads('OroCRM\Bundle\SalesBundle')
            ->assertElementNotPresent(
                "//div[@class='pull-right title-buttons-container']//a[contains(., 'Create Lead')]"
            );
    }

    public function viewAcl($login, $username, $roleName)
    {
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Lead', array('View'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openLeads('OroCRM\Bundle\SalesBundle')
            ->assertTitle('403 - Forbidden');
    }

    /**
     * Data provider for Tags ACL test
     *
     * @return array
     */
    public function columnTitle()
    {
        return array(
            'delete' => array('delete'),
            'update' => array('update'),
            'create' => array('create'),
            'view' => array('view'),
        );
    }
}
