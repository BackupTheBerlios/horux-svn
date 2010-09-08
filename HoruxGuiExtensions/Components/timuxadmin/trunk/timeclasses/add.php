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

class add extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($lastId = $this->saveData())
            {
                $id = $lastId;
                $pBack = array('okMsg'=>Prado::localize('The time class was added successfully'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timeclasses.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The time class was not added'));
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
                $pBack = array('okMsg'=>Prado::localize('The time class was added successfully'));
            }
            else
            $pBack = array('koMsg'=>Prado::localize('The time class was not added'));
            $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timeclasses.timeclasses',$pBack));
        }
    }

    protected function saveData()
    {

        $cmd = $this->db->createCommand( "INSERT INTO `hr_timux_timeclass` (
                                            `name` ,
                                            `multiplier`,
                                            `fromHour`,
                                            `toHour`
                                            )
                                            VALUES (
                                            :name,
                                            :multiplier,
                                            :fromHour,
                                            :toHour
                                            );" );

        $cmd->bindValue(":name",$this->name->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":multiplier",$this->multiplier->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":fromHour",$this->fromHour->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":toHour",$this->toHour->SafeText, PDO::PARAM_STR);

        $res1 = $cmd->execute();
        $lastId = $this->db->LastInsertID;


        return $lastId;
    }


    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timeclasses.timeclasses'));
    }
}
