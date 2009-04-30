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

Prado::using('Application.portlets.Portlet');

class Menu
{
	protected $items = array();
	
	public function addMenuItem(MenuItem $item)
	{
		$this->items[] = $item;
	}

	public function render()
	{
		$html = '[';
		
		foreach($this->items as $item)
		{
                  if($item->countVisible() > 0)
                    $html .= $item->render();
		}
		
		$html .= '];';
		
		return $html;
	}
}

class MenuItem
{	
	protected $isVisible = true;
	protected $icon;
	protected $title;
	protected $link;
	protected $subItems = array();

	function __construct( $icon, $title, $link, $visible=true)
	{
		$this->icon = $icon;
		$this->title = $title;
		$this->link = $link;
		$this->isVisible = $visible;
	}

        public function countVisible($index = 0, $countNextSplit=true)
        {
          $count = 0;
          
          for($i=$index; $i<count($this->subItems); $i++)
          {
            if($this->subItems[$i] != ',_cmSplit')
            {
              if($this->subItems[$i]->isVisible)
                $count++;
            }
            else
              if(!$countNextSplit)
                return $count;
          }
          return $count;
        }

	public function addMenuItem(MenuItem $item)
	{
		$this->subItems[] = $item;
	}
	
	public function setVisible($flag)
	{
		$this->isVisible = $flag;
	}

	public function addSplit($access = true)
	{
		//if($access)
                  $this->subItems[] = ',_cmSplit';
	}

	public function render()
	{
            if(!$this->isVisible) return '';
            
            $html = ',[';
            $html .= $this->icon.",";
            $html .= "'".addslashes($this->title)."',";
            $html .= "'".$this->link."',";
            $html .= "'_self',null";
            
            
            for($i=0; $i < count($this->subItems); $i++)
            {
              if($this->subItems[$i] == ',_cmSplit')
              {
                if($this->countVisible($i+2, false)>0)
                {
                  $html .= $this->subItems[$i];
                }
              }
              else
                $html .= $this->subItems[$i]->render();
            }

            $html .= ']';
            
            return $html;
	}

}

class HeaderBox extends Portlet
{
    protected $systemMenuCount = 0;
    protected $accessMenuCount = 0;
    protected $componentMenuCount = 0;
    protected $extensionMenuCount = 0;
    protected $toolMenuCount = 0;

    public function onInit($param)
    {
   	  parent::onInit($param);
	  $this->logout->Text = Prado::localize('Logout',array(), "messages");
	  $this->generateMenu();

  	  $user = $this->application->getUser();

	  if($user)
	  {
	
      	$p = $this->getService()->getRequestedPagePath();

	  	if( $user->getIsGuest() || $p == 'install.install' || $p == 'login.login' )
        {
     		$this->setVisible(false);            
        }
	  }
      else
     	$this->setVisible(false);
    }

    public function generateMenuDisabled()
    {
        $menu = array();
        $menu[] = 'Horux';
        if($this->systemMenuCount>0)
            $menu[] = Prado::localize('System',array(), "messages");
        if($this->accessMenuCount>0)
            $menu[] = Prado::localize('Access',array(), "messages");
        if($this->componentMenuCount>0)
            $menu[] = Prado::localize('Components',array(), "messages");
        if($this->extensionMenuCount>0)
            $menu[] = Prado::localize('Extensions',array(), "messages");
        if($this->toolMenuCount>0)
            $menu[] = Prado::localize('Tools',array(), "messages");

        $menu[] = Prado::localize('Info',array(), "messages");

        $html = '<div class="menu_disabled">';
        foreach($menu as $m)
        {
            $html .= '<div class="menu_disabled_item">'.$m.'</div>';
        }
        $html .= '</div>';
        return $html;
    }

