<?php
/**
 * @version      $Id$
 * @package      Horux
 * @subpackage   Horux
 * @copyright    Copyright (C) 2007  Letux. All rights reserved.
 * @license      GNU/GPL, see LICENSE.php
 * Horus is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

class addsubscription extends Page {
    public function onLoad($param) {
        parent::onLoad($param);

        if(!$this->isPostBack) {
            $sql = "SELECT devise FROM hr_site";
            $cmd = $this->db->createCommand( $sql );
            $res = $cmd->query();
            $res = $res->read();
            $this->devise->Text = $res["devise"];

            $this->multiple->DataSource = $this->Credit;
            $this->multiple->dataBind();

             $this->multiple->setSelectedIndex(0);

        }
    }


    public function getCredit()
    {
        $credits = array();

        for($i=1; $i<=50;$i++)
        {
           $credits[] = array('Text'=>$i, "Value"=>$i);
        }

        return $credits;
    }

    public function onApply($sender, $param) {
        if($this->Page->IsValid) {
            if($lastId = $this->saveData()) {
                $id = $lastId;
                $pBack = array('okMsg'=>Prado::localize('The subscription was added successfully'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('components.velopark.modsubscription', $pBack));
            }
            else {
                $pBack = array('koMsg'=>Prado::localize('The subscription was not added'));
            }
        }
    }

    public function onSave($sender, $param) {
        if($this->Page->IsValid) {
            if($this->saveData()) {
                $pBack = array('okMsg'=>Prado::localize('The subscriptio was added successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The subscription was not added'));
            $this->Response->redirect($this->Service->constructUrl('components.velopark.subscription',$pBack));
        }
    }

    public function onCancel($sender, $param) {
        $this->Response->redirect($this->Service->constructUrl('components.velopark.subscription'));
    }


    protected function saveData() {
        $cmd = $this->db->createCommand( "INSERT INTO `hr_vp_subscription` (`name` ,`description` ,`validity`, `credit`, `price`, `start`, `multiticket`, `VAT` ) VALUES (:name, :description, :validity, :credit, :price, :start,:multiticket, :vat)" );

        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":description",$this->description->SafeText, PDO::PARAM_STR);

        $validity = $this->year->getSelectedValue().":".$this->month->getSelectedValue().":".$this->day->getSelectedValue().":".$this->hour->getSelectedValue();

        $cmd->bindValue(":validity",$validity, PDO::PARAM_STR);
        $cmd->bindValue(":credit",$this->multiple->getSelectedValue(), PDO::PARAM_STR);
        $cmd->bindValue(":price",$this->price->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":vat",$this->vat->getSelectedValue(), PDO::PARAM_STR);



        $checked = 'immediatly';

        $cmd->bindValue(":start",$checked, PDO::PARAM_STR);

        $cmd->bindValue(":multiticket",$this->multipleticket->getChecked(), PDO::PARAM_STR);
        
        $cmd->execute();

        $lastId = $this->db->LastInsertID;

        return $lastId;
    }
} 
