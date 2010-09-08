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

class mod extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->isPostBack)
        {
            $this->id->Value = $this->Request['id'];
            $this->setData();
        }
    }


    protected function setData()
    {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_timeclass WHERE id=:id");
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();

        if($query)
        {
            $data = $query->read();
            $this->id->Value = $data['id'];
            $this->name->Text = $data['name'];
            $this->fromHour->Text = $data['fromHour'];
            $this->toHour->Text = $data['toHour'];
            $this->multiplier->Text = $data['multiplier'];

        }
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($lastId = $this->saveData())
            {
                $id = $lastId;
                $pBack = array('okMsg'=>Prado::localize('The time class was modified successfully'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timeclasses.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The time class was not modified'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timeclasses.mod', $pBack));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The time class was modified successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The time class was not modified'));
            $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timeclasses.timeclasses',$pBack));
        }
    }

    protected function saveData()
    {


        $cmd = $this->db->createCommand( "UPDATE `hr_timux_timeclass` SET
                                            `name`=:name ,
                                            `multiplier`=:multiplier,
                                            `fromHour`=:fromHour,
                                            `toHour`=:toHour
                                            WHERE id=:id
                                            ;" );

        $cmd->bindValue(":name",$this->name->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":multiplier",$this->multiplier->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":fromHour",$this->fromHour->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":toHour",$this->toHour->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);

        return $cmd->execute();
    }


    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timeclasses.timeclasses'));
    }
}
