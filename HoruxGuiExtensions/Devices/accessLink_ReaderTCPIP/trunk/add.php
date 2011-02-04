<?php

Prado::using('horux.pages.hardware.device.accessLink_ReaderTCPIP.sql');

class add extends AddDevicePage
{
    public function onLoad($param)
    {
        $this->deviceName = "a3m_lgm";

        parent::onLoad($param);
    }
    
    public function getInterface()
    {
        $command=$this->db->createCommand(SQL::SQL_GET_INTERFACE);
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
            $this->Response->redirect($this->Service->constructUrl('hardware.device.accessLink_ReaderTCPIP.mod', $pBack));
          }
          else
          {
           	$pBack = array('koMsg'=>Prado::localize('The device was not added'));
          	$this->Response->redirect($this->Service->constructUrl('hardware.device.accessLink_ReaderTCPIP.add',$pBack));        	          	
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
	  $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
	  $cmd->bindValue(":description",$this->comment->SafeText,PDO::PARAM_STR);
	  $cmd->bindValue(":isLog",$this->isLog->getChecked(),PDO::PARAM_STR);
	  $cmd->bindValue(":accessPlugin",$this->accessPlugin->SafeText,PDO::PARAM_STR);
	  $cmd->Execute();

	  $this->lastId = $this->db->getLastInsertID();
	  
	  
          $cmd = $this->db->createCommand( SQL::SQL_ADD_TCPIPREADER );
	  $cmd->bindValue(":ip",$this->ip->SafeText,PDO::PARAM_STR);
          $cmd->bindValue(":port",$this->port->SafeText,PDO::PARAM_STR);
	  $cmd->bindValue(":id_device",$this->lastId,PDO::PARAM_STR);
	  $cmd->bindValue(":outputTime1",$this->outputTime1->SafeText,PDO::PARAM_STR);
	  $cmd->bindValue(":outputTime2",$this->outputTime2->SafeText,PDO::PARAM_STR);
	  $cmd->bindValue(":outputTime3",$this->outputTime3->SafeText,PDO::PARAM_STR);
	  $cmd->bindValue(":outputTime4",$this->outputTime4->SafeText,PDO::PARAM_STR);
	  $cmd->bindValue(":antipassback",$this->antipassback->SafeText,PDO::PARAM_STR);

	  $cmd->bindValue(":open_mode",$this->open_mode->getSelectedValue(),PDO::PARAM_STR);
	  $cmd->bindValue(":open_mode_timeout",$this->open_mode_timeout->SafeText,PDO::PARAM_STR);
	  $cmd->bindValue(":open_mode_input",$this->open_mode_input->getSelectedValue(),PDO::PARAM_STR);

	  $cmd->Execute();

      
      return true;
	}     
	
    public function serverValidateName($sender, $param)
    {
      $cmd = $this->db->createCommand( SQL::SQL_IS_NAME_EXIST);
      $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
      $array = $cmd->query()->readAll();

      if(count($array) > 0)
        $param->IsValid=false;
      else 
        $param->IsValid=true;
	}

}
