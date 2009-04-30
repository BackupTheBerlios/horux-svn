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

Prado::using('horux.pages.controlPanel.sql');

class ControlPanel extends Page
{
	
    protected $alarmMessage = array();
	
    public function onLoad($param)
    {    
        parent::onLoad($param);
        
        $this->usersGrid->DataSource=$this->UsersLogged;
        $this->usersGrid->dataBind();

        $this->trackGrid->DataSource=$this->LastTrack;
        $this->trackGrid->dataBind();


        $this->alarmsGrid->DataSource=$this->LastAlarms;
        $this->alarmsGrid->dataBind();
        
        $app = $this->getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;

        $sql = "SELECT `key` FROM hr_config";
		$cmd = $db->createCommand($sql);
		$res = $cmd->query();	
		$res = $res->read();	
		$_SESSION['helpKey'] = $res['key'];
		
        

    }
    
    public function isAccess($page)
    {  	
		$app = $this->getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;
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
    
    protected function getUsersLogged()
    {
        $command=$this->db->createCommand(SQL::SQL_GET_USER_LOGGED);
        $dataReader=$command->query();
        
        $connection->Active=false;  // connection is established

        return $dataReader;
    }

    protected function getLastAlarms()
    {
       $this->alarmMessage[1001] = Prado::localize("1001");
        $this->alarmMessage[1002] = Prado::localize("1002");
        $this->alarmMessage[1003] = Prado::localize("1003");
        $this->alarmMessage[1004] = Prado::localize("1004");
        $this->alarmMessage[1005] = Prado::localize("1005");
        $this->alarmMessage[1006] = Prado::localize("1006");
        $this->alarmMessage[1007] = Prado::localize("1007");
        $this->alarmMessage[1008] = Prado::localize("1008");
        $this->alarmMessage[1009] = Prado::localize("1009");
        $this->alarmMessage[1010] = Prado::localize("1010");
        $this->alarmMessage[1011] = Prado::localize("1011");
        $this->alarmMessage[1012] = Prado::localize("1012");
        $this->alarmMessage[1013] = Prado::localize("1013");
        $this->alarmMessage[1014] = Prado::localize("1014");
        $this->alarmMessage[1015] = Prado::localize("1015");
        $this->alarmMessage[1016] = Prado::localize("1016");

        $this->alarmMessage[1100] = Prado::localize("1100");
        $this->alarmMessage[1101] = Prado::localize("1101");

        $this->alarmMessage[1200] = Prado::localize("1200");
    	
        $command=$this->db->createCommand(SQL::SQL_GET_LAST_ALARMS);
        $dataReader=$command->query();
        
		$data = $dataReader->readAll();
		
		for($i=0; $i<count($data); $i++)
		{
			$dateAndTime = explode(" ", $data[$i]['datetime_']);
			$data[$i]['datetime_'] = date("d-m-Y", strtotime($dateAndTime[0])).' '.$dateAndTime[1];
		
			$text =  $this->alarmMessage[$data[$i]['type']];		
			$data[$i]['description'] = $text;
		
           if($data[$i]['type'] >= 1001 && $data[$i]['type'] <= 1099)
            {
 				   $object_type = Prado::localize("Device");
				   $sql = "SELECT * FROM hr_device WHERE id=".$data[$i]['id_object'];
				   $command=$this->db->createCommand($sql);
				   $dataObj=$command->query();
				   $dataObj = $dataObj->read();
				   $object = $dataObj['name'];
            }

            if($data[$i]['type'] >= 1100 && $data[$i]['type'] <= 1199)
            {
				   $object_type =  Prado::localize("User");
				   $sql = "SELECT * FROM hr_user WHERE id=".$data[$i]['id_object'];
				   $command=$this->db->createCommand($sql);
				   $dataObj=$command->query();
				   $dataObj = $dataObj->read();
				   $object = $dataObj['name']." ".$dataObj['firstname'];
            }
		
			$data[$i]['object'] = '<i>'.$object_type.'</i>:'.$object;
		
		}

  			
        $connection->Active=false;  // connection is established

        return $data;
    }

    protected function getLastTrack()
    {
        $command = NULL; 
        if($this->db->DriverName == 'sqlite')
        {
            $command=$this->db->createCommand(SQL::SQL_GET_LAST_TRACK_SQLITE);
        }
        else
        {
            $command=$this->db->createCommand(SQL::SQL_GET_LAST_TRACK);
        }
        $dataReader=$command->query();
        
        $connection->Active=false;  // connection is established

        return $dataReader;
    }
    
}

?>
