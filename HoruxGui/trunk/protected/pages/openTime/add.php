<?php
/**
* @version      $Id$
* @package      Horux
* @subpackage   Horux
* @copyright    Copyright (C) 2008  Letux. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* Horux is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

Prado::using('horux.pages.openTime.sql');

class add extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);       
    }
    
    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $id = $this->db->getLastInsertID();
            $pBack = array('okMsg'=>Prado::localize('The open time was added successfully'), 'id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('openTime.mod', $pBack));
          }
          else
          {
            $pBack = array('koMsg'=>Prado::localize('The open time was not added'));
          }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The open time was added successfully'));
          }
          else
            $pBack = array('koMsg'=>Prado::localize('The open time was not added'));
          $this->Response->redirect($this->Service->constructUrl('openTime.openTimeList',$pBack));
        }
    }

    protected function saveData()
    {
      	$cmd = $this->db->createCommand( SQL::SQL_ADD_OPEN_TIME );
      	$cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
      	$cmd->bindParameter(":non_working_day",$this->nonWorkingDayAccess->Checked,PDO::PARAM_STR);
	    $cmd->bindParameter(":week_end",$this->weekEndAccess->Checked,PDO::PARAM_STR);
      	$cmd->bindParameter(":monday_default",$this->mondayDefault->Checked,PDO::PARAM_STR);

		$from = $this->dateToSql($this->from->SafeText);
		$until = $this->dateToSql($this->until->SafeText);
		

      	$cmd->bindParameter(":from",$from,PDO::PARAM_STR);
      	$cmd->bindParameter(":until",$until,PDO::PARAM_STR);
      	$cmd->bindParameter(":comment",$this->comment->SafeText,PDO::PARAM_STR);

		$res = $cmd->execute();

		if($res)
		{
			$lastId = $this->db->getLastInsertId();
	
			$this->saveTimeData($this->time1->Value, 'lundi', $lastId);
			$this->saveTimeData($this->time2->Value, 'mardi', $lastId);
			$this->saveTimeData($this->time3->Value, 'mercredi', $lastId);
			$this->saveTimeData($this->time4->Value, 'jeudi', $lastId);
			$this->saveTimeData($this->time5->Value, 'vendredi', $lastId);
			$this->saveTimeData($this->time6->Value, 'samedi', $lastId);
			$this->saveTimeData($this->time7->Value, 'dimanche', $lastId);
		}
      	return $res;
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

		      	$cmd = $this->db->createCommand( SQL::SQL_ADD_OPEN_TIME_TIME );
		      	$cmd->bindParameter(":id_openTime",$lastId,PDO::PARAM_STR);
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
