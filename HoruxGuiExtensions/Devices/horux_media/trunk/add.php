<?php


Prado::using('horux.pages.hardware.device.horux_media.sql');

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
          $this->Response->redirect($this->Service->constructUrl('hardware.device.horux_media.mod', $pBack));
        }
        else
        {
              $pBack = array('koMsg'=>Prado::localize('The device was not added'));
              $this->Response->redirect($this->Service->constructUrl('hardware.device.horux_media.add',$pBack));        	          	
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
	  $cmd->Execute();

	  $this->lastId = $this->db->getLastInsertID();
  
          $cmd = $this->db->createCommand( SQL::SQL_ADD_INFODISPLAY );
	  $cmd->bindValue(":id_device",$this->lastId,PDO::PARAM_STR);
	  $cmd->bindValue(":ip",$this->ip->SafeText,PDO::PARAM_STR);
	  $cmd->bindValue(":port",$this->port->SafeText,PDO::PARAM_STR);
	  $cmd->bindValue(":id_action_device",$this->id_action_device->getSelectedValue(),PDO::PARAM_STR);

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
