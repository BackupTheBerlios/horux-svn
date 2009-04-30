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

class userMod extends Page
{
	protected $isPasswordChanged = false;
	
    public function onLoad($param)
    {
        parent::onLoad($param);

		$superAdmin = $this->Application->getUser()->getSuperAdmin();
		$param = $this->Application->getParameters();

		if($param['appMode'] == 'demo' && $superAdmin == 0)
		{
			$this->apply->setEnabled(false);
			$this->save->setEnabled(false);
		} 


        if(!$this->isPostBack)
        {
        
          $superAdmin = $this->Application->getUser()->getSuperAdmin();		
        
          $userId=$this->Application->getUser()->getUserId();
    	  $this->blockRecord('hr_superusers', $this->Request['id'], $userId);	

		  if($userId != $this->Request['id'] && !$superAdmin)
		  {
		  	$pBack = array('koMsg'=>Prado::localize("You don't have the right to modify this user'"));	
		  
		  	$this->Response->redirect($this->Service->constructUrl('superuser.userList',$pBack));
		  }

          $this->id->Value = $this->Request['id'];
          $this->setData();
          
		  $this->group_id->DataTextField='name';
		  $this->group_id->DataValueField='id';
          $this->group_id->DataSource=$this->DataGroup;
          $this->group_id->dataBind();        
          
          if(!$superAdmin)
          {
          	$this->group_id->setEnabled(false);
          	$this->user_id->setEnabled(false);
          	$this->name->setEnabled(false);
          }

		  $this->user_id->DataTextField='name';
		  $this->user_id->DataValueField='id';
          $this->user_id->DataSource=$this->DataPerson;
          $this->user_id->dataBind();           
        }
    }	
	
	public function setData()
	{
        $cmd = $this->db->createCommand( SQL::SQL_GET_USER_BY_ID );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
          $data = $query->read();
          
          $this->name->Text = $data['name'];
          $this->email->Text = $data['email'];
          $this->group_id->setSelectedValue($data['group_id']); 
          $this->user_id->setSelectedValue($data['user_id']); 
          $this->currentPswd->Value = $data['password']; 
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
            $id = $this->id->Value;
            if($this->isPasswordChanged)
	            $pBack = array('okMsg'=>Prado::localize('The user was modified successfully. The password was changed'), 'id'=>$id);
	        else
	        	$pBack = array('okMsg'=>Prado::localize('The user was modified successfully. The password was not changed'), 'id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('superuser.userMod', $pBack));
          }
          else
          {
          	$id = $this->id->Value;
          	$pBack = array('koMsg'=>Prado::localize('The user was not modified. The password was not changed'), 'id'=>$id);
          	$this->Response->redirect($this->Service->constructUrl('superuser.userMod',$pBack));        	          	
          }
        }		
	}

	public function onSave($sender, $param)
	{
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            if($this->isPasswordChanged)
	            $pBack = array('okMsg'=>Prado::localize('The user was modified successfully. The password was changed'));
			else            
	            $pBack = array('okMsg'=>Prado::localize('The user was modified successfully. The password was not changed'));
          }
          else
          {
            	$pBack = array('koMsg'=>Prado::localize('The user was not modified. The password was not changed'));
          }
          $this->blockRecord('hr_superusers', $this->id->Value, 0);
          $this->Response->redirect($this->Service->constructUrl('superuser.userList',$pBack));
        }		
	}

	public function onCancel($sender, $param)
	{
		$this->blockRecord('hr_superusers', $this->id->Value, 0);	
        $this->Response->redirect($this->Service->constructUrl('superuser.userList'));	
	}

	public function saveData()
	{
      $cmd = $this->db->createCommand( SQL::SQL_MOD_USER );
      $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":email",$this->email->SafeText,PDO::PARAM_STR);

      if($this->password->SafeText == "")
      {
      	$cmd->bindParameter(":password",$this->currentPswd->Value,PDO::PARAM_STR);
      	$this->isPasswordChanged = false;
      }
      else
      {
      	$cmd->bindParameter(":password",sha1( $this->password->SafeText),PDO::PARAM_STR);
      	$this->isPasswordChanged = true;
      }
      	
      $cmd->bindParameter(":group_id",$this->group_id->getSelectedValue(),PDO::PARAM_INT);
      $cmd->bindParameter(":user_id",$this->user_id->getSelectedValue(),PDO::PARAM_INT);
      $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);

      if(!$cmd->execute()) return false;

      $this->log("Modify the super user:".$this->name->SafeText);

      return true;
	} 	
	
	public function serverValidatePassword($sender, $param)
	{
		if($this->password->Text != $this->confirmation->Text && $this->password->Text != "")
			 $param->IsValid=false;		
	}	
	
	public function serverValidateName($sender, $param)
	{
	  $cmd = $this->db->createCommand( SQL::SQL_IS_NAME_EXIST2 );
      $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
      $data = $cmd->query();
      $data = $data->read();
      
     
      if($data['nb'] > 0)
      	$param->IsValid=false;		
	}	 
}
