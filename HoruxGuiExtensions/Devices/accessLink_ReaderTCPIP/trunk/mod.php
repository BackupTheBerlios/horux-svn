<?php

Prado::using('horux.pages.hardware.device.accessLink_ReaderTCPIP.sql');

class mod extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        
        if(!$this->isPostBack)
        {

          $userId=$this->Application->getUser()->getUserId();
    	  $this->blockRecord('hr_device', $this->Request['id'], $userId);          
          
            
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
        $cmd = $this->db->createCommand( SQL::SQL_GET_TCPIP );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();

        if($query)
        {
          $data = $query->read();
          $this->name->Text = $data['name'];
		  $this->accessPlugin->Text = $data['accessPlugin'];
		  $this->ip->Text =  $data['ip'];
                  $this->port->Text =  $data['port'];
		  
		  $this->outputTime1->Text = $data['outputTime1'];
		  $this->outputTime2->Text = $data['outputTime2'];
		  $this->outputTime3->Text = $data['outputTime3'];
		  $this->comment->Text = $data['description'];
		  $this->outputTime4->Text = $data['outputTime4'] == 0 ? '' : $data['outputTime4'];
		  $this->isLog->setChecked($data['isLog'] );
		  $this->antipassback->Text = $data['antipassback'];

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
            $pBack = array('okMsg'=>Prado::localize('The device was modified successfully'), 'id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('hardware.device.accessLink_ReaderTCPIP.mod', $pBack));
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
	  $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
	  $cmd->bindValue(":description",$this->comment->SafeText,PDO::PARAM_STR);
	  $cmd->bindValue(":isLog",$this->isLog->getChecked(),PDO::PARAM_STR);
	  $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
	  $cmd->bindValue(":accessPlugin",$this->accessPlugin->SafeText,PDO::PARAM_STR);
	  $cmd->Execute();

	  
	  
          $cmd = $this->db->createCommand( SQL::SQL_UPDATE_TCPIPREADER );
	  $cmd->bindValue(":ip",$this->ip->SafeText,PDO::PARAM_STR);
          $cmd->bindValue(":port",$this->port->SafeText,PDO::PARAM_STR);
	  $cmd->bindValue(":outputTime1",$this->outputTime1->SafeText,PDO::PARAM_STR);
	  $cmd->bindValue(":outputTime2",$this->outputTime2->SafeText,PDO::PARAM_STR);
	  $cmd->bindValue(":outputTime3",$this->outputTime3->SafeText,PDO::PARAM_STR);
	  $cmd->bindValue(":outputTime4",$this->outputTime4->SafeText,PDO::PARAM_STR);
	  $cmd->bindValue(":antipassback",$this->antipassback->SafeText,PDO::PARAM_STR);
	  $cmd->bindValue(":open_mode",$this->open_mode->getSelectedValue(),PDO::PARAM_STR);
	  $cmd->bindValue(":open_mode_timeout",$this->open_mode_timeout->SafeText,PDO::PARAM_STR);
	  $cmd->bindValue(":open_mode_input",$this->open_mode_input->getSelectedValue(),PDO::PARAM_STR);

	  $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
	  $cmd->Execute();
	  
	  return true;
    }

    public function serverValidateName($sender, $param)
    {
      $cmd = $this->db->createCommand( SQL::SQL_IS_READER_NAME_EXIST2);
      $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
      $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
      $array = $cmd->query()->readAll();

      if(count($array) > 0)
        $param->IsValid=false;
      else 
        $param->IsValid=true;
	}


}
