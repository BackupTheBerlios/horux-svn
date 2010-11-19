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

class ModDevicePage extends Page
{
    protected $deviceName = "";
    protected $data = array();

    public function onLoad($param) {
        parent::onLoad($param);

        if(!$this->IsPostBack) {

            $userId=$this->Application->getUser()->getUserId();
            $this->blockRecord('hr_device', $this->Request['id'], $userId);

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

            $this->id->Value = $this->Request['id'];
            $this->setData();
        }
    }

    public function setData() {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_".$this->deviceName." AS ai LEFT JOIN hr_device AS d ON d.id=ai.id_device  WHERE ai.id_device=:id" );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        $data = array();

        if($query) {
            $data = $query->read();
            $this->name->Text = $data['name'];
            $this->isActive->setChecked($data['isActive']);
            $this->isLog->setChecked($data['isLog'] );
	    $this->horuxControllerId->setSelectedValue( $data['horuxControllerId'] );
            $this->parent->setSelectedValue( $data['parent_id'] );
            $this->accessPlugin->Text = $data['accessPlugin'];
            $this->comment->Text = $data['description'];
            $this->data = $data;
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
                $id = $this->id->Value;
                $pBack = array('okMsg'=>Prado::localize('The device was modified successfully'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('hardware.device.'.$this->deviceName.'.mod', $pBack));
            }
            else {
                $pBack = array('koMsg'=>Prado::localize('The device was not modified'));
            }
        }
    }

    public function onSave($sender, $param) {
        if($this->Page->IsValid) {
            if($this->saveData()) {
                $pBack = array('okMsg'=>Prado::localize('The device was modified successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The device was not modified'));

            $this->blockRecord('hr_device', $this->id->Value, 0);
            $this->Response->redirect($this->Service->constructUrl('hardware.HardwareList',$pBack));
        }
    }

    public function onCancel($sender, $param) {
        $this->blockRecord('hr_device', $this->id->Value, 0);
        $this->Response->redirect($this->Service->constructUrl('hardware.HardwareList'));
    }


    public function saveData() {
        
        $cmd = $this->db->createCommand(" UPDATE hr_device SET
                                            `name`=:name,
                                            `isLog`=:isLog, 
                                            `description`=:description, 
                                            `accessPlugin`=:accessPlugin,
                                            `horuxControllerId`=:horuxControllerId,
                                            `parent_id` = :parent_id,
                                            `isActive` = :isActive
                                            WHERE id=:id" );

        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":description",$this->comment->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":isLog",$this->isLog->getChecked(),PDO::PARAM_STR);
        $cmd->bindValue(":isActive",$this->isActive->getChecked(),PDO::PARAM_STR);
        $cmd->bindValue(":accessPlugin",$this->accessPlugin->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":horuxControllerId",$this->horuxControllerId->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":parent_id",$this->parent->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);

        $cmd->Execute();

    }

    public function serverValidateName($sender, $param) {
        $cmd = $this->db->createCommand("SELECT name FROM  hr_device WHERE type='".$this->deviceName."' AND name=:name AND id<>:id");
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
        $array = $cmd->query()->readAll();

        if(count($array) > 0)
            $param->IsValid=false;
        else
            $param->IsValid=true;
    }

}


?>