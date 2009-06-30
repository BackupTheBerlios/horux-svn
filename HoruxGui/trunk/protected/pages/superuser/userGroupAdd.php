<?php
/**
* @version      $Id$
* @package      Horux
* @subpackage   Horux
* @copyright    Copyright (C) 2007  Letux. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* Horux is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

Prado::using('horux.pages.superuser.sql');

class userGroupAdd extends Page
{
    protected $lastId;

    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {
            $this->application->setGlobalState('dataPage',array());

            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();

            $this->addComponent();
        }
    }

    public function addComponent()
    {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_install WHERE type='component'" );
        $data = $cmd->query();
        $data = $data->readAll();

        foreach($data as $d)
        {
            $item = new TListItem;
            $item->setAttribute('Group',Prado::localize('Components'));

            $doc=new TXmlDocument();
            $doc->loadFromFile('./protected/pages/components/'.$d['name'].'/install.xml');
            $name = $doc->getElementByTagName('name');

            $item->setText(Prado::localize($name->getValue()));
            $item->setValue($d['name']);

            $this->accessPage->getItems()->add($item);
        }
    }

    public function getData()
    {
        $data = $this->application->getGlobalState('dataPage');

        return $data;

    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $id = $this->lastId;
                $pBack = array('okMsg'=>Prado::localize('The group was added successfully'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('superuser.userGroupMod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The group was not added'));
                $this->Response->redirect($this->Service->constructUrl('superuser.userGroupAdd',$pBack));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The group was added successfully'));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The group was not saved'));
            }

            $this->application->clearGlobalState('dataPage');
            $this->Response->redirect($this->Service->constructUrl('superuser.userGroupList',$pBack));
        }
    }

    public function saveData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_ADD_USER_GROUP );
        $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":superAdmin",$this->superAdmin->getChecked(),PDO::PARAM_STR);
        $cmd->bindParameter(":description",$this->description->SafeText,PDO::PARAM_STR);


        if(!$cmd->execute()) return false;

        $this->lastId = $this->db->getLastInsertId();

        $this->insertNewPermissions($this->lastId);

        $this->log("Add the super user group :".$this->name->SafeText);

        return true;
    }

    protected function insertNewPermissions($lastId)
    {
        $data = $this->application->getGlobalState('dataPage');

        foreach($data as $k=>$v)
        {
            echo $k;

            switch($k)
            {
                case "controlPanel":
                    $this->insertNewPermission($lastId, 'controlPanel.ControlPanel');
                    break;
                case "superUser":
                    $this->insertNewPermission($lastId, 'superuser.userList');
                    $this->insertNewPermission($lastId, 'superuser.userAdd');
                    $this->insertNewPermission($lastId, 'superuser.userMod');
                    break;
                case "superUserGroup":
                    $this->insertNewPermission($lastId, 'superuser.userGroupList');
                    $this->insertNewPermission($lastId, 'superuser.userGroupAdd');
                    $this->insertNewPermission($lastId, 'superuser.userGroupMod');
                    break;
                case "configuration":
                    $this->insertNewPermission($lastId, 'configuration.config');
                    break;
                case "site":
                    $this->insertNewPermission($lastId, 'site.Site');
                    break;
                case "openTime":
                    $this->insertNewPermission($lastId, 'openTime.openTimeList');
                    $this->insertNewPermission($lastId, 'openTime.add');
                    $this->insertNewPermission($lastId, 'openTime.mod');
                    $this->insertNewPermission($lastId, 'openTime.attribute');
                    break;
                case "hardware":
                    $this->insertNewPermission($lastId, 'hardware.HardwareList');
                    $this->insertNewPermission($lastId, 'hardware.HardwareAddList');

                    $path = './protected/pages/hardware/device/';

                    $files = scandir($path);

                    foreach($files as $f)
                    {
                        if($f != '..' && $f != '.' && $f != '.svn' && is_dir($path.$f))
                        {
                            $doc=new TXmlDocument();
                            $doc->loadFromFile($path.$f.'/install.xml');
                            $permissions = $doc->getElementByTagName('permissions');
                            $permissions = $permissions->getElements();
                            foreach($permissions as $perm)
                            {
                                $this->insertNewPermission($lastId, $perm->getValue());
                            }
                        }
                    }
                    break;
                case "alarms":
                    $this->insertNewPermission($lastId, 'system.Alarms');
                    break;
                case "notification":
                    $this->insertNewPermission($lastId, 'system.Notification');
                    $this->insertNewPermission($lastId, 'system.NotificationAdd');
                    $this->insertNewPermission($lastId, 'system.NotificationMod');
                    break;
                case "service":
                    $this->insertNewPermission($lastId, 'system.Service');
                    break;
                case "status":
                    $this->insertNewPermission($lastId, 'system.Status');
                    break;
                case "user":
                    $this->insertNewPermission($lastId, 'user.UserList');
                    $this->insertNewPermission($lastId, 'user.add');
                    $this->insertNewPermission($lastId, 'user.mod');
                    $this->insertNewPermission($lastId, 'user.attribution');
                    $this->insertNewPermission($lastId, 'user.groups');
                    break;
                case "userGroup":
                    $this->insertNewPermission($lastId, 'userGroup.UserGroupList');
                    $this->insertNewPermission($lastId, 'userGroup.add');
                    $this->insertNewPermission($lastId, 'userGroup.mod');
                    break;
                case "userWizard":
                    $this->insertNewPermission($lastId, 'user.UserWizzard');
                    break;
                case "key":
                    $this->insertNewPermission($lastId, 'key.KeyList');
                    $this->insertNewPermission($lastId, 'key.add');
                    $this->insertNewPermission($lastId, 'key.mod');
                    break;
                case "accessLevel":
                    $this->insertNewPermission($lastId, 'accessLevel.accessLevelList');
                    $this->insertNewPermission($lastId, 'accessLevel.add');
                    $this->insertNewPermission($lastId, 'accessLevel.mod');
                    break;
                case "nonWorkingDay":
                    $this->insertNewPermission($lastId, 'nonWorkingDay.nonWorkingDay');
                    $this->insertNewPermission($lastId, 'nonWorkingDay.add');
                    $this->insertNewPermission($lastId, 'nonWorkingDay.mod');
                    break;
                case "install_uninstall":
                    $this->insertNewPermission($lastId, 'installation.extensions');
                    break;
                case "devices":
                    $this->insertNewPermission($lastId, 'installation.devices');
                    break;
                case "components":
                    $this->insertNewPermission($lastId, 'installation.components');
                    $this->insertNewPermission($lastId, 'installation.componentconfig');
                    break;
                case "template":
                    $this->insertNewPermission($lastId, 'installation.template');
                    break;
                case "language":
                    $this->insertNewPermission($lastId, 'installation.language');
                    break;
                case "checkKey":
                    $this->insertNewPermission($lastId, 'tool.CheckKey');
                    break;
                case "globalCheckin":
                    $this->insertNewPermission($lastId, 'tool.GlobalCheckin');
                    break;
                case "guilog":
                    $this->insertNewPermission($lastId, 'tool.GuiLog');
                    break;
                case "systemInfo":
                    $this->insertNewPermission($lastId, 'help.SystemInfo');
                    break;
                case "about":
                    $this->insertNewPermission($lastId, 'help.About');
                    break;
                default:
                    $doc=new TXmlDocument();
                    $doc->loadFromFile('./protected/pages/components/'.$k.'/install.xml');
                    $permissions = $doc->getElementByTagName('permissions');
                    $permissions = $permissions->getElements();
                    foreach($permissions as $perm)
                    {
                        $this->insertNewPermission($lastId, $perm->getValue());
                    }
                    break;
            }

        }
    }

    protected function insertNewPermission($lasetId, $page)
    {
        $cmd = $this->db->createCommand( SQL::SQL_ADD_PERMISSION );
        $cmd->bindParameter(":page",$page,PDO::PARAM_STR);
        $cmd->bindParameter(":id",$lasetId,PDO::PARAM_STR);

        $cmd->execute();
    }

    public function serverValidateName($sender, $param)
    {
        $cmd = $this->db->createCommand( SQL::SQL_IS_GROUP_EXIST );
        $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
        $data = $cmd->query();
        $data = $data->read();

        if($data['nb'] > 0)
        $param->IsValid=false;
    }

    public function onAddAccess($sender, $param)
    {
        if($this->accessPage->getSelectedValue() == 'none') return;

        $data = $this->application->getGlobalState('dataPage');
        $item = $this->accessPage->getSelectedItem();

        $data[$this->accessPage->getSelectedValue()] = array('id' => $this->accessPage->getSelectedValue(),'Type'=>$item->Attributes->Group, 'Text'=>$item->Text );


        $this->application->setGlobalState('dataPage', $data);

        $this->DataGrid->DataSource=$data;
        $this->DataGrid->dataBind();

    }

    public function onDeleteAccess($sender, $param)
    {
        $data = $this->application->getGlobalState('dataPage');

        unset($data[$sender->Text]);

        $this->application->setGlobalState('dataPage', $data);

        $this->DataGrid->DataSource=$data;
        $this->DataGrid->dataBind();


    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('superuser.userGroupList'));
    }

}
