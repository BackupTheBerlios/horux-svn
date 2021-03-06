<?php

class AddDevicePage extends Page
{
    protected $deviceName = "";
    protected $isAccessDevice = 1;
    protected $lastId;

    public function onLoad($param) {
        parent::onLoad($param);

        if(!$this->IsPostBack) {

            $this->horuxControllerId->setDataValueField('id');
            $this->horuxControllerId->setDataTextField('name');
            $this->horuxControllerId->DataSource=$this->Controller;
            $this->horuxControllerId->dataBind();
            $this->horuxControllerId->setSelectedIndex(0);

            $this->parent->setDataValueField('id');
            $this->parent->setDataTextField('name');
            $this->parent->DataSource=$this->Devices;
            $this->parent->dataBind();

            $param = $this->Application->getParameters();
            $superAdmin = $this->Application->getUser()->getSuperAdmin();

            if($param['appMode'] == 'demo' && $superAdmin == 0) {
                $this->Save->setEnabled(false);
                $this->Apply->setEnabled(false);
            }
        }
    }

    public function getDevices() {
        $command=$this->db->createCommand("SELECT * FROM hr_device");
        $data = $command->query();

        $d[] = array("id"=>0, 'name'=>Prado::localize("-- None --"));

        foreach($data as $dd) {
            $d[] = array("id"=>$dd['id'], 'name'=>$dd['name']);
        }

        return $d;
    }

    public function getController() {
        $command=$this->db->createCommand("SELECT * FROM hr_horux_controller");
        $data = $command->query();

        foreach($data as $dd) {
            $d[] = array("id"=>$dd['id'], 'name'=>$dd['name']);
        }

        return $d;
    }

    public function onApply($sender, $param) {
        if($this->Page->IsValid) {
            if($this->saveData()) {
                $id = $this->lastId;
                $pBack = array('okMsg'=>Prado::localize('The device was added successfully'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('hardware.device.'.$this->deviceName.'.mod', $pBack));
            }
            else {
                $pBack = array('koMsg'=>Prado::localize('The device was not added'));
                $this->Response->redirect($this->Service->constructUrl('hardware.device.'.$this->deviceName.'.add',$pBack));
            }
        }
    }

    public function onSave($sender, $param) {
        if($this->Page->IsValid) {
            if($this->saveData()) {
                $pBack = array('okMsg'=>Prado::localize('The device was added successfully'));
            }
            else {
                $pBack = array('koMsg'=>Prado::localize('The device was not saved'));
            }
            $this->Response->redirect($this->Service->constructUrl('hardware.HardwareList',$pBack));
        }
    }


    public function saveData() {
        
        $cmd = $this->db->createCommand( "INSERT INTO hr_device (
                                                `name` , 
                                                `accessPoint` , 
                                                `type` , 
                                                `isLog` , 
                                                `parent_id` , 
                                                `description`,
                                                `accessPlugin`,
                                                `horuxControllerId`
                                          )
                                          VALUES (
                                                :name,
                                                ".$this->isAccessDevice.",
                                                '".$this->deviceName."',
                                                :isLog,
                                                :parent_id,
                                                :description,
                                                :accessPlugin,
                                                :horuxControllerId
                                          )" );

        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":description",$this->comment->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":isLog",$this->isLog->getChecked(),PDO::PARAM_STR);
        $cmd->bindValue(":accessPlugin",$this->accessPlugin->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":horuxControllerId",$this->horuxControllerId->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":parent_id",$this->parent->getSelectedValue(),PDO::PARAM_STR);
        if($cmd->Execute())
            $this->lastId = $this->db->getLastInsertID();
        else
            $this->lastId = false;

    }

    public function serverValidateName($sender, $param) {
        $cmd = $this->db->createCommand("SELECT name FROM  hr_device WHERE type='".$this->deviceName."' AND name=:name");
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $array = $cmd->query()->readAll();

        if(count($array) > 0)
            $param->IsValid=false;
        else
            $param->IsValid=true;
    }

    public function onCancel($sender, $param) {
        $this->Response->redirect($this->Service->constructUrl('hardware.HardwareList'));
    }
}


?>