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

Prado::using('horux.pages.accessLevel.sql');

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
                $pBack = array('okMsg'=>Prado::localize('The access level was added successfully'), 'id'=>$this->lastId);
                $this->Response->redirect($this->Service->constructUrl('accessLevel.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The access level was not added'));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The access level was added successfully'));
            }
            else
            $pBack = array('koMsg'=>Prado::localize('The access level was not added'));
            $this->Response->redirect($this->Service->constructUrl('accessLevel.accessLevelList',$pBack));
        }
    }

    protected function saveData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_ADD_ACCESS_LEVEL );
        $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":full_access",$this->fullAccess->Checked,PDO::PARAM_STR);
        $cmd->bindParameter(":non_working_day",$this->nonWorkingDayAccess->Checked,PDO::PARAM_STR);
        $cmd->bindParameter(":week_end",$this->weekEndAccess->Checked,PDO::PARAM_STR);
        $cmd->bindParameter(":monday_default",$this->mondayDefault->Checked,PDO::PARAM_STR);

        $from = $this->dateToSql($this->from->SafeText);
        $until = $this->dateToSql($this->until->SafeText);


        $cmd->bindParameter(":from",$from,PDO::PARAM_STR);
        $cmd->bindParameter(":until",$until,PDO::PARAM_STR);
        $cmd->bindParameter(":comment",$this->comment->SafeText,PDO::PARAM_STR);

        $res = $cmd->execute();

        if($res)
        {
            $this->lastId = $this->db->getLastInsertId();
            $this->timeArray = $this->getViewState('timeArray',array());
            foreach($this->timeArray as $time)
            {
                $this->saveTimeData($time['day'], $time['hourStart'], $time['duration'], $lastId);
            }
        }
        $this->log("Add the access level: ".$this->name->SafeText);

        return $lastId;
    }

    protected function saveTimeData($day, $hourStart, $duration ,$lastId)
    {
        switch($day)
        {
            case 0:
                $dayName = 'lundi';
                break;
            case 1:
                $dayName = 'mardi';
                break;
            case 2:
                $dayName = 'mercredi';
                break;
            case 3:
                $dayName = 'jeudi';
                break;
            case 4:
                $dayName = 'vendredi';
                break;
            case 5:
                $dayName = 'samedi';
                break;
            case 6:
                $dayName = 'dimanche';
                break;
        }

        $indexStartHours=explode(':',$hourStart);
        $indexEndHours=explode(':',$duration);
        $indexStartHours = ($indexStartHours[0]*60) + $indexStartHours[1];
        $indexEndHours= $indexStartHours + ($indexEndHours[0]*60) + $indexEndHours[1];

        $cmd = $this->db->createCommand( SQL::SQL_ADD_ACCESS_LEVEL_TIME );
        $cmd->bindParameter(":id_access_level",$lastId,PDO::PARAM_STR);
        $cmd->bindParameter(":day",$dayName,PDO::PARAM_STR);
        $cmd->bindParameter(":from",$indexStartHours,PDO::PARAM_INT);
        $cmd->bindParameter(":until",$indexEndHours,PDO::PARAM_INT);

        $cmd->execute();
    }

    protected function serverUntilValidate($sender, $param)
    {
        if( $this->until->SafeText == "" ) return;

        $until = strtotime($this->until->SafeText);
        $from = strtotime($this->from->SafeText);
        if($until<$from)
        $param->IsValid=false;
    }

    public function OnLoadAppointments($sender, $param)
    {
        $arrItems[] = array();
        $this->getResponse()->getAdapter()->setResponseData($arrItems);
    }

    public function OnSaveAppointment($sender, $param)
    {
        $this->timeArray = $this->getViewState('timeArray',array());

        $p = $param->getCallbackParameter()->CommandParameter;
        $this->timeArray[$p->id] = array("day"=> $p->day, "duration"=>$p->duration,"hourStart"=>$p->hour);

        $this->setViewState('timeArray',$this->timeArray,'');
    }

    public function OnDeleteAppointment($sender, $param)
    {
        $this->timeArray = $this->getViewState('timeArray',array());
        $p = $param->getCallbackParameter()->CommandParameter;
        unset($this->timeArray[$p->id]);
        $this->setViewState('timeArray',$this->timeArray,'');
    }

    public function nameValidateIdentificator($sender, $param)
    {
        $cmd = $this->db->createCommand( SQL::SQL_IS_ACCESS_LEVEL_NAME_EXIST);
        $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
        $array = $cmd->query()->readAll();

        if(count($array) > 0)
        $param->IsValid=false;
        else
        $param->IsValid=true;
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('accessLevel.accessLevelList'));
    }


}
