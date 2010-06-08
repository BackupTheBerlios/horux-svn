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
            $this->Session['dataPage'] = array();

            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();

            $this->defaultPage->DataTextField='pagename';
            $this->defaultPage->DataValueField='page';
            $this->defaultPage->DataSource=$this->DataPage;
            $this->defaultPage->dataBind();
            $this->defaultPage->setSelectedValue('controlPanel.ControlPanel');

        }


    }

    public function getDataPage()
    {
        $cmd = $this->db->createCommand( "SELECT c.menuname AS pagename, c.page, i . * FROM hr_component AS c LEFT JOIN hr_install AS i ON i.id=c.id_install  ORDER BY pagename" );
        $data_ = $cmd->query();
        $data_ = $data_->readAll();

        for($i=0;$i<count($data_); $i++)
        {
            $data_[$i]['pagename'] = Prado::localize($data_[$i]['pagename'],array(), $data_[$i]['name'])." ({$data_[$i]['name']})" ;
        }


        $data_[] = array('page'=>'controlPanel.ControlPanel', 'pagename'=>Prado::localize('Control Panel'));
        $data_[] = array('page'=>'system.Alarms', 'pagename'=>Prado::localize('Alarms'));
        $data_[] = array('page'=>'system.Status', 'pagename'=>Prado::localize('Status'));
        $data_[] = array('page'=>'user.UserList', 'pagename'=>Prado::localize('User List'));

        return $data_;
    }

    public function addComponent($data)
    {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_install WHERE type='component'" );
        $data_ = $cmd->query();
        $data_ = $data_->readAll();

        $isComponentHasOne = false;

        foreach($data_ as $d)
        {
            $doc=new TXmlDocument();
            $doc->loadFromFile('./protected/pages/components/'.$d['name'].'/install.xml');
            $name = $doc->getElementByTagName('name');
            $installName = $doc->getElementByTagName('installName')->getValue();


            $cmd = $this->db->createCommand("SELECT * FROM hr_install WHERE type='component' AND name='$installName'");
            $data1 = $cmd->query();
            $data1 = $data1->readAll();

            foreach($data1 as $d1)
            {
                $cmd = $this->db->createCommand("SELECT * FROM hr_install AS i LEFT JOIN hr_component as c ON c.id_install=i.id WHERE i.type='component' AND c.parentmenu=0 AND i.id=".$d1['id']);
                $data2 = $cmd->query();
                $data2 = $data2->read();

                if(!$isComponentHasOne)
                {
                    $data[] = array('Type'=>Prado::localize('Components'), 'id'=>$data2['page'], 'Text'=>Prado::localize($name->getValue(), array(),$installName ), 'access'=>true, 'composantname'=>$installName, 'shortcut'=>false);
                    $isComponentHasOne = true;
                }
                else
                    $data[] = array('Type'=>'', 'id'=>$data2['page'], 'Text'=>Prado::localize($name->getValue(), array(),$installName ), 'access'=>true, 'composantname'=>$installName, 'shortcut'=>false);


                $cmd = $this->db->createCommand("SELECT * FROM hr_install AS i LEFT JOIN hr_component as c ON c.id_install=i.id WHERE i.type='component' AND c.parentmenu=".$data2['id']." AND c.parentmenu>0 AND i.id=".$d1['id']);
                $data2 = $cmd->query();
                $data2 = $data2->readAll();

                foreach($data2 as $d2)
                {
                    $data[] = array('Type'=>'', 'id'=>$d2['page'], 'Text'=>'', 'Text2'=>Prado::localize($d2['menuname'], array(),$installName ), 'access'=>true, 'composantname'=>$installName, 'shortcut'=>false);
                }

            }

        }

        return $data;
    }

    public function getData()
    {
        $param = $this->Application->getParameters();
        $groupId = $this->Application->getUser()->getGroupID();

        $data[] = array('Type'=>'Horux', 'id'=>'superUser', 'Text'=>Prado::localize('Super User'), 'access'=>true, 'shortcut'=>false);
        $data[] = array('Type'=>'', 'id'=>'superUserGroup', 'Text'=>Prado::localize('Super User Group'), 'access'=>true, 'shortcut'=>false);

        if( ($param['appMode'] == 'saas' && $groupId == 1) || $param['appMode'] != 'saas' )
        {
            $data[] = array('Type'=>'', 'id'=>'configuration', 'Text'=>Prado::localize('Configuration'), 'access'=>true, 'shortcut'=>false);
        }

        $data[] = array('Type'=>Prado::localize('System'), 'id'=>'site', 'Text'=>Prado::localize('Site'), 'access'=>true, 'shortcut'=>false);
        $data[] = array('Type'=>'', 'id'=>'department', 'Text'=>Prado::localize('Department'), 'access'=>true, 'shortcut'=>false);
        $data[] = array('Type'=>'', 'id'=>'hardware', 'Text'=>Prado::localize('Hardware'), 'access'=>true, 'shortcut'=>false);
        $data[] = array('Type'=>'', 'id'=>'openTime', 'Text'=>Prado::localize('Opent time'), 'access'=>true, 'shortcut'=>false);
        $data[] = array('Type'=>'', 'id'=>'alarms', 'Text'=>Prado::localize('Alarms'), 'access'=>true, 'shortcut'=>false);
        $data[] = array('Type'=>'', 'id'=>'notification', 'Text'=>Prado::localize('Notification'), 'access'=>true, 'shortcut'=>false);

        if( ($param['appMode'] == 'saas' && $groupId == 1) || $param['appMode'] != 'saas' )
        {
            $data[] = array('Type'=>'', 'id'=>'service', 'Text'=>Prado::localize('Horux Service'), 'access'=>true, 'shortcut'=>false);
        }
        
        $data[] = array('Type'=>'', 'id'=>'status', 'Text'=>Prado::localize('Horux Status'), 'access'=>true, 'shortcut'=>false);

        $data[] = array('Type'=>Prado::localize('Access'), 'id'=>'user', 'Text'=>Prado::localize('User'), 'access'=>true, 'shortcut'=>false);
        $data[] = array('Type'=>'', 'id'=>'userGroup', 'Text'=>Prado::localize('User Group'), 'access'=>true, 'shortcut'=>false);
        $data[] = array('Type'=>'', 'id'=>'userWizard', 'Text'=>Prado::localize('User Wizard'), 'access'=>true, 'shortcut'=>false);
        $data[] = array('Type'=>'', 'id'=>'key', 'Text'=>Prado::localize('Key'), 'access'=>true, 'shortcut'=>false);
        $data[] = array('Type'=>'', 'id'=>'accessLevel', 'Text'=>Prado::localize('Access Level'), 'access'=>true, 'shortcut'=>false, 'shortcut'=>false);
        $data[] = array('Type'=>'', 'id'=>'nonWorkingDay', 'Text'=>Prado::localize('Non Working Day'), 'access'=>true, 'shortcut'=>false);


        if( ($param['appMode'] == 'saas' && $groupId == 1) || $param['appMode'] != 'saas' )
        {
            $data[] = array('Type'=>Prado::localize('Extensions'), 'id'=>'install_uninstall', 'Text'=>Prado::localize('Install/Uninstall'), 'access'=>true, 'shortcut'=>false);
            $data[] = array('Type'=>'', 'id'=>'devices', 'Text'=>Prado::localize('Devices Manager'), 'access'=>true, 'shortcut'=>false);
            $data[] = array('Type'=>'', 'id'=>'components', 'Text'=>Prado::localize('Component Manager'), 'access'=>true, 'shortcut'=>false);
            $data[] = array('Type'=>'', 'id'=>'template', 'Text'=>Prado::localize('Template Manager'), 'access'=>true, 'shortcut'=>false);
            $data[] = array('Type'=>'', 'id'=>'language', 'Text'=>Prado::localize('Language Manager'), 'access'=>true, 'shortcut'=>false);
        }
        else
            $data[] = array('Type'=>Prado::localize('Extensions'), 'id'=>'language', 'Text'=>Prado::localize('Language Manager'), 'access'=>true, 'shortcut'=>false);



        $data[] = array('Type'=>Prado::localize('Tools'), 'id'=>'guilog', 'Text'=>Prado::localize('Horux Gui Log'), 'access'=>true, 'shortcut'=>false);
        $data[] = array('Type'=>'', 'id'=>'globalCheckin', 'Text'=>Prado::localize('Global Checkin'), 'access'=>true, 'shortcut'=>false);
        $data[] = array('Type'=>'', 'id'=>'recycling', 'Text'=>Prado::localize('Recycling a Key'), 'access'=>true, 'shortcut'=>false);

        $data[] = array('Type'=>Prado::localize('Help'), 'id'=>'systemInfo', 'Text'=>Prado::localize('System Info'), 'access'=>true, 'shortcut'=>false);

        $data = $this->addComponent($data);

        $this->Session['dataPage'] = $data;

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
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":superAdmin",$this->superAdmin->getChecked(),PDO::PARAM_STR);
        $cmd->bindValue(":description",$this->description->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":defaultPage",$this->defaultPage->getSelectedValue(),PDO::PARAM_STR);

        $f1 = $this->dispUserLoggedIn->getChecked();
        $f2 = $this->dispLastAlarm->getChecked();
        $f3 = $this->dispLastTracking->getChecked();
        $f4 = $this->webservice->getChecked();

        $cmd->bindValue(":dispUserLoggedIn",$f1,PDO::PARAM_STR);
        $cmd->bindValue(":dispLastAlarm",$f2,PDO::PARAM_STR);
        $cmd->bindValue(":dispLastTracking",$f3,PDO::PARAM_STR);

        $cmd->bindValue(":webservice",$f4,PDO::PARAM_STR);


        if(!$cmd->execute()) return false;

        $this->lastId = $this->db->getLastInsertId();

        $this->insertNewPermissions($this->lastId);

        $this->log("Add the super user group :".$this->name->SafeText);

        return true;
    }

    protected function insertNewPermissions($lastId)
    {
        $data = $this->Session['dataPage'];

        foreach($data as $v)
        {
            switch($v['id'])
            {
                case "controlPanel":
                    $v['access'] ? $this->insertNewPermission($lastId, 'controlPanel.ControlPanel') : '';
                    break;
                case "superUser":
                    $v['access'] ? $this->insertNewPermission($lastId, 'superuser.userList', $v['shortcut']) : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'superuser.userAdd') : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'superuser.userMod') : '';
                    break;
                case "superUserGroup":
                    $v['access'] ? $this->insertNewPermission($lastId, 'superuser.userGroupList', $v['shortcut']) : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'superuser.userGroupAdd') : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'superuser.userGroupMod') : '';
                    break;
                case "configuration":
                    $v['access'] ? $this->insertNewPermission($lastId, 'configuration.config', $v['shortcut']) : '';
                    break;
                case "site":
                    $v['access'] ? $this->insertNewPermission($lastId, 'site.Site', $v['shortcut']) : '';
                    break;
                case "department":
                    $v['access'] ? $this->insertNewPermission($lastId, 'site.department', $v['shortcut']) : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'site.add', $v['shortcut']) : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'site.mod', $v['shortcut']) : '';
                    break;
                case "openTime":
                    $v['access'] ? $this->insertNewPermission($lastId, 'openTime.openTimeList', $v['shortcut']) : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'openTime.add') : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'openTime.mod') : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'openTime.attribute') : '';
                    break;
                case "hardware":
                    $v['access'] ? $this->insertNewPermission($lastId, 'hardware.HardwareList', $v['shortcut']) : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'hardware.HardwareAddList') : '';

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
                                $v['access'] ? $this->insertNewPermission($lastId, $perm->getValue()) : '';
                            }
                        }
                    }
                    break;
                case "alarms":
                    $v['access'] ? $this->insertNewPermission($lastId, 'system.Alarms', $v['shortcut']) : '';
                    break;
                case "notification":
                    $v['access'] ? $this->insertNewPermission($lastId, 'system.Notification', $v['shortcut']) : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'system.NotificationAdd') : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'system.NotificationMod') : '';
                    break;
                case "service":
                    $v['access'] ? $this->insertNewPermission($lastId, 'system.Service', $v['shortcut']) : '';
                    break;
                case "status":
                    $v['access'] ? $this->insertNewPermission($lastId, 'system.Status', $v['shortcut']) : '';
                    break;
                case "user":
                    $v['access'] ? $this->insertNewPermission($lastId, 'user.UserList', $v['shortcut']) : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'user.add') : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'user.mod') : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'user.attribution') : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'user.groups') : '';
                    break;
                case "userGroup":
                    $v['access'] ? $this->insertNewPermission($lastId, 'userGroup.UserGroupList', $v['shortcut']) : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'userGroup.add') : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'userGroup.mod') : '';
                    break;
                case "userWizard":
                    $v['access'] ? $this->insertNewPermission($lastId, 'user.UserWizzard',$v['shortcut']) : '';
                    break;
                case "key":
                    $v['access'] ? $this->insertNewPermission($lastId, 'key.KeyList', $v['shortcut']) : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'key.add') : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'key.mod') : '';
                    break;
                case "accessLevel":
                    $v['access'] ? $this->insertNewPermission($lastId, 'accessLevel.accessLevelList', $v['shortcut']) : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'accessLevel.add') : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'accessLevel.mod') : '';
                    break;
                case "nonWorkingDay":
                    $v['access'] ? $this->insertNewPermission($lastId, 'nonWorkingDay.nonWorkingDay', $v['shortcut']) : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'nonWorkingDay.add') : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'nonWorkingDay.mod') : '';
                    break;
                case "install_uninstall":
                    $v['access'] ? $this->insertNewPermission($lastId, 'installation.extensions', $v['shortcut']) : '';
                    break;
                case "devices":
                    $v['access'] ? $this->insertNewPermission($lastId, 'installation.devices', $v['shortcut']) : '';
                    break;
                case "components":
                    $v['access'] ? $this->insertNewPermission($lastId, 'installation.components', $v['shortcut']) : '';
                    $v['access'] ? $this->insertNewPermission($lastId, 'installation.componentconfig') : '';
                    break;
                case "template":
                    $v['access'] ? $this->insertNewPermission($lastId, 'installation.template', $v['shortcut']) : '';
                    break;
                case "language":
                    $v['access'] ? $this->insertNewPermission($lastId, 'installation.language', $v['shortcut']) : '';
                    break;
                case "recycling":
                    $v['access'] ? $this->insertNewPermission($lastId, 'key.recycling', $v['shortcut']) : '';
                    break;
                case "globalCheckin":
                    $v['access'] ? $this->insertNewPermission($lastId, 'tool.GlobalCheckin', $v['shortcut']) : '';
                    break;
                case "guilog":
                    $v['access'] ? $this->insertNewPermission($lastId, 'tool.GuiLog', $v['shortcut']) : '';
                    break;
                case "systemInfo":
                    $v['access'] ? $this->insertNewPermission($lastId, 'help.SystemInfo', $v['shortcut']) : '';
                    break;
                case "about":
                    $v['access'] ? $this->insertNewPermission($lastId, 'help.About', $v['shortcut']) : '';
                    break;
                default:
                    

                    /*add child permission*/

                    $path = './protected/pages/components/'.$v['composantname'];

                    $files = scandir($path);

                    $doc=new TXmlDocument();
                    $doc->loadFromFile($path.'/install.xml');
                    $permissions = $doc->getElementByTagName('permissions');
                    $permissions = $permissions->getElements();

                    $id = 0;
                    $addParent = null;
                    //! find the parent id of $v['id']
                    foreach($permissions as $perm)
                    {
                        if($perm->getValue() == $v['id'])
                        {
                            $id = $perm->getAttribute('id');
                            $addParent = $perm->getAttribute('add');
                        }
                    }

                    //add each permission where the parentid equal the id
                    foreach($permissions as $perm)
                    {
                        if( $id == $perm->getAttribute('parent') )
                        {
                            $v['access'] ? $this->insertNewPermission($lastId, $perm->getValue()) : '';
                        }
                    }

                    if($addParent === null || $addParent == "true")
                    {
                       $v['access'] ?  $this->insertNewPermission($lastId, $v['id'], $v['shortcut']) : '';
                    }

                 
                    break;
            }

        }
    }

    protected function insertNewPermission($lasetId, $page, $shortcut=false)
    {
        $cmd = $this->db->createCommand( SQL::SQL_ADD_PERMISSION );
        $cmd->bindValue(":page",$page,PDO::PARAM_STR);
        $cmd->bindValue(":id",$lasetId,PDO::PARAM_STR);
        $cmd->bindValue(":shortcut",$shortcut);

        $cmd->execute();
    }

    public function serverValidateName($sender, $param)
    {
        $cmd = $this->db->createCommand( SQL::SQL_IS_GROUP_EXIST );
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $data = $cmd->query();
        $data = $data->read();

        if($data['nb'] > 0)
        $param->IsValid=false;
    }


    public function onChangeAccess($sender, $param)
    {
        $data = $this->Session['dataPage'];

        unset($data[$sender->Text]);

        for($i=0; $i< count($data); $i++)
        {
            if($data[$i]['id'] == $sender->Text)
            {
                if($data[$i]['access'])
                {
                   $data[$i]['access'] = false;
                   $sender->ImageUrl = './themes/letux/images/menu/icon-16-access.png';
                }
                else
                {
                   $data[$i]['access'] = true;
                   $sender->ImageUrl = './themes/letux/images/menu/icon-16-checkin.png';
                }

                $i = count($data);
            }
        }


        $this->Session['dataPage'] = $data;
    }

    public function onChangeShortcut($sender, $param)
    {
        $data = $this->Session['dataPage'];

        unset($data[$sender->Text]);

        for($i=0; $i< count($data); $i++)
        {
            if($data[$i]['id'] == $sender->Text)
            {
                if($data[$i]['shortcut'])
                {
                   $data[$i]['shortcut'] = false;
                   $sender->ImageUrl = './themes/letux/images/menu/icon-16-cross.png';
                }
                else
                {
                   $data[$i]['shortcut'] = true;
                   $sender->ImageUrl = './themes/letux/images/menu/icon-16-checkin.png';
                }
            }
        }


        $this->Session['dataPage'] = $data;
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('superuser.userGroupList'));
    }

}
