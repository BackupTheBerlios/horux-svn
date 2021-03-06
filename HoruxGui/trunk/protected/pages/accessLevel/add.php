<?php

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
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":full_access",$this->fullAccess->Checked,PDO::PARAM_STR);
        $cmd->bindValue(":non_working_day",$this->nonWorkingDayAccess->Checked,PDO::PARAM_STR);
        $cmd->bindValue(":monday_default",$this->mondayDefault->Checked,PDO::PARAM_STR);

        $cmd->bindValue(":comment",$this->comment->SafeText,PDO::PARAM_STR);

        $res = $cmd->execute();

        if($res)
        {
            $this->lastId = $this->db->getLastInsertId();
            $this->timeArray = $this->getViewState('timeArray',array());
            foreach($this->timeArray as $time)
            {
                $this->saveTimeData($time['day'], $time['hourStart'], $time['duration'], $this->lastId, $time['pinCode'], $time['exitingOnly'], $time['specialRelayPlan']);
            }
        }
        
        $this->log("Add the access level: ".$this->name->SafeText);

        return $res;
    }

    protected function saveTimeData($day, $hourStart, $duration ,$lastId, $pinCode, $exitingOnly, $specialRelayPlan)
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
        $cmd->bindValue(":id_access_level",$lastId,PDO::PARAM_STR);
        $cmd->bindValue(":day",$dayName,PDO::PARAM_STR);
        $cmd->bindValue(":from",$indexStartHours,PDO::PARAM_INT);
        $cmd->bindValue(":until",$indexEndHours,PDO::PARAM_INT);
        $cmd->bindValue(":pinCodeNecessary", $pinCode);
        $cmd->bindValue(":specialRelayPlan", $specialRelayPlan);
        $cmd->bindValue(":exitingOnly", $exitingOnly);
        
        $cmd->execute();
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
        $this->timeArray[$p->id] = array("day"=> $p->day,
                                         "duration"=>$p->duration,
                                         "hourStart"=>$p->hour,
                                         "pinCode"=>$p->pinCode,
                                         "exitingOnly"=>$p->exitingOnly,
                                         "specialRelayPlan"=>$p->specialRelayPlan
                                        );

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
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
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
