<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use OroCRM\Bundle\TaskBundle\Tests\Selenium\Pages\Tasks;

class AclTasksTest extends Selenium2TestCase
{
    public function testCreateRole()
    {
        $randomPrefix = mt_rand();
        $login = $this->login();
        $login->openRoles('Oro\Bundle\UserBundle')
            ->add()
            ->setLabel('Label_' . $randomPrefix)
            ->setOwner('Main')
            ->setEntity('Task', array('Create', 'Edit', 'Delete', 'View', 'Assign'), 'System')
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
    public function testCreateTask()
    {
        $subject = 'Tasks_' . mt_rand();

        $login = $this->login();
        /** @var Tasks $login */
        $login->openTasks('OroCRM\Bundle\TaskBundle')
            ->add()
            ->setSubject($subject)
            ->setDescription($subject)
            ->setDueDate('Apr 9, 2014 12:51 PM')
            ->save()
            ->assertMessage('Task saved')
            ->toGrid()
            ->assertTitle('Tasks - Activities');

        return $subject;
    }


    /**
     * @depends testCreateUser
     * @depends testCreateRole
     * @depends testCreateTask
     *
     * @param $aclCase
     * @param $username
     * @param $role
     * @param $taskSubject
     *
     * @dataProvider columnTitle
     */
    public function testTaskAcl($aclCase, $username, $role, $taskSubject)
    {
        $roleName = 'Label_' . $role;
        $login = $this->login();
        switch ($aclCase) {
            case 'delete':
                $this->deleteAcl($login, $roleName, $username, $taskSubject);
                break;
            case 'update':
                $this->updateAcl($login, $roleName, $username, $taskSubject);
                break;
            case 'create':
                $this->createAcl($login, $roleName, $username);
                break;
            case 'view':
                $this->viewAcl($login, $username, $roleName, $taskSubject);
                break;
        }
    }

    public function deleteAcl($login, $roleName, $username, $taskSubject)
    {
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Task', array('Delete'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openTasks('OroCRM\Bundle\TaskBundle')
            ->filterBy('Subject', $taskSubject)
            ->checkActionMenu('Delete')
            ->open(array($taskSubject))
            ->assertElementNotPresent("//div[@class='pull-left btn-group icons-holder']/a[@title='Delete Task']");
    }

    public function updateAcl($login, $roleName, $username, $taskSubject)
    {
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Task', array('Edit'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openTasks('OroCRM\Bundle\TaskBundle')
            ->filterBy('Subject', $taskSubject)
            ->checkActionMenu('Update')
            ->open(array($taskSubject))
            ->assertElementNotPresent("//div[@class='pull-left btn-group icons-holder']/a[@title='Edit Task']");
    }

    public function createAcl($login, $roleName, $username)
    {
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Task', array('Create'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openTasks('OroCRM\Bundle\TaskBundle')
            ->assertElementNotPresent(
                "//div[@class='pull-right title-buttons-container']//a[contains(., 'Create Task')]"
            );
    }

    public function viewAcl($login, $username, $roleName)
    {
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Task', array('View'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openTasks('OroCRM\Bundle\TaskBundle')
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
