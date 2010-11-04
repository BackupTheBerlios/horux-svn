<?php
/**
* @version      $Id$
* @package      Horux
* @subpackage   Horux
* @copyright    Copyright (C) 2008  Letux. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* Horux is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

class mod extends Page
{
    protected $timeArray = array();
    protected $lastId = 0;

    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {
            $userId=$this->Application->getUser()->getUserId();
            $this->blockRecord('hr_horux_controller', $this->Request['id'], $userId);

            $this->id->Value = $this->Request['id'];
            $this->setData();
        }

        $param = $this->Application->getParameters();
        $superAdmin = $this->Application->getUser()->getSuperAdmin();

        if($param['appMode'] == 'demo' && $superAdmin == 0)
        {
              $this->tbb->Save->setEnabled(false);
              $this->tbb->apply->setEnabled(false);
        }
    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_horux_controller WHERE id=:id" );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            $this->id->Value = $data['id'];
            $this->name->Text = $data['name'];
            $this->comment->Text = $data['comment'];
            $this->ip->Text = $data['ip'];
            $this->type->setChecked( $data['type'] == 'master' );            
        }
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The Horux Controller was modified successfully'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('horuxController.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The Horux Controller was not modified'));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The Horux Controller was modified successfully'));
            }
            else
            $pBack = array('koMsg'=>Prado::localize('The Horux Controller was not modified'));

            $this->blockRecord('hr_horux_controller', $this->id->Value, 0);
            $this->Response->redirect($this->Service->constructUrl('horuxController.horuxController',$pBack));
        }
    }

    protected function saveData()
    {
        $cmd = $this->db->createCommand( "UPDATE hr_horux_controller SET name=:name, ip=:ip, type=:type, comment=:comment WHERE id=:id" );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":ip",$this->ip->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":type", $this->type->Checked ? 'master' : 'slave' ,PDO::PARAM_STR);
        $cmd->bindValue(":comment",$this->comment->SafeText,PDO::PARAM_STR);

        $res = $cmd->execute();

        $this->log("Modify the Horux Controller: ".$this->name->SafeText);

        return $res;
    }




    public function nameValidateIdentificator($sender, $param)
    {
        $cmd = $this->db->createCommand( SQL::SQL_IS_OPEN_TIME_NAME_EXIST);
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $array = $cmd->query()->readAll();

        if(count($array) > 0)
        $param->IsValid=false;
        else
        $param->IsValid=true;
    }

    public function onCancel($sender, $param)
    {
        $this->blockRecord('hr_horux_controller', $this->id->Value, 0);
        $this->Response->redirect($this->Service->constructUrl('horuxController.horuxController'));
    }

}
