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

Prado::using('horux.pages.accessLevel.sql');

class mod extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);
                
       if(!$this->isPostBack)
        {
          $userId=$this->Application->getUser()->getUserId();
    	  $this->blockRecord('hr_access_level', $this->Request['id'], $userId);	

          $this->id->Value = $this->Request['id'];
          $this->setData();
        }
    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_GET_ACCESS_LEVEL_ID );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
          $data = $query->read();
          $this->id->Value = $data['id'];
          $this->name->Text = $data['name'];
          $this->comment->Text = $data['comment'];
          $this->fullAccess->setChecked($data['full_access']);
          $this->nonWorkingDayAccess->setChecked($data['non_working_day']);
          $this->weekEndAccess->setChecked($data['week_end']);
          $this->mondayDefault->setChecked($data['monday_default']);

		  if($data['validity_date'] != '0000-00-00' && $data['validity_date'] != NULL)
		  {
		  	$this->from->Text = $this->dateFromSql($data['validity_date']);	
		  }

		  if($data['validity_date_to'] != '0000-00-00'  && $data['validity_date_to'] != NULL)
		  {
	  		  $this->until->Text = $this->dateFromSql($data['validity_date_to']);	
		  }
        } 

        $cmd = $this->db->createCommand( SQL::SQL_GET_ACCESS_TIME_ID );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
        	$data = $query->readAll();

			$this->time1->Value = "000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000";
			$this->time2->Value = "000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000";
			$this->time3->Value = "000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000";
			$this->time4->Value = "000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000";
			$this->time5->Value = "000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000";
			$this->time6->Value = "000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000";
			$this->time7->Value = "000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000";
        	
        	foreach($data as $d)
        	{
				$start = $d['from'];
				$stop = $d['until'];
				$day = $d['day'];
      			  		
				$n_case = ($stop - $start) / 15;

				if($start == 0)
					$index = 0; 
				else
					$index = ($start/15);
				switch($day)
				{
					case "lundi": 
						$v = $this->time1->Value;
						for($i=0;$i<$n_case;$i++)
							$v{$index+$i}  = '1';
						$this->time1->Value = $v;
						break;
					case "mardi":
						$v = $this->time2->Value;
						for($i=0;$i<$n_case;$i++)
							$v{$index+$i}  = '1';
						$this->time2->Value = $v;
						break;
					case "mercredi":
						$v = $this->time3->Value;
						for($i=0;$i<$n_case;$i++)
							$v{$index+$i}  = '1';
						$this->time3->Value = $v;
						break;
					case "jeudi":
						$v = $this->time4->Value;
						for($i=0;$i<$n_case;$i++)
							$v{$index+$i}  = '1';
						$this->time4->Value = $v;
						break; 
					case "vendredi":
						$v = $this->time5->Value;
						for($i=0;$i<$n_case;$i++)
							$v{$index+$i}  = '1';
						$this->time5->Value = $v;
						break;
					case "samedi":
						$v = $this->time6->Value;
						for($i=0;$i<$n_case;$i++)
							$v{$index+$i}  = '1';
						$this->time6->Value = $v;
						break;
					case "dimanche":
						$v = $this->time7->Value;
						for($i=0;$i<$n_case;$i++)
							$v{$index+$i}  = '1';
						$this->time7->Value = $v;
						break;
				} 

        	}
        	
        }
        
        
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $id = $this->id->Value;
            $pBack = array('okMsg'=>Prado::localize('The access level was modified successfully'), 'id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('accessLevel.mod', $pBack));
          }
          else
          {
            $pBack = array('koMsg'=>Prado::localize('The access level was not added'));
          }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The access level was modified successfully'));
          }
          else
            $pBack = array('koMsg'=>Prado::localize('The access level was not modified'));

          $this->blockRecord('hr_access_level', $this->id->Value, 0);
          $this->Response->redirect($this->Service->constructUrl('accessLevel.accessLevelList',$pBack));
        }
    }
    
	public function onCancel($sender, $param)
	{
		$this->blockRecord('hr_access_level', $this->id->Value, 0);	
        $this->Response->redirect($this->Service->constructUrl('accessLevel.accessLevelList'));	
	}

    protected function saveData()
    {
      	$cmd = $this->db->createCommand( SQL::SQL_MOD_ACCESS_LEVEL );
      	$cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
      	$cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
      	$cmd->bindParameter(":full_access",$this->fullAccess->Checked,PDO::PARAM_STR);
      	$cmd->bindParameter(":non_working_day",$this->nonWorkingDayAccess->Checked,PDO::PARAM_STR);
	    $cmd->bindParameter(":week_end",$this->weekEndAccess->Checked,PDO::PARAM_STR);
      	$cmd->bindParameter(":monday_default",$this->mondayDefault->Checked,PDO::PARAM_STR);

		$from = $this->dateToSql($this->from->SafeText);
		$until = $this->dateToSql($this->until->SafeText);

      	$cmd->bindParameter(":from",$from,PDO::PARAM_STR);
      	$cmd->bindParameter(":until",$until,PDO::PARAM_STR);
      	$cmd->bindParameter(":comment",$this->comment->SafeText,PDO::PARAM_STR);

		$res2 = $cmd->execute();
	
    	$cmd = $this->db->createCommand( SQL::SQL_REMOVE_ACCESS_TIME );	
   	    $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
		$res = $cmd->execute();	

			$this->saveTimeData($this->time1->Value, 'lundi', $this->Request['id']);
			$this->saveTimeData($this->time2->Value, 'mardi', $this->Request['id']);
			$this->saveTimeData($this->time3->Value, 'mercredi', $this->Request['id']);
			$this->saveTimeData($this->time4->Value, 'jeudi', $this->Request['id']);
			$this->saveTimeData($this->time5->Value, 'vendredi', $this->Request['id']);
			$this->saveTimeData($this->time6->Value, 'samedi', $this->Request['id']);
			$this->saveTimeData($this->time7->Value, 'dimanche', $this->Request['id']);		

        $this->log("Modify the access level: ".$this->name->SafeText);

		return $res || $res2;
    }

    protected function saveTimeData($day, $dayName,$lastId)
    {
		$indexStartHours=0;
		$indexEndHours=0;
		for($i=0; $i<96; $i++)
		{
			if(strcmp($day{$i},"1")==0)
			{
				$indexStartHours = $i;
	
				while( (strcmp($day{$i},"1") == 0 ) )
				{
					$i++;
					
					if($i === 96)
						break;
				}
				$indexEndHours = $i;
				$indexStartHours *=15;
				$indexEndHours *=15;
		      	$cmd = $this->db->createCommand( SQL::SQL_ADD_ACCESS_LEVEL_TIME );
		      	$cmd->bindParameter(":id_access_level",$lastId,PDO::PARAM_STR);
		      	$cmd->bindParameter(":day",$dayName,PDO::PARAM_STR);
			    $cmd->bindParameter(":from",$indexStartHours,PDO::PARAM_INT);
		      	$cmd->bindParameter(":until",$indexEndHours,PDO::PARAM_INT);
				
				$cmd->execute();
			}
		}	
    } 

	protected function serverUntilValidate($sender, $param)
	{
		if( $this->until->SafeText == "" ) return; 
	
		$until = strtotime($this->until->SafeText);	
		$from = strtotime($this->from->SafeText);
		if($until<$from)
			$param->IsValid=false;
	}

}
