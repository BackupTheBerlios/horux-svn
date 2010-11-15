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

Prado::using('horux.pages.hardware.device.horux_rstcpip_converter.sql');

class mod extends Page {
    public function onLoad($param) {
        parent::onLoad($param);

        if(!$this->isPostBack) {

            $userId=$this->Application->getUser()->getUserId();
            $this->blockRecord('hr_device', $this->Request['id'], $userId);

            $this->horuxControllerId->setDataValueField('id');
            $this->horuxControllerId->setDataTextField('name');
            $this->horuxControllerId->DataSource=$this->Controller;
            $this->horuxControllerId->dataBind();
            $this->horuxControllerId->setSelectedIndex(0);

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

    public function getController() {
        $command=$this->db->createCommand(SQL::SQL_GET_CONTROLLER);
        $data = $command->query();

        foreach($data as $dd) {
            $d[] = array("id"=>$dd['id'], 'name'=>$dd['name']);
        }

        return $d;
    }

    protected function setData() {
        $cmd = $this->db->createCommand( SQL::SQL_GET_TCPIP );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();

        if($query) {
            $data = $query->read();
            $this->name->Text = $data['name'];
            $this->accessPlugin->Text = $data['accessPlugin'];
            $this->ip->Text =  $data['ip'];
            $this->port->Text =  $data['port'];
	    $this->horuxControllerId->setSelectedValue( $data['horuxControllerId'] );

            $this->comment->Text = $data['description'];
            $this->isLog->setChecked($data['isLog'] );

        }
    }

    public function onApply($sender, $param) {
        if($this->Page->IsValid) {
            if($this->saveData()) {
                $id = $this->id->Value;
                $pBack = array('okMsg'=>Prado::localize('The device was modified successfully'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('hardware.device.horux_rstcpip_converter.mod', $pBack));
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

    protected function saveData() {
        $cmd = $this->db->createCommand( SQL::SQL_MOD_DEVICE );
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":description",$this->comment->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":isLog",$this->isLog->getChecked(),PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
        $cmd->bindValue(":accessPlugin",$this->accessPlugin->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":horuxControllerId",$this->horuxControllerId->getSelectedValue(),PDO::PARAM_STR);

        $cmd->Execute();



        $cmd = $this->db->createCommand( SQL::SQL_UPDATE_DEVICE );
        $cmd->bindValue(":ip",$this->ip->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":port",$this->port->SafeText,PDO::PARAM_STR);        
        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
        $cmd->Execute();

        return true;
    }

    public function serverValidateName($sender, $param) {
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
