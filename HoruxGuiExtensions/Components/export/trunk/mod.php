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


class mod extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {
            if(isset($this->Request['id']))
            {
                $userId=$this->Application->getUser()->getUserId();
                $this->blockRecord('hr_export', $this->Request['id'], $userId);

                $this->id->Value = $this->Request['id'];
                $this->setData();
            }

        }
    }

    protected function setData()
    {

        $cmd = $this->db->createCommand( SQL::SQL_GET_EXPORT );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            $this->name->Text = $data['name'];
            $this->sql->Text = $data['sql'];
            $this->description->Text = $data['description'];
        }

    }

    public function onCancel($sender, $param)
    {
        $this->blockRecord('hr_export', $this->id->Value, 0);
        $this->Response->redirect($this->Service->constructUrl('components.export.export'));
    }


    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {

                $pBack = array('okMsg'=>Prado::localize('The export was modified successfully'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.export.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The export was not modified'));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The export was modified successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The export was not modified'));

            $this->blockRecord('hr_export', $this->id->Value, 0);
            $this->Response->redirect($this->Service->constructUrl('components.export.export',$pBack));
        }
    }


    protected function saveData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_UPDATE_EXPORT );
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":sql",$this->sql->Text, PDO::PARAM_STR);
        $cmd->bindValue(":description",$this->description->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_STR);

        return $cmd->execute();
    }

}

?>
