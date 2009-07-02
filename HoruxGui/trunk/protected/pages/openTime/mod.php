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

Prado::using('horux.pages.openTime.sql');

class mod extends Page
{
    protected $timeArray = array();

    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {
            $userId=$this->Application->getUser()->getUserId();
            $this->blockRecord('hr_openTime', $this->Request['id'], $userId);

            $this->id->Value = $this->Request['id'];
            $this->setData();
        }
    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_GET_OPEN_TIME_ID );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            $this->id->Value = $data['id'];
            $this->name->Text = $data['name'];
            $this->comment->Text = $data['comment'];
            $this->nonWorkingDayAccess->setChecked($data['non_working_day']);
            $this->weekEndAccess->setChecked($data['week_end']);
            $this->mondayDefault->setChecked($data['monday_default']);

            if($data['validity_date'] != '0000-00-00' && $data['validity_date'] != NULL)
            {
                $this->from->Text = $this->dateFromSql($data['validity_date']);
            }

            if($data['validity_date_to'] != '0000-00-00'  && $data['validity_date_to'] != NULL)
            {
                $this->until->Text = $this->dateFromSql($data['validity_date_to']);
            }
        }
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $id = $this->id->Value;
                $pBack = array('okMsg'=>Prado::localize('The open time was modified successfully'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('openTime.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The open time was not added'));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The open time was modified successfully'));
            }
            else
            $pBack = array('koMsg'=>Prado::localize('The open time was not modified'));

            $this->blockRecord('hr_openTime', $this->id->Value, 0);
            $this->Response->redirect($this->Service->constructUrl('openTime.openTimeList',$pBack));
        }
    }

    public function onCancel($sender, $param)
    {
        $this->blockRecord('hr_openTime', $this->id->Value, 0);
        $this->Response->redirect($this->Service->constructUrl('openTime.openTimeList'));
    }

    protected function saveData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_MOD_OPEN_TIME );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":non_working_day",$this->nonWorkingDayAccess->Checked,PDO::PARAM_STR);
        $cmd->bindParameter(":week_end",$this->weekEndAccess->Checked,PDO::PARAM_STR);
        $cmd->bindParameter(":monday_default",$this->mondayDefault->Checked,PDO::PARAM_STR);

        $from = $this->dateToSql($this->from->SafeText);
        $until = $this->dateToSql($this->until->SafeText);

        $cmd->bindParameter(":from",$from,PDO::PARAM_STR);
        $cmd->bindParameter(":until",$until,PDO::PARAM_STR);
        $cmd->bindParameter(":comment",$this->comment->SafeText,PDO::PARAM_STR);

        $res2 = $cmd->execute();

        $cmd = $this->db->createCommand( SQL::SQL_REMOVE_OPEN_TIME_TIME );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $res = $cmd->execute();

        $this->timeArray = $this->getViewState('timeArray',array());
        foreach($this->timeArray as $time)
        {
            $this->saveTimeData($time['day'], $time['hourStart'], $time['duration'], $this->Request['id']);
        }

        return $res || $res2;
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

        $cmd = $this->db->createCommand( SQL::SQL_ADD_OPEN_TIME_TIME );
        $cmd->bindParameter(":id_openTime",$lastId,PDO::PARAM_STR);
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
        $cmd = $this->db->createCommand( SQL::SQL_GET_OPEN_TIME_TIME_ID );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->readAll();
            $arrItems = array();
            $days['lundi'] = 0;
            $days['mardi'] = 1;
            $days['mercredi'] = 2;
            $days['jeudi'] = 3;
            $days['vendredi'] = 4;
            $days['samedi'] = 5;
            $days['dimanche'] = 6;
            foreach($data as $d)
            {
                $from = str_pad((int)($d['from'] / 60),2,'0',STR_PAD_LEFT).':'.str_pad(($d['from'] % 60),2,'0',STR_PAD_LEFT);
                $duration = $d['until'] - $d['from'];
                $duration = str_pad((int)($duration / 60),2,'0',STR_PAD_LEFT).':'.str_pad(($duration % 60),2,'0',STR_PAD_LEFT);

                $arrItems[] = array('id' => $d['id'],
                         'day' => $days[$d['day']],
                         'hour' => $from,
                         'duration' => $duration);

                $this->timeArray[$d['id']] = array("day"=> $days[$d['day']], "duration"=>$duration,"hourStart"=>$from);
            }
            $this->setViewState('timeArray',$this->timeArray,'');
            $this->getResponse()->getAdapter()->setResponseData($arrItems);
        }
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
        $cmd = $this->db->createCommand( SQL::SQL_IS_OPEN_TIME_NAME_EXIST_EXCEPT_ID);
        $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":id",$this->Request['id'],PDO::PARAM_STR);
        $array = $cmd->query()->readAll();

        if(count($array) > 0)
        $param->IsValid=false;
        else
        $param->IsValid=true;
    }
}
