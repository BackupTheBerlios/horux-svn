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

Prado::using('horux.pages.components.export.sql');


class modImport extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {
            if(isset($this->Request['id']))
            {
                $userId=$this->Application->getUser()->getUserId();
                $this->blockRecord('hr_import', $this->Request['id'], $userId);

                $this->id->Value = $this->Request['id'];
                $this->setData();
            }

        }
    }

    protected function setData()
    {

        $cmd = $this->db->createCommand( SQL::SQL_GET_IMPORT );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            $this->name->Text = $data['name'];
            $this->tb_name->Text = $data['tb_name'];
            $this->cols->Text = $data['cols'];
            $this->terminated_by->Text = $data['terminated_by'];
            $this->enclosed_by->Text = $data['enclosed_by'];
            $this->escaped_by->Text = $data['escaped_by'];
            $this->description->Text = $data['description'];
        }

    }

    public function onCancel($sender, $param)
    {
        $this->blockRecord('hr_import', $this->id->Value, 0);
        $this->Response->redirect($this->Service->constructUrl('components.export.import'));
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The import configuration was modified successfully'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.export.modImport', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The import configuration was not modified'));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The import configuration was modified successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The import configuration was not modified'));

            if(isset($this->Request['id']))
                $this->blockRecord('hr_import', $this->id->Value, 0);
            $this->Response->redirect($this->Service->constructUrl('components.export.import',$pBack));
        }
    }

    protected function saveData()
    {
        if ($this->id->Value != "") {
            $cmd = $this->db->createCommand( SQL::SQL_UPDATE_IMPORT );
            $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_STR);
        }
        else
            $cmd = $this->db->createCommand( SQL::SQL_ADD_IMPORT );
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":tb_name",$this->tb_name->Text, PDO::PARAM_STR);
        $cmd->bindValue(":cols",$this->cols->Text, PDO::PARAM_STR);
        $cmd->bindValue(":terminated_by",$this->terminated_by->Text, PDO::PARAM_STR);
        $cmd->bindValue(":enclosed_by",$this->enclosed_by->Text, PDO::PARAM_STR);
        $cmd->bindValue(":escaped_by",$this->escaped_by->Text, PDO::PARAM_STR);
        $cmd->bindValue(":description",$this->description->SafeText, PDO::PARAM_STR);
        

        return $cmd->execute();
    }

}

?>
