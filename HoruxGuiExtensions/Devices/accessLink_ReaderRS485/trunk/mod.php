<?php


Prado::using('horux.pages.hardware.device.accessLink_ReaderRS485.sql');

class mod extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        
        if(!$this->isPostBack)
        {

          $userId=$this->Application->getUser()->getUserId();
    	  $this->blockRecord('hr_device', $this->Request['id'], $userId);          
          
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

          $this->id->Value = $this->Request['id'];
          $this->setData();
          
        }        
    }

    public function getInterface()
    {
        $command=$this->db->createCommand(SQL::SQL_GET_INTERFACE);
        $data = $command->query();    	
        return $data;
    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_GET_RS485 );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();

        if($query)
        {
          $data = $query->read();
          $this->name->Text = $data['name'];
		  $this->accessPlugin->Text = $data['accessPlugin'];
		  $this->address->setSelectedValue( $data['address'] );
		  
		  if( $data['parent_id'] > 0)
		  	$this->interface_id->setSelectedValue( $data['parent_id'] );

		  $this->outputTime1->Text = $data['outputTime1'];
		  $this->outputTime2->Text = $data['outputTime2'];
		  $this->outputTime3->Text = $data['outputTime3'];
		  $this->comment->Text = $data['description'];
		  $this->lcd->setChecked( $data['lcd'] );
		  $this->rtc->setChecked( $data['rtc'] );
		  $this->keyboard->setChecked( $data['keyboard'] );
		  $this->eeprom->setChecked( $data['eeprom'] );
		  $this->defaultText->Text = $data['defaultText'];
		  $this->standalone_1->setChecked( $data['memory'] == 200);
		  $this->standalone_2->setChecked(  $data['memory'] == 1000 );
		  $this->outputTime4->Text = $data['outputTime4'] == 0 ? '' : $data['outputTime4'];
		  $this->isLog->setChecked($data['isLog'] );
		  $this->antipassback->Text = $data['antipassback'];
          $this->standalone->setChecked($data['standalone'] );

		  $this->open_mode->setSelectedValue( $data['open_mode'] );
		  $this->open_mode_timeout->Text = $data['open_mode_timeout'];
		  $this->open_mode_input->setSelectedValue( $data['open_mode_input'] );
          
        } 
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $id = $this->id->Value;
            $pBack = array('okMsg'=>Prado::localize('The reader was modified successfully'), 'id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('hardware.device.accessLink_ReaderRS485.mod', $pBack));
          }
          else
          {
            $pBack = array('koMsg'=>Prado::localize('The reader was not modified'));
          }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The reader was modified successfully'));
          }
          else
            $pBack = array('koMsg'=>Prado::localize('The reader was not modified'));
          
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
	  $cmd->bindParameter(":id_interface",$this->interface_id->getSelectedValue(),PDO::PARAM_STR);
	  $cmd->bindParameter(":accessPlugin",$this->accessPlugin->SafeText,PDO::PARAM_STR);
	  $cmd->Execute();

	  $standalone = '200';
	  if($this->standalone_1->getChecked())
	  	$standalone = '200';
	  if($this->standalone_2->getChecked())
	  	$standalone = '1000';
	  
	  
      $cmd = $this->db->createCommand( SQL::SQL_UPDATE_RS485READER );
	  $cmd->bindParameter(":address",$this->address->getSelectedValue(),PDO::PARAM_STR);
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
