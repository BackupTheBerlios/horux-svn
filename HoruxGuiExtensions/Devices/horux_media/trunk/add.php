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

class add extends Page
{
    protected $lastId;
    public function onLoad($param)
    {
        parent::onLoad($param);
        
        if(!$this->IsPostBack)
        {
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
        }
    }
    
    public function getAccessPoint()
    {
        $command=$this->db->createCommand(SQL::SQL_GET_ACCESSPOINT);
        $data = $command->query();    	
        return $data;
    }

    public function onApply($sender, $param)
    {
      if($this->Page->IsValid)
      {
        if($this->saveData())
        {
          $id = $this->lastId;
          $pBack = array('okMsg'=>Prado::localize('The device was added successfully'), 'id'=>$id);
          $this->Response->redirect($this->Service->constructUrl('hardware.device.horux_InfoDisplay.mod', $pBack));
        }
        else
        {
              $pBack = array('koMsg'=>Prado::localize('The device was not added'));
              $this->Response->redirect($this->Service->constructUrl('hardware.device.horux_InfoDisplay.add',$pBack));        	          	
        }
      }		
    }

	public function onSave($sender, $param)
	{
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The device was added successfully'));
          }
          else
          {
           	$pBack = array('koMsg'=>Prado::localize('The device was not saved'));
          }
          $this->Response->redirect($this->Service->constructUrl('hardware.HardwareList',$pBack));
        }		
	}

	public function saveData()
	{
          $cmd = $this->db->createCommand( SQL::SQL_ADD_DEVICE );
	  $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":description",$this->comment->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":isLog",$this->isLog->getChecked(),PDO::PARAM_STR);
	  $cmd->Execute();

	  $this->lastId = $this->db->getLastInsertID();
  
          $cmd = $this->db->createCommand( SQL::SQL_ADD_INFODISPLAY );
	  $cmd->bindParameter(":id_device",$this->lastId,PDO::PARAM_STR);
	  $cmd->bindParameter(":ip",$this->ip->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":port",$this->port->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":id_action_device",$this->id_action_device->getSelectedValue(),PDO::PARAM_STR);

	  $cmd->Execute();

      
          return true;
	}     
	
    public function serverValidateName($sender, $param)
    {
      $cmd = $this->db->createCommand( SQL::SQL_IS_NAME_EXIST);
      $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
      $array = $cmd->query()->readAll();

      if(count($array) > 0)
        $param->IsValid=false;
      else 
        $param->IsValid=true;
    }

}
