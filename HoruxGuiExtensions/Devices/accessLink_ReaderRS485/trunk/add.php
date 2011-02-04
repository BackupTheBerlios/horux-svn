<?php

Prado::using('horux.pages.hardware.device.accessLink_ReaderRS485.sql');

class add extends Page
{
	protected $lastId;
    public function onLoad($param)
    {
        parent::onLoad($param);
        
        if(!$this->IsPostBack)
        {
			$this->interface_id->setDataValueField('id');
			$this->interface_id->setDataTextField('name');
            $this->interface_id->DataSource=$this->Interface;
            $this->interface_id->dataBind(); 

			$param = $this->Application->getParameters();
			$superAdmin = $this->Application->getUser()->getSuperAdmin();
	
			if($param['appMode'] == 'demo' && $superAdmin == 0)
			{
				$this->Save->setEnabled(false);
				$this->Apply->setEnabled(false);
			}           
        }
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
            $this->Response->redirect($this->Service->constructUrl('hardware.device.accessLink_ReaderRS485.mod', $pBack));
          }
          else
          {
           	$pBack = array('koMsg'=>Prado::localize('The device was not added'));
          	$this->Response->redirect($this->Service->constructUrl('hardware.device.accessLink_ReaderRS485.add',$pBack));        	          	
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
	  $cmd->bindParameter(":id_interface",$this->interface_id->getSelectedValue(),PDO::PARAM_STR);
	  $cmd->bindParameter(":accessPlugin",$this->accessPlugin->SafeText,PDO::PARAM_STR);
	  $cmd->Execute();

	  $this->lastId = $this->db->getLastInsertID();
  
	  $standalone = '200';
	  if($this->standalone_1->getChecked())
	  	$standalone = '200';
	  if($this->standalone_2->getChecked())
	  	$standalone = '1000';
	  
	  
      $cmd = $this->db->createCommand( SQL::SQL_ADD_RS485READER );
	  $cmd->bindParameter(":address",$this->address->getSelectedValue(),PDO::PARAM_STR);
	  $cmd->bindParameter(":id_device",$this->lastId,PDO::PARAM_STR);
	  $cmd->bindParameter(":memory",$standalone,PDO::PARAM_STR);
	  $cmd->bindParameter(":rtc",$this->rtc->getChecked(),PDO::PARAM_STR);
	  $cmd->bindParameter(":lcd",$this->lcd->getChecked(),PDO::PARAM_STR);
	  $cmd->bindParameter(":keyboard",$this->keyboard->getChecked(),PDO::PARAM_STR);
	  $cmd->bindParameter(":eeprom",$this->eeprom->getChecked(),PDO::PARAM_STR);
	  $cmd->bindParameter(":defaultText",$this->defaultText->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":outputTime1",$this->outputTime1->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":outputTime2",$this->outputTime2->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":outputTime3",$this->outputTime3->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":outputTime4",$this->outputTime4->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":antipassback",$this->antipassback->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":standalone",$this->standalone->getChecked(),PDO::PARAM_STR);

	  $cmd->bindParameter(":open_mode",$this->open_mode->getSelectedValue(),PDO::PARAM_STR);
	  $cmd->bindParameter(":open_mode_timeout",$this->open_mode_timeout->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":open_mode_input",$this->open_mode_input->getSelectedValue(),PDO::PARAM_STR);

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
