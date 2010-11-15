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

Prado::using('horux.pages.hardware.device.a3m_lgm.sql');

class add extends Page {
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
        $command=$this->db->createCommand(SQL::SQL_GET_DEVICES);
        $data = $command->query();

        $d[] = array("id"=>0, 'name'=>Prado::localize("-- None --"));

        foreach($data as $dd) {
            $d[] = array("id"=>$dd['id'], 'name'=>$dd['name']);
        }

        return $d;
    }

    public function getController() {
        $command=$this->db->createCommand(SQL::SQL_GET_CONTROLLER);
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
                $this->Response->redirect($this->Service->constructUrl('hardware.device.a3m_lgm.mod', $pBack));
            }
            else {
                $pBack = array('koMsg'=>Prado::localize('The device was not added'));
                $this->Response->redirect($this->Service->constructUrl('hardware.device.a3m_lgm.add',$pBack));
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

    public function onCancel($sender, $param) {
        $this->Response->redirect($this->Service->constructUrl('hardware.HardwareList'));
    }

    public function saveData() {
        $cmd = $this->db->createCommand( SQL::SQL_ADD_DEVICE );
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":description",$this->comment->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":isLog",$this->isLog->getChecked(),PDO::PARAM_STR);
        $cmd->bindValue(":accessPlugin",$this->accessPlugin->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":horuxControllerId",$this->horuxControllerId->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":parent_id",$this->parent->getSelectedValue(),PDO::PARAM_STR);
        $cmd->Execute();

        $this->lastId = $this->db->getLastInsertID();


        $cmd = $this->db->createCommand( SQL::SQL_ADD_DEVICE2 );
        $cmd->bindValue(":address",$this->address->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":id_device",$this->lastId,PDO::PARAM_STR);
        $cmd->bindValue(":serialNumberFormat",$this->serialNumberFormat->SafeText,PDO::PARAM_STR);

        $cmd->Execute();


        return true;
    }


    public function serverValidateName($sender, $param) {
        $cmd = $this->db->createCommand( SQL::SQL_IS_NAME_EXIST);
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $array = $cmd->query()->readAll();

        if(count($array) > 0)
            $param->IsValid=false;
        else
            $param->IsValid=true;
    }
}
