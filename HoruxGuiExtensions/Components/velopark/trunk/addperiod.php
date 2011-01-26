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

class addperiod extends Page {
    public function onLoad($param) {
        parent::onLoad($param);

        if(!$this->isPostBack) {

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
        $this->Response->redirect($this->Service->constructUrl('components.velopark.period'));
    }


    protected function saveData() {
        $cmd = $this->db->createCommand( "INSERT INTO `hr_vp_period` (`name` ,`start` ,`end` ) VALUES (:name, :start, :end)" );

        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);

        $cmd->bindValue(":start",$this->start_hour->SafeText.":".$this->start_minute->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":end",$this->end_hour->SafeText.":".$this->end_minute->SafeText,PDO::PARAM_STR);

        $cmd->execute();

        $lastId = $this->db->LastInsertID;

        return $lastId;

    }
}
