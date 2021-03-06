<?php


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
        $cmd = $this->db->createCommand( SQL::SQL_GET_OPEN_TIME_ID );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            $this->id->Value = $data['id'];
            $this->name->Text = $data['name'];
            $this->comment->Text = $data['comment'];
            $this->nonWorkingDayAccess->setChecked($data['non_working_day']);
            $this->mondayDefault->setChecked($data['monday_default']);
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
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":non_working_day",$this->nonWorkingDayAccess->Checked,PDO::PARAM_STR);
        $cmd->bindValue(":monday_default",$this->mondayDefault->Checked,PDO::PARAM_STR);

        $cmd->bindValue(":comment",$this->comment->SafeText,PDO::PARAM_STR);

        $res2 = $cmd->execute();

        $cmd = $this->db->createCommand( SQL::SQL_REMOVE_OPEN_TIME_TIME );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $res = $cmd->execute();

        $this->timeArray = $this->getViewState('timeArray',array());
        foreach($this->timeArray as $time)
        {
            $this->saveTimeData($time['day'], $time['hourStart'], $time['duration'], $this->Request['id']
                                , $time['unlocking'], $time['supOpenTooLongAlarm'], $time['supWithoutPermAlarm'], $time['checkOnlyCompanyID'], $time['specialRelayPlan']);
        }

        return $res || $res2;
    }

    protected function saveTimeData($day, $hourStart, $duration ,$lastId, $unlocking, $supOpenTooLongAlarm, $supWithoutPermAlarm, $checkOnlyCompanyID, $specialRelayPlan)
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
        $cmd->bindValue(":id_openTime",$lastId,PDO::PARAM_STR);
        $cmd->bindValue(":day",$dayName,PDO::PARAM_STR);
        $cmd->bindValue(":from",$indexStartHours,PDO::PARAM_INT);
        $cmd->bindValue(":until",$indexEndHours,PDO::PARAM_INT);
        $cmd->bindValue(":unlocking",$unlocking,PDO::PARAM_INT);
        $cmd->bindValue(":supOpenTooLongAlarm",$supOpenTooLongAlarm,PDO::PARAM_INT);
        $cmd->bindValue(":supWithoutPermAlarm",$supWithoutPermAlarm,PDO::PARAM_INT);
        $cmd->bindValue(":checkOnlyCompanyID",$checkOnlyCompanyID,PDO::PARAM_INT);
        $cmd->bindValue(":specialRelayPlan",$specialRelayPlan,PDO::PARAM_INT);


        $cmd->execute();
    }

    public function OnLoadAppointments($sender, $param)
    {
        $cmd = $this->db->createCommand( SQL::SQL_GET_OPEN_TIME_TIME_ID );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
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
                         'duration' => $duration,
                         'unlocking' => $d['unlocking'],
                         'supOpenTooLongAlarm' => $d['supOpenTooLongAlarm'],
                         'supWithoutPermAlarm' => $d['supWithoutPermAlarm'],
                         'checkOnlyCompanyID' => $d['checkOnlyCompanyID'],
                         'specialRelayPlan' => $d['specialRelayPlan'],
                        );

                $this->timeArray[$d['id']] = array("day"=> $days[$d['day']],
                                                   "duration"=>$duration,
                                                   "hourStart"=>$from,
                                                   "unlocking"=>$d['unlocking'],
                                                   "supOpenTooLongAlarm"=>$d['supOpenTooLongAlarm'],
                                                   "supWithoutPermAlarm"=>$d['supWithoutPermAlarm'],
                                                   "checkOnlyCompanyID"=>$d['checkOnlyCompanyID'],
                                                   "specialRelayPlan"=>$d['specialRelayPlan'],
                                                  );
            }
            $this->setViewState('timeArray',$this->timeArray,'');
            $this->getResponse()->getAdapter()->setResponseData($arrItems);
        }
    }

    public function OnSaveAppointment($sender, $param)
    {
        $this->timeArray = $this->getViewState('timeArray',array());

        $p = $param->getCallbackParameter()->CommandParameter;
        $this->timeArray[$p->id] = array("day"=> $p->day,
                                         "duration"=>$p->duration,
                                         "hourStart"=>$p->hour,
                                         "unlocking"=>$p->unlocking,
                                         "supOpenTooLongAlarm"=>$p->supOpenTooLongAlarm,
                                         "supWithoutPermAlarm"=>$p->supWithoutPermAlarm,
                                         "checkOnlyCompanyID"=>$p->checkOnlyCompanyID,
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
        $cmd = $this->db->createCommand( SQL::SQL_IS_OPEN_TIME_NAME_EXIST_EXCEPT_ID);
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->Request['id'],PDO::PARAM_STR);
        $array = $cmd->query()->readAll();

        if(count($array) > 0)
        $param->IsValid=false;
        else
        $param->IsValid=true;
    }
}
