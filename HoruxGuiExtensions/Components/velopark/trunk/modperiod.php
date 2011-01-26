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

class modperiod extends Page {
    public function onLoad($param) {
        parent::onLoad($param);

        if(!$this->isPostBack) {
                $userId=$this->Application->getUser()->getUserId();
                $this->blockRecord('hr_vp_period', $this->Request['id'], $userId);
                $this->id->Value = $this->Request['id'];
                $this->setData();
        }
    }

    protected function setData() {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_vp_period WHERE id=:id" );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query) {
            $data = $query->read();
            $this->id->Value = $data['id'];
            $this->name->Text = $data['name'];

            $start = explode(":",$data['start']);
            $end = explode(":", $data['end']);

            $this->start_hour->Text = $start[0];
            $this->start_minute->Text = $start[1];

            $this->end_hour->Text = $end[0];
            $this->end_minute->Text = $end[1];

        }
    }

    public function onApply($sender, $param) {
        if($this->Page->IsValid) {
            if($lastId = $this->saveData()) {
                $id = $lastId;
                $pBack = array('okMsg'=>Prado::localize('The period was added successfully'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('components.velopark.modperiod', $pBack));
            }
            else {
                $pBack = array('koMsg'=>Prado::localize('The period was not added'));
            }
        }
    }

    public function onSave($sender, $param) {
        if($this->Page->IsValid) {
            if($this->saveData()) {
                $pBack = array('okMsg'=>Prado::localize('The period was added successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The period was not added'));
            $this->Response->redirect($this->Service->constructUrl('components.velopark.period',$pBack));
        }
    }

    public function onCancel($sender, $param) {
        $this->blockRecord('hr_vp_period', $this->id->Value, 0);
        $this->Response->redirect($this->Service->constructUrl('components.velopark.period'));
    }


    protected function saveData() {
        $cmd = $this->db->createCommand( "UPDATE `hr_vp_period` SET `name`=:name ,`start`=:start ,`end`=:end WHERE id=:id" );

        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_STR);
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);

        $cmd->bindValue(":start",$this->start_hour->SafeText.":".$this->start_minute->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":end",$this->end_hour->SafeText.":".$this->end_minute->SafeText,PDO::PARAM_STR);

        $cmd->execute();


        return true;

    }
}
