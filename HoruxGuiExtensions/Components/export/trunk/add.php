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


class add extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.export.export'));
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($lastId = $this->saveData())
            {
                $id = $lastId;
                $pBack = array('okMsg'=>Prado::localize('The export was added successfully'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('components.export.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The export was not added'));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The export was added successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The export was not added'));
            $this->Response->redirect($this->Service->constructUrl('components.export.export',$pBack));
        }
    }


    protected function saveData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_ADD_EXPORT );
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":sql",$this->sql->Text, PDO::PARAM_STR);
        $cmd->bindValue(":description",$this->description->SafeText, PDO::PARAM_STR);

        if(!$cmd->execute())
            return false;

        return $this->db->LastInsertID;
    }


}
?>
