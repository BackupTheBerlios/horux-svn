<?php

Prado::using('horux.pages.superuser.sql');

class userGroupMod extends Page
{
    protected $lastId;

    public function onLoad($param)
    {
        parent::onLoad($param);

        $groupId = $this->Application->getUser()->getGroupID();
        $param = $this->Application->getParameters();
        
        if($this->Request['id'] == 1 && $groupId!=1 && $param['appMode'] == 'saas')
        {
            $pBack = array('koMsg'=>Prado::localize("You don't have the right to modify this users group"));
            $this->Response->redirect($this->Service->constructUrl('superuser.userGroupList',$pBack));
        }

        if(!$this->isPostBack)
        {
            $userId=$this->Application->getUser()->getUserId();
            $this->blockRecord('hr_superuser_group', $this->Request['id'], $userId);

            $this->id->Value = $this->Request['id'];

            $this->defaultPage->DataTextField='pagename';
            $this->defaultPage->DataValueField='page';
            $this->defaultPage->DataSource=$this->DataPage;
            $this->defaultPage->dataBind();

            $this->setData();

            $this->Session['dataPage'] = array();

            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();

            $superAdmin = $this->Application->getUser()->getSuperAdmin();
            $param = $this->Application->getParameters();

            if($param['appMode'] == 'demo' && $superAdmin == 0)
            {
                $this->tbb->apply->setEnabled(false);
                $this->tbb->Save->setEnabled(false);
            }

            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();


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
                    $data[] = array('Type'=>Prado::localize('Components'), 'id'=>$data2['page'], 'Text'=>Prado::localize($name->getValue(), array(),$installName ), 'Text2'=>'', 'access'=>$this->isAccess($data2['page']), 'composantname'=>$installName, 'shortcut'=>$this->isShortcut($data2['page']));
                    $isComponentHasOne = true;
                }
                else
                    $data[] = array('Type'=>'', 'id'=>$data2['page'], 'Text'=>Prado::localize($name->getValue(), array(),$installName ), 'Text2'=>'', 'access'=>$this->isAccess($data2['page']), 'composantname'=>$installName, 'shortcut'=>$this->isShortcut($data2['page']));


                $cmd = $this->db->createCommand("SELECT * FROM hr_install AS i LEFT JOIN hr_component as c ON c.id_install=i.id WHERE i.type='component' AND c.parentmenu=".$data2['id']." AND c.parentmenu>0 AND i.id=".$d1['id']);
                $data2 = $cmd->query();
                $data2 = $data2->readAll();

                foreach($data2 as $d2)
                {
                    $data[] = array('Type'=>'', 'id'=>$d2['page'], 'Text'=>'', 'Text2'=>Prado::localize($d2['menuname'], array(),$installName ), 'access'=>$this->isAccess($d2['page']), 'composantname'=>$installName, 'shortcut'=>$this->isShortcut($d2['page']));
                }

            }

        }

        return $data;
    }

    public function setData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_GET_GROUP_BY_ID );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();

            $this->name->Text = $data['name'];
            $this->description->Text =$data['description'];
            $this->superAdmin->setChecked($data['superAdmin']);

            $this->dispUserLoggedIn->setChecked($data['dispUserLoggedIn']);
            $this->dispLastAlarm->setChecked($data['dispLastAlarm']);
            $this->dispLastTracking->setChecked($data['dispLastTracking']);

            $this->webservice->setChecked($data['webservice']);

            if($data['defaultPage'] != '')
                $this->defaultPage->setSelectedValue($data['defaultPage']);
            else
                $this->defaultPage->setSelectedValue('controlPanel.ControlPanel');
        }

    }

    protected function isAccess($page)
    {
        $cmd = $this->db->createCommand( "SELECT COUNT(*) AS n FROM hr_gui_permissions WHERE page=:page AND value=:id" );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $cmd->bindValue(":page",$page, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            return $data['n'] > 0;
        }
    }

    protected function isShortcut($page)
    {
        $cmd = $this->db->createCommand( "SELECT COUNT(*) AS n FROM hr_gui_permissions WHERE page=:page AND value=:id AND shortcut=1" );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $cmd->bindValue(":page",$page, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            return $data['n'] > 0;
        }
    }

    public function getData()
    {

        $param = $this->Application->getParameters();
        $groupId = $this->Application->getUser()->getGroupID();


        $data[] = array('Type'=>'Horux', 'id'=>'superUser', 'Text'=>Prado::localize('Super User'), 'Text2'=>'','access'=>$this->isAccess('superuser.userList'), 'shortcut'=>$this->isShortcut('superuser.userList'));
        $data[] = array('Type'=>'', 'id'=>'superUserGroup', 'Text'=>Prado::localize('Super User Group'), 'Text2'=>'', 'access'=>$this->isAccess('superuser.userGroupList'), 'shortcut'=>$this->isShortcut('superuser.userGroupList'));

        if( ($param['appMode'] == 'saas' && $groupId == 1) || $param['appMode'] != 'saas' )
        {
            $data[] = array('Type'=>'', 'id'=>'configuration', 'Text'=>Prado::localize('Configuration'), 'Text2'=>'', 'access'=>$this->isAccess('configuration.config'), 'shortcut'=>$this->isShortcut('configuration.config'));
        }
        
        $data[] = array('Type'=>Prado::localize('System'), 'id'=>'site', 'Text'=>Prado::localize('Site'), 'Text2'=>'', 'access'=>$this->isAccess('site.Site'), 'shortcut'=>$this->isShortcut('site.Site'));
        $data[] = array('Type'=>'', 'id'=>'department', 'Text'=>Prado::localize('Department'), 'Text2'=>'', 'access'=>$this->isAccess('site.department'), 'shortcut'=>$this->isShortcut('site.department'));
        $data[] = array('Type'=>'', 'id'=>'horuxController', 'Text'=>Prado::localize('Horux Controller'), 'Text2'=>'', 'access'=>$this->isAccess('horuxController.horuxController'), 'shortcut'=>$this->isShortcut('horuxController.horuxController'));
        $data[] = array('Type'=>'', 'id'=>'hardware', 'Text'=>Prado::localize('Hardware'), 'Text2'=>'', 'access'=>$this->isAccess('hardware.HardwareList'), 'shortcut'=>$this->isShortcut('hardware.HardwareList'));
        $data[] = array('Type'=>'', 'id'=>'openTime', 'Text'=>Prado::localize('Opent time'), 'Text2'=>'', 'access'=>$this->isAccess('openTime.openTimeList'), 'shortcut'=>$this->isShortcut('openTime.openTimeList'));
        $data[] = array('Type'=>'', 'id'=>'alarms', 'Text'=>Prado::localize('Alarms'), 'Text2'=>'', 'access'=>$this->isAccess('system.Alarms'), 'shortcut'=>$this->isShortcut('system.Alarms'));
        $data[] = array('Type'=>'', 'id'=>'notification', 'Text'=>Prado::localize('Notification'), 'Text2'=>'', 'access'=>$this->isAccess('system.Notification'), 'shortcut'=>$this->isShortcut('system.Notification'));
        if( ($param['appMode'] == 'saas' && $groupId == 1) || $param['appMode'] != 'saas' )
        {
            $data[] = array('Type'=>'', 'id'=>'service', 'Text'=>Prado::localize('Horux Service'), 'Text2'=>'', 'access'=>$this->isAccess('system.Service'), 'shortcut'=>$this->isShortcut('system.Service'));
        }
        $data[] = array('Type'=>'', 'id'=>'status', 'Text'=>Prado::localize('Horux Status'), 'Text2'=>'', 'access'=>$this->isAccess('system.Status'), 'shortcut'=>$this->isShortcut('system.Status'));

        $data[] = array('Type'=>Prado::localize('Access'), 'id'=>'user', 'Text'=>Prado::localize('User'), 'Text2'=>'', 'access'=>$this->isAccess('user.UserList'), 'shortcut'=>$this->isShortcut('user.UserList'));
        $data[] = array('Type'=>'', 'id'=>'userGroup', 'Text'=>Prado::localize('User Group'), 'Text2'=>'', 'access'=>$this->isAccess('userGroup.UserGroupList'), 'shortcut'=>$this->isShortcut('userGroup.UserGroupList'));
        $data[] = array('Type'=>'', 'id'=>'userWizard', 'Text'=>Prado::localize('User Wizard'), 'Text2'=>'', 'access'=>$this->isAccess('user.UserWizzard'), 'shortcut'=>$this->isShortcut('user.UserWizzard'));
        $data[] = array('Type'=>'', 'id'=>'key', 'Text'=>Prado::localize('Key'), 'Text2'=>'', 'access'=>$this->isAccess('key.KeyList'), 'shortcut'=>$this->isShortcut('key.KeyList'));
        $data[] = array('Type'=>'', 'id'=>'accessLevel', 'Text'=>Prado::localize('Access Level'), 'Text2'=>'', 'access'=>$this->isAccess('accessLevel.accessLevelList'), 'shortcut'=>$this->isShortcut('accessLevel.accessLevelList'));
        $data[] = array('Type'=>'', 'id'=>'nonWorkingDay', 'Text'=>Prado::localize('Non Working Day'), 'Text2'=>'', 'access'=>$this->isAccess('nonWorkingDay.nonWorkingDay'), 'shortcut'=>$this->isShortcut('nonWorkingDay.nonWorkingDay'));


        if( ($param['appMode'] == 'saas' && $groupId == 1) || $param['appMode'] != 'saas' )
        {
            $data[] = array('Type'=>Prado::localize('Extensions'), 'id'=>'install_uninstall', 'Text2'=>'', 'Text'=>Prado::localize('Install/Uninstall'), 'access'=>$this->isAccess('installation.extensions'), 'shortcut'=>$this->isShortcut('installation.extensions'));
            $data[] = array('Type'=>'', 'id'=>'devices', 'Text'=>Prado::localize('Devices Manager'), 'Text2'=>'', 'access'=>$this->isAccess('installation.devices'), 'shortcut'=>$this->isShortcut('installation.devices'));
            $data[] = array('Type'=>'', 'id'=>'components', 'Text'=>Prado::localize('Component Manager'), 'Text2'=>'', 'access'=>$this->isAccess('installation.components'), 'shortcut'=>$this->isShortcut('installation.components'));
            $data[] = array('Type'=>'', 'id'=>'template', 'Text'=>Prado::localize('Template Manager'), 'Text2'=>'', 'access'=>$this->isAccess('installation.template'), 'shortcut'=>$this->isShortcut('installation.template'));
            $data[] = array('Type'=>'', 'id'=>'language', 'Text'=>Prado::localize('Language Manager'), 'Text2'=>'', 'access'=>$this->isAccess('installation.language'), 'shortcut'=>$this->isShortcut('installation.language'));
        }
        else
            $data[] = array('Type'=>Prado::localize('Extensions'), 'id'=>'language', 'Text'=>Prado::localize('Language Manager'), 'Text2'=>'', 'access'=>$this->isAccess('installation.language'), 'shortcut'=>$this->isShortcut('installation.language'));


        $data[] = array('Type'=>Prado::localize('Tools'), 'id'=>'guilog', 'Text'=>Prado::localize('Horux Gui Log'), 'Text2'=>'', 'access'=>$this->isAccess('tool.GuiLog'), 'shortcut'=>$this->isShortcut('tool.GuiLog'));
        $data[] = array('Type'=>'', 'id'=>'globalCheckin', 'Text'=>Prado::localize('Global Checkin'), 'Text2'=>'', 'access'=>$this->isAccess('tool.GlobalCheckin'), 'shortcut'=>$this->isShortcut('tool.GlobalCheckin'));
        $data[] = array('Type'=>'', 'id'=>'recycling', 'Text'=>Prado::localize('Recycling a Key'), 'Text2'=>'', 'access'=>$this->isAccess('key.recycling'), 'shortcut'=>$this->isShortcut('key.recycling'));
        $data[] = array('Type'=>'', 'id'=>'update', 'Text'=>Prado::localize('Horux Update'), 'Text2'=>'', 'access'=>$this->isAccess('update.Update'), 'shortcut'=>$this->isShortcut('update.Update'));

        $data[] = array('Type'=>Prado::localize('Help'), 'id'=>'systemInfo', 'Text'=>Prado::localize('System Info'), 'Text2'=>'', 'access'=>$this->isAccess('help.SystemInfo'), 'shortcut'=>$this->isShortcut('help.SystemInfo'));

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
                $id = $this->id->Value;
                $pBack = array('okMsg'=>Prado::localize('The group was modified successfully'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('superuser.userGroupMod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The group was not modified'));
                $this->Response->redirect($this->Service->constructUrl('superuser.userGroupMod',$pBack));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The group was modified successfully'));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The group was not modified'));
            }

            $this->application->clearGlobalState('dataPage');
            $this->blockRecord('hr_superuser_group', $this->id->Value, 0);
            $this->Response->redirect($this->Service->constructUrl('superuser.userGroupList',$pBack));
        }
    }

    public function onCancel($sender, $param)
    {
        $this->blockRecord('hr_superuser_group', $this->id->Value, 0);
        $this->Response->redirect($this->Service->constructUrl('superuser.userGroupList'));
    }

    public function saveData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_UPDATE_USER_GROUP );
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":superAdmin",$this->superAdmin->getChecked(),PDO::PARAM_STR);
        $cmd->bindValue(":description",$this->description->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);

        $f1 = $this->dispUserLoggedIn->getChecked();
        $f2 = $this->dispLastAlarm->getChecked();
        $f3 = $this->dispLastTracking->getChecked();
        $f4 = $this->webservice->getChecked();


        $cmd->bindValue(":dispUserLoggedIn",$f1,PDO::PARAM_STR);
        $cmd->bindValue(":dispLastAlarm",$f2,PDO::PARAM_STR);
        $cmd->bindValue(":dispLastTracking",$f3,PDO::PARAM_STR);

        $cmd->bindValue(":webservice",$f4,PDO::PARAM_STR);
        $cmd->bindValue(":defaultPage",$this->defaultPage->getSelectedValue(),PDO::PARAM_STR);



        $cmd->execute();

        $cmd=$this->db->createCommand(SQL::SQL_DELETE_GROUP_PERM);
        $cmd->bindValue(":id",$this->id->Value);
        $cmd->execute();

        $this->updatePermissions($this->id->Value);

        if($this->Application->getUser()->getGroupID() == $this->id->Value)
        {
            $this->Application->getUser()->setSuperAdmin($this->superAdmin->getChecked());

            $this->Application->getModule('Auth')->updateSessionUser($this->Application->getUser());
        }

        $this->log("Modify the super user group :".$this->name->SafeText);
        return true;
    }

    protected function updatePermissions($lastId)
    {
        $data = $this->Session['dataPage'];

        foreach($data as $v)
        {
            switch($v['id'])
            {
                case "controlPanel":
                    $v['access'] ? $this->updatePermission($lastId, 'controlPanel.ControlPanel') : '';
                    break;
                case "superUser":
                    $v['access'] ? $this->updatePermission($lastId, 'superuser.userList', $v['shortcut']) : '';
                    $v['access'] ? $this->updatePermission($lastId, 'superuser.userAdd') : '';
                    $v['access'] ?$this->updatePermission($lastId, 'superuser.userMod') : '';
                    break;
                case "superUserGroup":
                    $v['access'] ?$this->updatePermission($lastId, 'superuser.userGroupList', $v['shortcut']) : '';
                    $v['access'] ?$this->updatePermission($lastId, 'superuser.userGroupAdd') : '';
                    $v['access'] ?$this->updatePermission($lastId, 'superuser.userGroupMod') : '';
                    break;
                case "configuration":
                    $v['access'] ?$this->updatePermission($lastId, 'configuration.config', $v['shortcut']) : '';
                    break;
                case "site":
                    $v['access'] ? $this->updatePermission($lastId, 'site.Site', $v['shortcut']) : '';
                    break;
                case "department":
                    $v['access'] ? $this->updatePermission($lastId, 'site.department', $v['shortcut']) : '';
                    $v['access'] ? $this->updatePermission($lastId, 'site.add', $v['shortcut']) : '';
                    $v['access'] ? $this->updatePermission($lastId, 'site.mod', $v['shortcut']) : '';
                    break;
                case "openTime":
                    $v['access'] ? $this->updatePermission($lastId, 'openTime.openTimeList', $v['shortcut']) : '';
                    $v['access'] ? $this->updatePermission($lastId, 'openTime.add') : '';
                    $v['access'] ? $this->updatePermission($lastId, 'openTime.mod') : '';
                    $v['access'] ? $this->updatePermission($lastId, 'openTime.attribute') : '';
                    break;
                case "horuxController":
                    $v['access'] ? $this->updatePermission($lastId, 'horuxController.horuxController', $v['shortcut']) : '';
                    $v['access'] ? $this->updatePermission($lastId, 'horuxController.add') : '';
                    $v['access'] ? $this->updatePermission($lastId, 'horuxController.mod') : '';
                    break;

                case "hardware":
                    $v['access'] ? $this->updatePermission($lastId, 'hardware.HardwareList', $v['shortcut']) : '';
                    $v['access'] ? $this->updatePermission($lastId, 'hardware.HardwareAddList') : '';

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
                               $v['access'] ?  $this->updatePermission($lastId, $perm->getValue()) : '';
                            }
                        }
                    }

                    break;
                case "alarms":
                    $v['access'] ? $this->updatePermission($lastId, 'system.Alarms', $v['shortcut']) : '';
                    break;
                case "notification":
                    $v['access'] ? $this->updatePermission($lastId, 'system.Notification', $v['shortcut']) : '';
                    $v['access'] ? $this->updatePermission($lastId, 'system.NotificationMod') : '';
                    $v['access'] ? $this->updatePermission($lastId, 'system.NotificationAdd') : '';
                    break;
                case "service":
                    $v['access'] ? $this->updatePermission($lastId, 'system.Service', $v['shortcut']) : '';
                    break;
                case "status":
                    $v['access'] ? $this->updatePermission($lastId, 'system.Status', $v['shortcut']) : '';
                    break;
                case "user":
                    $v['access'] ? $this->updatePermission($lastId, 'user.UserList', $v['shortcut']) : '';
                    $v['access'] ? $this->updatePermission($lastId, 'user.add') : '';
                    $v['access'] ? $this->updatePermission($lastId, 'user.mod') : '';
                    $v['access'] ? $this->updatePermission($lastId, 'user.attribution') : '';
                    $v['access'] ? $this->updatePermission($lastId, 'user.groups') : '';
                    break;
                case "userGroup":
                    $v['access'] ? $this->updatePermission($lastId, 'userGroup.UserGroupList', $v['shortcut']) : '';
                    $v['access'] ? $this->updatePermission($lastId, 'userGroup.add') : '';
                    $v['access'] ? $this->updatePermission($lastId, 'userGroup.mod') : '';
                    break;
                case "userWizard":
                    $v['access'] ? $this->updatePermission($lastId, 'user.UserWizzard', $v['shortcut']) : '';
                    break;
                case "key":
                    $v['access'] ? $this->updatePermission($lastId, 'key.KeyList', $v['shortcut']) : '';
                    $v['access'] ? $this->updatePermission($lastId, 'key.add') : '';
                    $v['access'] ? $this->updatePermission($lastId, 'key.mod') : '';
                    break;
                case "accessLevel":
                    $v['access'] ? $this->updatePermission($lastId, 'accessLevel.accessLevelList', $v['shortcut']) : '';
                    $v['access'] ? $this->updatePermission($lastId, 'accessLevel.add') : '';
                    $v['access'] ? $this->updatePermission($lastId, 'accessLevel.mod') : '';
                    break;
                case "nonWorkingDay":
                    $v['access'] ? $this->updatePermission($lastId, 'nonWorkingDay.nonWorkingDay', $v['shortcut']) : '';
                    $v['access'] ? $this->updatePermission($lastId, 'nonWorkingDay.add') : '';
                    $v['access'] ? $this->updatePermission($lastId, 'nonWorkingDay.mod') : '';
                    break;
                case "install_uninstall":
                    $v['access'] ? $this->updatePermission($lastId, 'installation.extensions', $v['shortcut']) : '';
                    break;
                case "devices":
                    $v['access'] ? $this->updatePermission($lastId, 'installation.devices', $v['shortcut']) : '';
                    break;
                case "components":
                    $v['access'] ? $this->updatePermission($lastId, 'installation.components', $v['shortcut']) : '';
                    $v['access'] ? $this->updatePermission($lastId, 'installation.componentconfig') : '';
                    break;
                case "template":
                    $v['access'] ? $this->updatePermission($lastId, 'installation.template', $v['shortcut']) : '';
                    break;
                case "language":
                    $v['access'] ? $this->updatePermission($lastId, 'installation.language', $v['shortcut']) : '';
                    break;
                case "globalCheckin":
                    $v['access'] ? $this->updatePermission($lastId, 'tool.GlobalCheckin', $v['shortcut']) : '';
                    break;
                case "guilog":
                    $v['access'] ? $this->updatePermission($lastId, 'tool.GuiLog', $v['shortcut']) : '';
                    break;
                case "update":
                    $v['access'] ? $this->updatePermission($lastId, 'update.Update', $v['shortcut']) : '';
                    break;
                case "recycling":
                    $v['access'] ? $this->updatePermission($lastId, 'key.recycling', $v['shortcut']) : '';
                    break;
                case "systemInfo":
                    $v['access'] ? $this->updatePermission($lastId, 'help.SystemInfo', $v['shortcut']) : '';
                    break;
                case "about":
                    $v['access'] ? $this->updatePermission($lastId, 'help.About', $v['shortcut']) : '';
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
                            $v['access'] ? $this->updatePermission($lastId, $perm->getValue()) : '';
                        }
                    }

                    if($addParent === null || $addParent == "true")
                    {
                        $v['access'] ? $this->updatePermission($lastId, $v['id'], $v['shortcut']) : '';
                    }


                    break;
            }
        }
    }

    protected function updatePermission($lasetId, $page, $shortcut=false)
    {
        $cmd = $this->db->createCommand( SQL::SQL_ADD_PERMISSION );
        $cmd->bindValue(":page",$page,PDO::PARAM_STR);
        $cmd->bindValue(":id",$lasetId,PDO::PARAM_STR);
        $cmd->bindValue(":shortcut",$shortcut);

        $cmd->execute();
    }

    public function serverValidateName($sender, $param)
    {
        $cmd = $this->db->createCommand( SQL::SQL_IS_GROUP_EXIST2 );
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->id->Value);
        $data = $cmd->query();
        $data = $data->read();


        if($data['nb'] > 0)
        $param->IsValid=false;
    }

    public function onChangeAccess($sender, $param)
    {
        $data = $this->Session['dataPage'];

        unset($data[$sender->Text]);

        $c = count($data);

        for($i=0; $i< $c; $i++)
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

                $i = $c;
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

}
