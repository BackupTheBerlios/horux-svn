<?php

Prado::using('horux.pages.hardware.device.accessLink_Interface.sql');

class mod extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {
          $userId=$this->Application->getUser()->getUserId();
    	  $this->blockRecord('hr_device', $this->Request['id'], $userId); 	
        
          $this->id->Value = $this->Request['id'];
          $this->setData();
          
			$param = $this->Application->getParameters();
			$superAdmin = $this->Application->getUser()->getSuperAdmin();
	
			if($param['appMode'] == 'demo' && $superAdmin == 0)
			{
				$this->Save->setEnabled(false);
				$this->Apply->setEnabled(false);
			}           
          
        }
    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_GET_INTERFACE );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();

        if($query)
        {
          $data = $query->read();
          $this->name->Text = $data['name'];
          $this->ip->Text = $data['ip'];
          $this->mask->Text = $data['mask'];
          $this->gateway->Text = $data['gateway'];
          $this->server1->Text = $data['server1'];
          $this->server2->Text = $data['server2'];
          $this->server3->Text = $data['server3'];
          $this->temp_max->Text = $data['temp_max'];
          $this->comment->Text = $data['description'];
          $this->data_port->Text = $data['data_port'];
          $this->isLog->setChecked($data['isLog']);
        } 
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The interface was modified successfully'), 'id'=>$this->id->Value);
            $this->Response->redirect($this->Service->constructUrl('hardware.device.accessLink_Interface.mod', $pBack));
          }
          else
          {
            $this->displayMessage(Prado::localize('The interface was not modified'), false);
          }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The interface was modified successfully'));
          }
          else
          	$pBack = array('koMsg'=>Prado::localize('The interface was not modified'));

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

      //! if the user don't give an ip address for s2 and s3, take the value from s1
      if(empty($this->server2->SafeText))
              $this->server2->Text = $this->server1->SafeText; 
      if(empty($this->server3->SafeText))
              $this->server3->Text = $this->server1->SafeText;
  
      $cmd = $this->db->createCommand( SQL::SQL_MOD_INTERFACE );
      $cmd->bindParameter(":ip",$this->ip->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":mask",$this->mask->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":gateway",$this->gateway->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":data_port",$this->data_port->SafeText, PDO::PARAM_INT);
      $cmd->bindParameter(":server1",$this->server1->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":server2",$this->server2->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":server3",$this->server3->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":temp_max",$this->temp_max->SafeText, PDO::PARAM_INT);
      $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
      $cmd->execute();
      
      $cmd = $this->db->createCommand( SQL::SQL_MOD_DEVICE );
      $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":description",$this->comment->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":isLog",$this->isLog->getChecked(),PDO::PARAM_STR);
      $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
          
      return $cmd->execute();

    }

    public function serverValidateName($sender, $param)
    {
      $cmd = $this->db->createCommand( SQL::SQL_IS_NAME_EXIST_EXCEPT_ID);
      $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);
      $array = $cmd->query()->readAll();

      if(count($array) > 0)
        $param->IsValid=false;
      else 
        $param->IsValid=true;
}

    public function serverValidateIp($sender, $param)
    {
      $cmd = $this->db->createCommand( SQL::SQL_IS_IP_EXIST_EXCEPT_ID);
      $cmd->bindParameter(":ip",$this->ip->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);
      $array = $cmd->query()->readAll();

      if(count($array) > 0)
        $param->IsValid=false;
      else 
        $param->IsValid=true;
    }

}
