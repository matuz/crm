<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Selenium\Accounts;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use OroCRM\Bundle\AccountBundle\Tests\Selenium\Pages\Accounts;

class AclAccountTest extends Selenium2TestCase
{
    public function testCreateRole()
    {
        $randomPrefix = mt_rand();
        $login = $this->login();
        $login->openRoles('Oro\Bundle\UserBundle')
            ->add()
            ->setLabel('Label_' . $randomPrefix)
            ->setOwner('Main')
            ->setEntity('Account', array('Create', 'Edit', 'Delete', 'View', 'Assign'), 'System')
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
     * @depends testCreateUser
     * @return string
     */
    public function testCreateAccount()
    {
        $accountName = 'Account_'.mt_rand();

        $login = $this->login();
        /** @var Accounts $login */
        $login->openAccounts('OroCRM\Bundle\AccountBundle')
            ->add()
            ->assertTitle('Create Account - Accounts - Customers')
            ->setAccountName($accountName)
            ->setOwner('admin')
            ->save()
            ->assertMessage('Account saved')
            ->toGrid()
            ->assertTitle('Accounts - Customers');

        return $accountName;
    }


    /**
     * @depends testCreateUser
     * @depends testCreateRole
     * @depends testCreateAccount
     *
     * @param $aclCase
     * @param $username
     * @param $role
     * @param $accountName
     *
     * @dataProvider columnTitle
     */
    public function testAccountAcl($aclCase, $username, $role, $accountName)
    {
        $roleName = 'Label_' . $role;
        $login = $this->login();
        switch ($aclCase) {
            case 'delete':
                $this->deleteAcl($login, $roleName, $username, $accountName);
                break;
            case 'update':
                $this->updateAcl($login, $roleName, $username, $accountName);
                break;
            case 'create':
                $this->createAcl($login, $roleName, $username);
                break;
            case 'view':
                $this->viewAcl($login, $username, $roleName, $accountName);
                break;
        }
    }

    public function deleteAcl($login, $roleName, $username, $accountName)
    {
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Account', array('Delete'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openAccounts('OroCRM\Bundle\AccountBundle')
            ->filterBy('Account name', $accountName)
            ->checkActionMenu('Delete')
            ->open(array($accountName))
            ->assertTitle($accountName . " - Accounts - Customers")
            ->assertElementNotPresent("//div[@class='pull-left btn-group icons-holder']/a[@title='Delete Account']");
    }

    public function updateAcl($login, $roleName, $username, $accountName)
    {
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Account', array('Edit'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openAccounts('OroCRM\Bundle\AccountBundle')
            ->filterBy('Account name', $accountName)
            ->checkActionMenu('Update')
            ->open(array($accountName))
            ->assertElementNotPresent("//div[@class='pull-left btn-group icons-holder']/a[@title='Edit Account']");
    }

    public function createAcl($login, $roleName, $username)
    {
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Account', array('Create'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openAccounts('OroCRM\Bundle\AccountBundle')
            ->assertElementNotPresent("//div[@class = 'container-fluid']//a[contains(., 'Create Account')]");
    }

    public function viewAcl($login, $username, $roleName)
    {
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Account', array('View'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openAccounts('OroCRM\Bundle\AccountBundle')
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