	public function generateMenu()
	{
		$menu = new Menu;
		
		/******************************************
		 * Horux menu
		 */
		$horux = new MenuItem('null', 'Horux', '#');

        $horux->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-controlPanel.png\" />'",
                                          Prado::localize('Control Panel',array(), "messages"),
                                          $this->Service->constructUrl('controlPanel.ControlPanel'),
                                          /*$this->isAccess('controlPanel.ControlPanel')*/1
                                          ));

        $horux->addSplit();

        $horux->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-superUser.png\" />'",
                                          Prado::localize('Super User',array(), "messages"),
                                          $this->Service->constructUrl('superuser.userList'),
                                          $this->isAccess('superuser.userList')
                                          ));
        $horux->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-superGroupUser.png\" />'",
                                          Prado::localize('Super User Group',array(), "messages"),
                                          $this->Service->constructUrl('superuser.userGroupList'),
                                          $this->isAccess('superuser.userGroupList')
                                          ));
        $horux->addSplit();

        $horux->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-configure.png\" />'",
                                          Prado::localize('Configuration',array(), "messages"),
                                          $this->Service->constructUrl('configuration.config'),
                                          $this->isAccess('configuration.config')
                                          ));

		/******************************************
		 * System menu
		 */
		$system = new MenuItem('null', Prado::localize('System',array(), "messages"), '#');

		$system->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-site.png\" />'", 
										  Prado::localize('Site',array(), "messages"), 
										  $this->Service->constructUrl('site.Site'),
										  $this->isAccess('site.Site')
										  ));
        if($this->isAccess('site.Site'))
            $this->systemMenuCount++;

		$system->addSplit();


		$system->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-hardware.png\" />'", 
										  Prado::localize('Hardware',array(), "messages"), 
										  $this->Service->constructUrl('hardware.HardwareList'),
										  $this->isAccess('hardware.HardwareList')
										  ));
        if($this->isAccess('hardware.HardwareList'))
            $this->systemMenuCount++;

		$system->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-accessLevel.png\" />'", 
										  Prado::localize('Open time',array(), "messages"), 
										  $this->Service->constructUrl('openTime.openTimeList'),
										  $this->isAccess('openTime.openTimeList')
										  ));
        if($this->isAccess('openTime.openTimeList'))
            $this->systemMenuCount++;

		$system->addSplit();


		$system->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-notification.png\" />'",
										  Prado::localize('Notification',array(), "messages"),
										  $this->Service->constructUrl('system.Notification'),
										  $this->isAccess('system.Notification')
										  ));
        if($this->isAccess('system.Notification'))
            $this->systemMenuCount++;

		
		$system->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-alarm.png\" />'", 
										  Prado::localize('Alarms',array(), "messages"), 
										  $this->Service->constructUrl('system.Alarms'),
										  $this->isAccess('system.Alarms')
										  ));
        if($this->isAccess('system.Alarms'))
            $this->systemMenuCount++;

        $system->addSplit();

		$system->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-service.png\" />'", 
										  Prado::localize('Horux Service',array(), "messages"), 
										  $this->Service->constructUrl('system.Service'),
										  $this->isAccess('system.Service')
										  ));
        if($this->isAccess('system.Service'))
            $this->systemMenuCount++;


		$system->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-standalone.png\" />'", 
										  Prado::localize('Standalone',array(), "messages"), 
										  $this->Service->constructUrl('system.Standalone'),
										  $this->isAccess('system.Standalone')
										  ));
        if($this->isAccess('system.Standalone'))
            $this->systemMenuCount++;


		$system->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-systemStatus.png\" />'", 
										  Prado::localize('System Status',array(), "messages"), 
										  $this->Service->constructUrl('system.Status'),
										  $this->isAccess('system.Status')
										  ));
        if($this->isAccess('system.Status'))
            $this->systemMenuCount++;


		/******************************************
		 * access menu
		 */		
		$access = new MenuItem('null', Prado::localize('Access',array(), "messages"), '#');

		$access->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-user2.png\" />'", 
										  Prado::localize('User',array(), "messages"), 
										  $this->Service->constructUrl('user.UserList'),
										  $this->isAccess('user.UserList')
										  ));

        if($this->isAccess('user.UserList'))
            $this->accessMenuCount++;

		$access->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-userGroup.png\" />'", 
										  Prado::localize('User group',array(), "messages"), 
										  $this->Service->constructUrl('userGroup.UserGroupList'),
										  $this->isAccess('userGroup.UserGroupList')
										  ));
        if($this->isAccess('userGroup.UserGroupList'))
            $this->accessMenuCount++;


		$access->addSplit();

		$access->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-key.png\" />'", 
										  Prado::localize('Key',array(), "messages"), 
										  $this->Service->constructUrl('key.KeyList'),
										  $this->isAccess('key.KeyList')
										  ));

        if($this->isAccess('key.KeyList'))
            $this->accessMenuCount++;

		$access->addSplit();

		$access->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-accessLevel.png\" />'", 
										  Prado::localize('Access Level',array(), "messages"), 
										  $this->Service->constructUrl('accessLevel.accessLevelList'),
										  $this->isAccess('accessLevel.accessLevelList')
										  ));
        if($this->isAccess('accessLevel.accessLevelList'))
            $this->accessMenuCount++;


		$access->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-nonWorkingDay.png\" />'", 
										  Prado::localize('Non Working Days',array(), "messages"), 
										  $this->Service->constructUrl('nonWorkingDay.nonWorkingDay'),
										  $this->isAccess('nonWorkingDay.nonWorkingDay')
										  ));
        if($this->isAccess('nonWorkingDay.nonWorkingDay'))
            $this->accessMenuCount++;


		/******************************************
		 * Component menu
		 */		
		$componnents = new MenuItem('null', Prado::localize('Components',array(), "messages"), '#');

                $db = $this->Application->getModule('horuxDb')->DbConnection;
                
                if(!$db) return $menu->render();	
                
                $db->Active=true;
		
		$cmd=$db->createCommand("SELECT * FROM hr_install WHERE type='component'");
		$data = $cmd->query();
		$data = $data->readAll();
		
		foreach($data as $d)
		{
			$cmd=$db->createCommand("SELECT * FROM hr_install AS i LEFT JOIN hr_component as c ON c.id_install=i.id WHERE i.type='component' AND c.parentmenu=0 AND i.id=".$d['id']);
			$data2 = $cmd->query();
			$data2 = $data2->read();

			$asset = $this->Application->getAssetManager();
			$url = $asset->publishFilePath('./protected/pages/components/'.$d['name'].'/assets/'.$data2['iconmenu']);
			$item = new MenuItem("'<img src=\"".$url."\" />'",
								 Prado::localize($data2['menuname'],array(), $d['name']), 
								 $this->Service->constructUrl($data2['page']),
								 $this->isAccess($data2['page'])
								 );

            if($this->isAccess($data2['page']))
                $this->componentMenuCount++;

			$cmd=$db->createCommand("SELECT * FROM hr_install AS i LEFT JOIN hr_component as c ON c.id_install=i.id WHERE i.type='component' AND c.parentmenu=".$data2['id']." AND c.parentmenu>0 AND i.id=".$d['id']);
			$data2 = $cmd->query();
			$data2 = $data2->readAll();
		
			foreach($data2 as $d2)
			{
				$asset = $this->Application->getAssetManager();
				$url = $asset->publishFilePath('./protected/pages/components/'.$d2['name'].'/assets/'.$d2['iconmenu']);

				$item->addMenuItem(new MenuItem("'<img src=\"".$url."\" />'",
												Prado::localize($d2['menuname'],array(), $d['name']), 
												$this->Service->constructUrl($d2['page']),
												$this->isAccess($d2['page'])));
			}
			
			$componnents->addMenuItem($item);			
		}		
		
		/******************************************
		 * Extension menu
		 */		
		$extensions = new MenuItem('null', Prado::localize('Extensions',array(), "messages"), '#');
		$extensions->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-install.png\" />'", 
										  Prado::localize('Install/Uninstal',array(), "messages"), 
										  $this->Service->constructUrl('installation.extensions'),
										  $this->isAccess('installation.extensions')
										  ));

        if($this->isAccess('installation.extensions'))
            $this->extensionMenuCount++;

		$extensions->addSplit();

		$extensions->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-hardware.png\" />'", 
										  Prado::localize('Devices Manager',array(), "messages"), 
										  $this->Service->constructUrl('installation.devices'),
										  $this->isAccess('installation.devices')
										  ));
        if($this->isAccess('installation.devices'))
            $this->extensionMenuCount++;

		$extensions->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-component.png\" />'", 
										  Prado::localize('Component Manager',array(), "messages"), 
										  $this->Service->constructUrl('installation.components'),
										  $this->isAccess('installation.components')
										  ));
        if($this->isAccess('installation.components'))
            $this->extensionMenuCount++;


		$extensions->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-themes.png\" />'", 
										  Prado::localize('Template Manager',array(), "messages"), 
										  $this->Service->constructUrl('installation.template'),
										  $this->isAccess('installation.template')
										  ));
        if($this->isAccess('installation.template'))
            $this->extensionMenuCount++;


		$extensions->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-language.png\" />'", 
										  Prado::localize('Language Manager',array(), "messages"), 
										  $this->Service->constructUrl('installation.language'),
										  $this->isAccess('installation.language')
										  ));
        if($this->isAccess('installation.language'))
            $this->extensionMenuCount++;

		
		/******************************************
		 * Tools menu
		 */		
		$tools = new MenuItem('null', Prado::localize('Tools',array(), "messages"), '#');

		$tools->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-globalCheckin.png\" />'", 
										  Prado::localize('Gloal Check-in',array(), "messages"), 
										  $this->Service->constructUrl('tool.GlobalCheckin'),
										  $this->isAccess('tool.GlobalCheckin')
										  ));

        if($this->isAccess('tool.GlobalCheckin'))
            $this->toolMenuCount++;

		$tools->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-guilog.png\" />'",
										  Prado::localize('Horux Gui log',array(), "messages"),
										  $this->Service->constructUrl('tool.GuiLog'),
										  $this->isAccess('tool.GuiLog')
										  ));

        if($this->isAccess('tool.GuiLog'))
            $this->toolMenuCount++;

		/******************************************
		 * Help menu
		 */
		$help = new MenuItem('null', Prado::localize('Info',array(), "messages"), '#');
		$help->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-help.png\" />'", 
										  Prado::localize('Horux About',array(), "messages"), 
										  $this->Service->constructUrl('help.About'),
										  /*$this->isAccess('help.About')*/1
										  ));

		$help->addSplit();

		$help->addMenuItem( new MenuItem("'<img src=\"./themes/letux/images/menu/icon-16-systemInfo.png\" />'", 
										  Prado::localize('System Info',array(), "messages"), 
										  $this->Service->constructUrl('help.SystemInfo'),
										  $this->isAccess('help.SystemInfo')
										  ));		

		
		
		$menu->addMenuItem($horux);
		$menu->addMenuItem($system);
		$menu->addMenuItem($access);
		$menu->addMenuItem($componnents);
		$menu->addMenuItem($extensions);
		$menu->addMenuItem($tools);
		$menu->addMenuItem($help);
	
		return $menu->render();	
	}


    public function setAccessLink($flag)
    {
      $this->accessLink->Visible = $flag;
    }

	public function getUserLogged()
	{	
        if(get_class($this->page) == 'install') return true;

		$app = $this->getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;

        $sql = "SELECT COUNT(*) AS n FROM hr_superusers WHERE isLogged=1";
        $cmd= $db->createCommand($sql);
        $data = $cmd->query();
        $data = $data->read();

        return $data['n'];
	} 

	public function getAlarm()
	{
		if(get_class($this->page) == 'install') return true;

        $app = $this->getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;

        $sql = "SELECT COUNT(*) AS n FROM hr_alarms WHERE checked=0";
        $cmd=$db->createCommand($sql);
        $data = $cmd->query();
        $data = $data->read();

        /*if($data['n'] == 0)
            $this->alarmLabelButton->setVisible(false);
        else
            $this->alarmLabelButton->setVisible(true);*/

        return $data['n'];
	}

    function onDispAlarm($sender, $param)
    {
		if(get_class($this->page) == 'install') return true;

        $app = $this->getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;

        $sql = "SELECT COUNT(*) AS n FROM hr_alarms WHERE checked=0";
        $cmd=$db->createCommand($sql);
        $data = $cmd->query();
        $data = $data->read();

        if($data['n'] == 0)
            $this->alarmLabelButton->setVisible(false);
        else
            $this->alarmLabelButton->setVisible(true);

        $this->alarmLabel->Text = $data['n'];
    }

    public function onCheckAlaram($sender, $param)
    {
        if($this->isAccess('system.Alarms'))
        {
    		$app = $this->getApplication();
            $db = $app->getModule('horuxDb')->DbConnection;

            $sql = "UPDATE hr_alarms SET checked=1 WHERE checked=0";
            $cmd = $db->createCommand($sql);
            $res = $cmd->Execute();

            $this->Response->redirect($this->Service->constructUrl('system.Alarms'));
        }
    }

	public function onLogout($sender, $param)
	{
		$userId = $this->Application->getUser()->getUserId();	
		
	
		$authManager=$this->Application->getModule('Auth');
		$authManager->logout();

		

		$app = $this->getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;
		
		$sql = "UPDATE hr_superusers SET isLogged=0 WHERE id=".$userId;
		$cmd = $db->createCommand($sql);
		$res = $cmd->Execute();			
		
		$this->application->clearGlobalState('lang');

        $username = $this->Application->getUser()->getName();
        $guiLog = new TGuiLog();
        $guiLog->log($username." is logged out");

		$this->Response->redirect($this->Service->constructUrl('login.login'));		
	}
	
    public function isAccess($page)
    {  	
		$app = $this->getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;
      	
      	if(!$db) return true;
      	
      	$db->Active=true;
	
		$usedId = $app->getUser()->getUserID() == null ? 0 : $app->getUser()->getUserID(); 
		$groupId = $app->getUser()->getGroupID() == null ? 0 : $app->getUser()->getGroupID(); 
		
		$sql = 	'SELECT `allowed` FROM hr_gui_permissions WHERE ' .
				'(`page`=\''.$page.'\' OR `page` IS NULL) ' .
				"AND (" .
					"(`selector`='user_id' AND `value`=".$usedId.") " .
					"OR (`selector`='group_id' AND `value`=".$groupId.") " .
				")" .
			'ORDER BY `page` DESC';

		$cmd = $db->createCommand($sql);
		$res = $cmd->query();	
		$res = $res->readAll();	
		// If there were no results
		if (!$res)
			return false;
		else
			// Traverse results
			foreach ($res as $allowed)
			{
				// If we get deny here
				if (! $allowed)
					return false;
			}
	
		return true;
    }
}

?>
