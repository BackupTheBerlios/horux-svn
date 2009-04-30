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

class userGroupMod extends Page
{
	protected $lastId;	

	public function onLoad($param)
	{
        parent::onLoad($param);

        if(!$this->isPostBack)
        {
          	$userId=$this->Application->getUser()->getUserId();
    	  	$this->blockRecord('hr_superuser_group', $this->Request['id'], $userId);        	
        
          	$this->id->Value = $this->Request['id'];
          	$this->setData();        
        
        	$this->application->setGlobalState('dataPage',array());
        	
	        $this->DataGrid->DataSource=$this->Data;
    	    $this->DataGrid->dataBind();	
    	    
    	     $this->addComponent();	  
    	     
			$superAdmin = $this->Application->getUser()->getSuperAdmin();
			$param = $this->Application->getParameters();
	
			if($param['appMode'] == 'demo' && $superAdmin == 0)
			{
				$this->Apply->setEnabled(false);
				$this->Save->setEnabled(false);
			}     	     
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
	
	public function setData()
	{
        $cmd = $this->db->createCommand( SQL::SQL_GET_GROUP_BY_ID );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
          $data = $query->read();
          
          $this->name->Text = $data['name'];
          $this->description->Text =$data['description']; 
          $this->superAdmin->setChecked($data['superAdmin']); 
        } 
		
	}	

	public function getData()
	{
		$data = $this->application->getGlobalState('dataPage');

        $cmd = $this->db->createCommand( SQL::SQL_GET_PERM );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
			$perm = $query->readAll();
			
			foreach($perm as $p)
			{	
				switch($p['page'])
				{
					case 'controlPanel.ControlPanel':
						$data['controlPanel'] = array('id' => 'controlPanel','Type'=>'Horux', 'Text'=>Prado::localize('Control Panel') );
						break;
					case 'superuser.userList':
						$data['superUser'] = array('id' => 'superUser','Type'=>'Horux', 'Text'=>Prado::localize('Super User') );
						break;
					case 'superuser.userGroupList':
						$data['superUserGroup'] = array('id' => 'superUserGroup','Type'=>'Horux', 'Text'=>Prado::localize('Super User Group') );
						break;
					case 'configuration.config':
						$data['configuration'] = array('id' => 'configuration','Type'=>'Horux', 'Text'=>Prado::localize('Configuration') );
						break;
						
					case 'site.Site':
						$data['site'] = array('id' => 'site','Type'=>Prado::localize('System'), 'Text'=>Prado::localize('Site') );
						break;
					case 'hardware.HardwareList':
						$data['hardware'] = array('id' => 'hardware','Type'=>Prado::localize('System'), 'Text'=>Prado::localize('Hardware') );
						break;
					case 'openTime.openTimeList':
						$data['openTime'] = array('id' => 'openTime','Type'=>Prado::localize('System'), 'Text'=>Prado::localize('Open Time') );
						break;
					case 'system.Alarms':
						$data['alarms'] = array('id' => 'alarms','Type'=>Prado::localize('System'), 'Text'=>Prado::localize('Alarms') );
						break;
					case 'system.Notification':
						$data['notification'] = array('id' => 'notification','Type'=>Prado::localize('System'), 'Text'=>Prado::localize('Notification') );
						break;
					case 'system.Service':
						$data['service'] = array('id' => 'service','Type'=>Prado::localize('System'), 'Text'=>Prado::localize('Horux Service') );
						break;
					case 'system.Status':
						$data['status'] = array('id' => 'status','Type'=>Prado::localize('System'), 'Text'=>Prado::localize('Horux Status') );
						break;
					case 'user.UserList':
						$data['user'] = array('id' => 'user','Type'=>Prado::localize('Access'), 'Text'=>Prado::localize('User') );
						break;
					case 'userGroup.UserGroupList':
						$data['userGroup'] = array('id' => 'userGroup','Type'=>Prado::localize('Access'), 'Text'=>Prado::localize('User Group') );
						break;
					case 'user.UserWizzard':
						$data['userWizard'] = array('id' => 'userWizard','Type'=>Prado::localize('Access'), 'Text'=>Prado::localize('User Wizard') );
						break;
					case 'key.KeyList':
						$data['key'] = array('id' => 'key','Type'=>Prado::localize('Access'), 'Text'=>Prado::localize('Key') );
						break;
					case 'accessLevel.accessLevelList':
						$data['accessLevel'] = array('id' => 'accessLevel','Type'=>Prado::localize('Access'), 'Text'=>Prado::localize('Access Level') );
						break;
					case 'nonWorkingDay.nonWorkingDay':
						$data['nonWorkingDay'] = array('id' => 'nonWorkingDay','Type'=>Prado::localize('Access'), 'Text'=>Prado::localize('Non Working Day') );
						break;
						
					case 'installation.extensions':
						$data['install_uninstall'] = array('id' => 'install_uninstall','Type'=>Prado::localize('Extensions'), 'Text'=>Prado::localize('Install/Uninstall') );
						break;
					case 'installation.devices':
						$data['devices'] = array('id' => 'devices','Type'=>Prado::localize('Extensions'), 'Text'=>Prado::localize('Devices Manager') );
						break;
					case 'installation.components':
						$data['components'] = array('id' => 'components','Type'=>Prado::localize('Extensions'), 'Text'=>Prado::localize('Components Manager') );
						break;
					case 'installation.template':
						$data['template'] = array('id' => 'template','Type'=>Prado::localize('Extensions'), 'Text'=>Prado::localize('Template Manager') );
						break;
					case 'installation.language':
						$data['language'] = array('id' => 'language','Type'=>Prado::localize('Extensions'), 'Text'=>Prado::localize('Language Manager') );
						break;
					case 'tool.GlobalCheckin':
						$data['globalCheckin'] = array('id' => 'globalCheckin','Type'=>Prado::localize('Tools'), 'Text'=>Prado::localize('Global Checkin') );
						break;
					case 'tool.GuiLog':
						$data['guilog'] = array('id' => 'guilog','Type'=>Prado::localize('Tools'), 'Text'=>Prado::localize('Horux Gui Log') );
						break;
						
					case 'help.SystemInfo':
						$data['systemInfo'] = array('id' => 'systemInfo','Type'=>Prado::localize('Help'), 'Text'=>Prado::localize('System Info') );
						break;

					case 'help.About':
						$data['about'] = array('id' => 'about','Type'=>Prado::localize('Help'), 'Text'=>Prado::localize('About') );
						break;

					default:
						$comp = explode('.',$p['page']);

						if(file_exists('./protected/pages/components/'.$comp[1].'/install.xml'))
						{ 
 
							$doc=new TXmlDocument();
							$doc->loadFromFile('./protected/pages/components/'.$comp[1].'/install.xml');
							$name = $doc->getElementByTagName('name')->getValue();
							$data[$comp[1]] = array('id' => $comp[1],'Type'=>Prado::localize('Component'), 'Text'=>Prado::localize($name) );
						}
						break;
				}				
			}		          
        }        
		$this->application->setGlobalState('dataPage', $data);
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
      $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":superAdmin",$this->superAdmin->getChecked(),PDO::PARAM_STR);
      $cmd->bindParameter(":description",$this->description->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);    

