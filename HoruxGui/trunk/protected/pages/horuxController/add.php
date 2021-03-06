<?php

class add extends Page
{
    protected $timeArray = array();
    protected $lastId = 0;

    public function onLoad($param)
    {
        parent::onLoad($param);
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The Horux Controller was added successfully'), 'id'=>$this->lastId);
                $this->Response->redirect($this->Service->constructUrl('horuxController.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The Horux Controller was not added'));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The Horux Controller was added successfully'));
            }
            else
            $pBack = array('koMsg'=>Prado::localize('The Horux Controller was not added'));
            $this->Response->redirect($this->Service->constructUrl('horuxController.horuxController',$pBack));
        }
    }

    protected function saveData()
    {
        $cmd = $this->db->createCommand( "INSERT INTO hr_horux_controller (name, ip, type, comment) VALUES (:name, :ip, :type, :comment)" );
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":ip",$this->ip->safeText,PDO::PARAM_STR);
        $cmd->bindValue(":type", $this->type->Checked ? 'master' : 'slave' ,PDO::PARAM_STR);
        $cmd->bindValue(":comment",$this->comment->SafeText,PDO::PARAM_STR);

        $res = $cmd->execute();

        $this->log("Add the Horux Controller: ".$this->name->SafeText);

        return $res;
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('horuxController.horuxController'));
    }

}
