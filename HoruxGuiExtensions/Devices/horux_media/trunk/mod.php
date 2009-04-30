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

Prado::using('horux.pages.hardware.device.horux_InfoDisplay.sql');

class mod extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        
        if(!$this->isPostBack)
        {

          $userId=$this->Application->getUser()->getUserId();
    	  $this->blockRecord('hr_device', $this->Request['id'], $userId);          
          
	  $this->id_action_device->setDataValueField('id');
	  $this->id_action_device->setDataTextField('name');
          $this->id_action_device->DataSource=$this->AccessPoint;
          $this->id_action_device->dataBind(); 
            
          $param = $this->Application->getParameters();
          $superAdmin = $this->Application->getUser()->getSuperAdmin();
          
          if($param['appMode'] == 'demo' && $superAdmin == 0)
          {
                  $this->Save->setEnabled(false);
                  $this->Apply->setEnabled(false);
          }  

          $this->id->Value = $this->Request['id'];
          $this->setData();
          
        }        
    }

    public function getAccessPoint()
    {
        $command=$this->db->createCommand(SQL::SQL_GET_ACCESSPOINT);
        $data = $command->query();    	
        return $data;
    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_GET_INFODISPLAY );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();

        if($query)
        {
          $data = $query->read();
          $this->name->Text = $data['name'];
          $this->id_action_device->setSelectedValue( $data['id_action_device'] );
 
          $this->ip->Text = $data['ip'];
          $this->port->Text = $data['port'];
          $this->comment->Text = $data['description'];
          $this->isLog->setChecked($data['isLog'] );
        } 
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $id = $this->id->Value;
            $pBack = array('okMsg'=>Prado::localize('The device was modified successfully'), 'id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('hardware.device.horux_InfoDisplay.mod', $pBack));
          }
          else
          {
            $pBack = array('koMsg'=>Prado::localize('The device was not modified'));
          }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The device was modified successfully'));
          }
          else
            $pBack = array('koMsg'=>Prado::localize('The device was not modified'));
          
          $this->blockRecord('hr_device', $this->id->Value, 0);
          $this->Response->redirect($this->Service->constructUrl('hardware.HardwareList',$pBack));
        }
    }

	public function onCancel($sender, $param)
	{
	     $this->blockRecord('hr_device', $this->id->Value, 0);	
            $this->Response->redirect($this->Service->constructUrl('hardware.HardwareList'));	
	}    

    protected function saveData()
    {
      $cmd = $this->db->createCommand( SQL::SQL_MOD_DEVICE );
	  $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":description",$this->comment->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":isLog",$this->isLog->getChecked(),PDO::PARAM_STR);
	  $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);
	  $cmd->Execute();

  
      $cmd = $this->db->createCommand( SQL::SQL_UPDATE_INFODISPLAY );
	  $cmd->bindParameter(":ip",$this->ip->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":port",$this->port->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":id_action_device",$this->id_action_device->getSelectedValue(),PDO::PARAM_STR);

	  $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);
	  $cmd->Execute();
	  
	  return true;
    }

    public function serverValidateName($sender, $param)
    {
      $cmd = $this->db->createCommand( SQL::SQL_IS_READER_NAME_EXIST2);
      $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);
      $array = $cmd->query()->readAll();

      if(count($array) > 0)
        $param->IsValid=false;
      else 
        $param->IsValid=true;
	}


}
