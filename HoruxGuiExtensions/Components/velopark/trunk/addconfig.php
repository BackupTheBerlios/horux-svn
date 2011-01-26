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

class addconfig extends Page {
    public function onLoad($param) {
        parent::onLoad($param);

        if(!$this->isPostBack) {

            $this->accesspoint->DataSource = $this->Accesspoint;
            $this->accesspoint->dataBind();
        }
    }

    protected function getAccesspoint() {
        $cmd = $this->db->createCommand( "SELECT id AS Value, name AS Text FROM hr_device WHERE accessPoint=1" );
        $data =  $cmd->query();
        $data = $data->readAll();
        return $data;
    }
    
    public function onApply($sender, $param) {
        if($this->Page->IsValid) {
            if($lastId = $this->saveData()) {
                $id = $lastId;
                $pBack = array('okMsg'=>Prado::localize('The service was added successfully'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('components.velopark.modconfig', $pBack));
            }
            else {
                $pBack = array('koMsg'=>Prado::localize('The service was not added'));
            }
        }
    }

    public function onSave($sender, $param) {
        if($this->Page->IsValid) {
            if($this->saveData()) {
                $pBack = array('okMsg'=>Prado::localize('The service was added successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The service was not added'));
            $this->Response->redirect($this->Service->constructUrl('components.velopark.config',$pBack));
        }
    }

    public function onCancel($sender, $param) {
        $this->Response->redirect($this->Service->constructUrl('components.velopark.config'));
    }


    protected function saveData() {
        $cmd = $this->db->createCommand( "INSERT INTO `hr_vp_parking` (`name` ,`area` ,`access_unknown_msg`,`access_ko_msg`,`device_ids`,`access_credit_warning_msg`,`access_warning_msg`, `creditValue` ) VALUES (:name, :area, :access_unknown_msg,:access_ko_msg,:device_ids,:access_credit_warning_msg, :access_warning_msg, :creditValue)" );

        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":area",$this->area->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":access_unknown_msg",$this->access_unknown_msg->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":access_ko_msg",$this->access_ko_msg->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":access_credit_warning_msg",$this->access_credit_warning_msg->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":access_warning_msg",$this->access_warning_msg->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":creditValue",$this->credit_value->SafeText,PDO::PARAM_STR);


        $indices=$this->accesspoint->SelectedIndices;
        $result=array();
        foreach($indices as $index)
        {
            $item = $this->accesspoint->Items[$index];
            $result[] = $item->Value;
        }

        $cmd->bindValue(":device_ids",implode(',',$result),PDO::PARAM_STR);

        $cmd->execute();

        $lastId = $this->db->LastInsertID;

        return $lastId;

    }
} 
