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

Prado::using('horux.pages.nonWorkingDay.sql');

class mod extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {
          $userId=$this->Application->getUser()->getUserId();
    	  $this->blockRecord('hr_non_working_day', $this->Request['id'], $userId);        	
       
          $this->id->Value = $this->Request['id'];
          $this->setData();
        }
        
    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_GET_NONWORKINGDAY );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
          $data = $query->read();
          
          $from = $this->dateFromSql($data['from']);
          $until = $this->dateFromSql($data['until']);
        
          $this->id->Value = $data['id'];
          $this->name->Text = $data['name'];
          $this->from->Text = $from;
          $this->until->Text = $until;
          $this->comment->Text = $data['comment'];
        } 
    }

	public function onDelete($sender, $param)
	{
        $this->log("Delete the non working day: ".$this->name->SafeText);

		$cmd = $this->db->createCommand( SQL::SQL_DELETE_NONWORKINGDAY );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
		
	    $cmd->execute();

        $pBack = array('okMsg'=>Prado::localize('The non working day was deleted successfully'), 'id'=>$this->id->Value);
        $this->Response->redirect($this->Service->constructUrl('nonWorkingDay.nonWorkingDay', $pBack));
	}
    
    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The non working day was modified successfully'), 'id'=>$this->id->Value);
            $this->Response->redirect($this->Service->constructUrl('nonWorkingDay.mod', $pBack));
          }
          else
          {
            $pBack = array('koMsg'=>Prado::localize('The non workong day was not modified'), 'id'=>$this->id->Value);
            $this->Response->redirect($this->Service->constructUrl('nonWorkingDay.mod', $pBack));
          }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The non working day was modified successfully'));
          }
          else
            $pBack = array('koMsg'=>Prado::localize('The non working day was not modified'));
          $this->blockRecord('hr_non_working_day', $this->id->Value, 0);
          $this->Response->redirect($this->Service->constructUrl('nonWorkingDay.nonWorkingDay',$pBack));
        }
    }

	public function onCancel($sender, $param)
	{
		$this->blockRecord('hr_non_working_day', $this->id->Value, 0);	
        $this->Response->redirect($this->Service->constructUrl('nonWorkingDay.nonWorkingDay'));	
	}

    protected function saveData()
    {
    	$from = $this->dateToSql($this->from->SafeText);	
    		
		if($this->until->SafeText != "")
		{
			$until = $this->dateToSql($this->until->SafeText);
		}
		else
			$until = $from;

		$cmd = $this->db->createCommand( SQL::SQL_UPDATE_NONWORKINGDAY );
		$cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
		$cmd->bindParameter(":from",$from, PDO::PARAM_STR);
		$cmd->bindParameter(":until",$until, PDO::PARAM_STR);
		$cmd->bindParameter(":comment",$this->comment->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);

        $this->log("Modify the non working day: ".$this->name->SafeText);

		return $cmd->execute();
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