      $cmd->execute();

	  $cmd=$this->db->createCommand(SQL::SQL_DELETE_GROUP_PERM);
	  $cmd->bindParameter(":id",$this->id->Value);
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
		$data = $this->application->getGlobalState('dataPage');
		
		foreach($data as $k=>$v)
		{
			switch($k)
			{
				case "controlPanel":
					$this->updatePermission($lastId, 'controlPanel.ControlPanel');
					break;	
				case "superUser":
					$this->updatePermission($lastId, 'superuser.userList');
					$this->updatePermission($lastId, 'superuser.userAdd');
					$this->updatePermission($lastId, 'superuser.userMod');
					break;	
				case "superUserGroup":
					$this->updatePermission($lastId, 'superuser.userGroupList');
					$this->updatePermission($lastId, 'superuser.userGroupAdd');
					$this->updatePermission($lastId, 'superuser.userGroupMod');
					break;	
				case "configuration":
					$this->updatePermission($lastId, 'configuration.config');
					break;	
				case "site":
					$this->updatePermission($lastId, 'site.Site');
					break;	
				case "openTime":
					$this->updatePermission($lastId, 'openTime.openTimeList');
					$this->updatePermission($lastId, 'openTime.add');
					$this->updatePermission($lastId, 'openTime.mod');
					$this->updatePermission($lastId, 'openTime.attribute');
					break;	
				case "hardware":
					$this->updatePermission($lastId, 'hardware.HardwareList');
					$this->updatePermission($lastId, 'hardware.HardwareAddList');

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
								$this->updatePermission($lastId, $perm->getValue());
							}
						}
					}

					break;	
				case "alarms":
					$this->updatePermission($lastId, 'system.Alarms');					
					break;	
				case "notification":
					$this->updatePermission($lastId, 'system.Notification');
					$this->updatePermission($lastId, 'system.NotificationMod');
					$this->updatePermission($lastId, 'system.NotificationAdd');
					break;
				case "service":
					$this->updatePermission($lastId, 'system.Service');					
					break;	
				case "status":
					$this->updatePermission($lastId, 'system.Status');					
					break;	
				case "user":
					$this->updatePermission($lastId, 'user.UserList');					
					$this->updatePermission($lastId, 'user.add');					
					$this->updatePermission($lastId, 'user.mod');					
					$this->updatePermission($lastId, 'user.attribution');					
					$this->updatePermission($lastId, 'user.groups');					
					break;	
				case "userGroup":
					$this->updatePermission($lastId, 'userGroup.UserGroupList');					
					$this->updatePermission($lastId, 'userGroup.add');					
					$this->updatePermission($lastId, 'userGroup.mod');					
					break;	
				case "userWizard":
					$this->updatePermission($lastId, 'user.UserWizzard');					
					break;	
				case "key":
					$this->updatePermission($lastId, 'key.KeyList');					
					$this->updatePermission($lastId, 'key.add');					
					$this->updatePermission($lastId, 'key.mod');					
					break;	
				case "accessLevel":
					$this->updatePermission($lastId, 'accessLevel.accessLevelList');					
					$this->updatePermission($lastId, 'accessLevel.add');					
					$this->updatePermission($lastId, 'accessLevel.mod');					
					break;	
				case "nonWorkingDay":
					$this->updatePermission($lastId, 'nonWorkingDay.nonWorkingDay');					
					$this->updatePermission($lastId, 'nonWorkingDay.add');					
					$this->updatePermission($lastId, 'nonWorkingDay.mod');					
					break;	
				case "install_uninstall":
					$this->updatePermission($lastId, 'installation.extensions');					
					break;	
				case "devices":
					$this->updatePermission($lastId, 'installation.devices');					
					break;	
				case "components":
					$this->updatePermission($lastId, 'installation.components');					
					$this->updatePermission($lastId, 'installation.componentconfig');					
					break;	
				case "template":
					$this->updatePermission($lastId, 'installation.template');					
					break;	
				case "language":
					$this->updatePermission($lastId, 'installation.language');					
					break;	
				case "globalCheckin":
					$this->updatePermission($lastId, 'tool.GlobalCheckin');					
					break;	
				case "guilog":
					$this->updatePermission($lastId, 'tool.GuiLog');
					break;
				case "systemInfo":
					$this->updatePermission($lastId, 'help.SystemInfo');
					break;	
				case "about":				
					$this->updatePermission($lastId, 'help.About');					
					break;	
				default:
					$doc=new TXmlDocument();
					$doc->loadFromFile('./protected/pages/components/'.$k.'/install.xml');
					$permissions = $doc->getElementByTagName('permissions');
					$permissions = $permissions->getElements();
					foreach($permissions as $perm)
					{
						$this->updatePermission($lastId, $perm->getValue());
					}
					break;		
			}
		}
	}
	
	protected function updatePermission($lasetId, $page)
	{
      $cmd = $this->db->createCommand( SQL::SQL_ADD_PERMISSION );
      $cmd->bindParameter(":page",$page,PDO::PARAM_STR);
      $cmd->bindParameter(":id",$lasetId,PDO::PARAM_STR);
      
      $cmd->execute();		
	}
	
	public function serverValidateName($sender, $param)
	{
      $cmd = $this->db->createCommand( SQL::SQL_IS_GROUP_EXIST2 );
      $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":id",$this->id->Value);
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
	
	
	
}
