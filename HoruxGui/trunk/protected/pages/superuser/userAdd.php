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

class userAdd extends Page
{
	public function onLoad($param)
	{
        parent::onLoad($param);

        if(!$this->isPostBack)
        {
        	
			$this->group_id->DataTextField='name';
			$this->group_id->DataValueField='id';
            $this->group_id->DataSource=$this->DataGroup;
            $this->group_id->dataBind();        

			$this->user_id->DataTextField='name';
			$this->user_id->DataValueField='id';
            $this->user_id->DataSource=$this->DataPerson;
            $this->user_id->dataBind();        

        }		
	}	

	protected function getDataGroup()
	{
		$cmd=$this->db->createCommand(SQL::SQL_GET_ALL_GROUP);
        $data=$cmd->query();

        return $data;		
	}

	protected function getDataPerson()
	{
        $cmd = NULL;
        if($this->db->DriverName == 'sqlite')
        {
            $cmd=$this->db->createCommand(SQL::SQL_GET_ALL_PERSON_SQLITE);
        }
        else
        {
            $cmd=$this->db->createCommand(SQL::SQL_GET_ALL_PERSON);
        }

        $data=$cmd->query();

		$data = $data->readAll();
		$data1[] = array('id'=>0, 'name'=>'---');
		
		$data = array_merge($data1, $data);


        return $data;		
	}


	public function onApply($sender, $param)
	{
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $id = $this->db->getLastInsertID();
            $pBack = array('okMsg'=>Prado::localize('The user was added successfully'), 'id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('superuser.userMod', $pBack));
          }
          else
          {
           	$pBack = array('koMsg'=>Prado::localize('The user was not added'));
          	$this->Response->redirect($this->Service->constructUrl('superuser.userAdd',$pBack));        	          	
          }
        }		
	}

	public function onSave($sender, $param)
	{
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The user was added successfully'));
          }
          else
          {
           	$pBack = array('koMsg'=>Prado::localize('The user was not saved'));
          }

          $this->Response->redirect($this->Service->constructUrl('superuser.userList',$pBack));
        }		
	}

	public function saveData()
	{
      $cmd = $this->db->createCommand( SQL::SQL_ADD_USER );
      $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":password",sha1($this->password->SafeText),PDO::PARAM_STR);
      $cmd->bindParameter(":email",$this->email->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":group_id",$this->group_id->getSelectedValue(),PDO::PARAM_INT);
      $cmd->bindParameter(":user_id",$this->user_id->getSelectedValue(),PDO::PARAM_INT);
      

      if(!$cmd->execute()) return false;
      
      $this->log("Add the super user:".$this->name->SafeText);

      return true;
	} 
	
	public function serverValidatePassword($sender, $param)
	{
		if($this->password->Text != $this->confirmation->Text)
			 $param->IsValid=false;		
	}	
	
	public function serverValidateName($sender, $param)
	{
      $cmd = $this->db->createCommand( SQL::SQL_IS_NAME_EXIST );
      $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
      $data = $cmd->query();
      $data = $data->read();
      
     
      if($data['nb'] > 0)
      	$param->IsValid=false;		
	}	 
}
