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

Prado::using('horux.pages.components.timuxuser.employee');

class add extends Page
{
    protected $userId = 0;
    protected $employee = null;

    public function onLoad($param)
    {
        parent::onLoad($param);

        $app = $this->getApplication();
        $usedId = $app->getUser()->getUserID() == null ? 0 : $app->getUser()->getUserID();

        $cmd=$this->db->createCommand("SELECT user_id FROM hr_superusers WHERE id=$usedId");
        $data = $cmd->query();
        $dataUser = $data->read();
        $this->userId = $dataUser['user_id'];
        $this->employee = new employee($this->userId );

        if(!$this->isPostBack)
        {

            $this->timecode->DataSource = $this->TimeCodeList;
            $this->timecode->dataBind();
            $this->timecode->setSelectedIndex(0);

            $this->user->DataSource = $this->EmployeeList;
            $this->user->dataBind();
            $this->user->setSelectedIndex(0);
        }
    }

    protected function getEmployeeList()
    {
        $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT(name, ' ', firstname) AS Text FROM hr_user WHERE name!='??' AND department>0" );
        $data =  $cmd->query();
        $data =  $data->readAll();
        $d[0]['Value'] = 'null';
        $d[0]['Text'] = Prado::localize('---- Choose a employee ----');
        $data = array_merge($d, $data);
        return $data;
    }


    protected function getTimeCodeList()
    {
        $cmd = NULL;
        $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT('[',abbreviation, '] ', name) AS Text FROM hr_timux_timecode" );
        $data =  $cmd->query();
        $data = $data->readAll();
        $d[0]['Value'] = 'null';
        $d[0]['Text'] = Prado::localize('---- Choose a timecode ----');
        $data = array_merge($d, $data);
        return $data;
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The leave was add successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The leave was not add'));

            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leave.leave',$pBack));
        }
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($lastId = $this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The leave was add successfully'), 'id'=>$lastId);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leave.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The leave was not add'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leave.add', $pBack));
            }
        }
    }

    protected function saveData()
    {
        $dateFrom = $this->dateToSql($this->from->SafeText);
        $dateto = $this->dateToSql($this->to->SafeText) == '' ? $dateFrom : $this->dateToSql($this->to->SafeText);


        if( ($this->monday->getChecked() &&
            $this->tuesday->getChecked() &&
            $this->wednesday->getChecked() &&
            $this->thursday->getChecked() &&
            $this->friday->getChecked() &&
            $this->saturday->getChecked() &&
            $this->sunday->getChecked()) ||
            ($dateFrom == $dateto)
        )
        {

            $cmd = $this->db->createCommand( "INSERT `hr_timux_request` SET
                                              userId=:userId,
                                              createDate=CURDATE(),
                                              modifyDate=CURDATE(),
                                              modifyUserId=:modifyUserId,
                                              state='validate',
                                              remark=:remark,
                                              timecodeId=:timecodeId
                                              ;" );

            $cmd->bindParameter(":userId",$this->user->getSelectedValue(),PDO::PARAM_STR);
            $cmd->bindParameter(":modifyUserId",$this->userId,PDO::PARAM_STR);
            $cmd->bindParameter(":remark",$this->remark->Text,PDO::PARAM_STR);
            $cmd->bindParameter(":timecodeId",$this->timecode->getSelectedValue(),PDO::PARAM_STR);
            $res1 = $cmd->execute();
            $lastId = $this->db->LastInsertID;

            $cmd = $this->db->createCommand( "INSERT `hr_timux_request_leave` SET
                                              request_id=:request_id,
                                              datefrom=:datefrom,
                                              dateto=:dateto,
                                              period=:period
                                              ;" );

            $cmd->bindParameter(":request_id",$lastId,PDO::PARAM_STR);
            $cmd->bindParameter(":datefrom",$dateFrom,PDO::PARAM_STR);


            $cmd->bindParameter(":dateto",$dateto,PDO::PARAM_STR);

            $period = "";

            if($this->allday->getChecked())
                $period = 'allday';
            if($this->morning->getChecked())
                $period = 'morning';
            if($this->afternoon->getChecked())
                $period = 'afternoon';

            $cmd->bindParameter(":period",$period,PDO::PARAM_STR);
            $res2 = $cmd->execute();

            return $lastId;
        }
        else
        {
            while($dateFrom != $dateto)
            {
                $dayN = date('N', strtotime($dateFrom));

                $mustBeInserted = false;
                switch($dayN)
                {
                    case 1: //monday
                        $mustBeInserted = $this->monday->getChecked();
                        break;
                    case 2: //tuesday
                        $mustBeInserted = $this->tuesday->getChecked();
                        break;
                    case 3: //wednesday
                        $mustBeInserted = $this->wednesday->getChecked();
                        break;
                    case 4: //thursday
                        $mustBeInserted = $this->thursday->getChecked();
                        break;
                    case 5: //friday
                        $mustBeInserted = $this->friday->getChecked();
                        break;
                    case 6: //saturday
                        $mustBeInserted = $this->saturday->getChecked();
                        break;
                    case 7: //sunday
                        $mustBeInserted = $this->sunday->getChecked();
                        break;
                }

                if($mustBeInserted)
                {
                    $cmd = $this->db->createCommand( "INSERT `hr_timux_request` SET
                                                      userId=:userId,
                                                      createDate=CURDATE(),
                                                      modifyDate=CURDATE(),
                                                      modifyUserId=:modifyUserId,
                                                      state='validate',
                                                      remark=:remark,
                                                      timecodeId=:timecodeId
                                                      ;" );

                    $cmd->bindParameter(":userId",$this->user->getSelectedValue(),PDO::PARAM_STR);
                    $cmd->bindParameter(":modifyUserId",$this->userId,PDO::PARAM_STR);
                    $cmd->bindParameter(":remark",$this->remark->Text,PDO::PARAM_STR);
                    $cmd->bindParameter(":timecodeId",$this->timecode->getSelectedValue(),PDO::PARAM_STR);
                    $res1 = $cmd->execute();
                    $lastId = $this->db->LastInsertID;

                    $cmd = $this->db->createCommand( "INSERT `hr_timux_request_leave` SET
                                                      request_id=:request_id,
                                                      datefrom=:datefrom,
                                                      dateto=:dateto,
                                                      period=:period
                                                      ;" );

                    $cmd->bindParameter(":request_id",$lastId,PDO::PARAM_STR);
                    $cmd->bindParameter(":datefrom",$dateFrom,PDO::PARAM_STR);


                    $cmd->bindParameter(":dateto",$dateFrom,PDO::PARAM_STR);

                    $period = "";

                    if($this->allday->getChecked())
                        $period = 'allday';
                    if($this->morning->getChecked())
                        $period = 'morning';
                    if($this->afternoon->getChecked())
                        $period = 'afternoon';

                    $cmd->bindParameter(":period",$period,PDO::PARAM_STR);
                    $res2 = $cmd->execute();

                    if(!$res2)
                        return false;
                }

                $dateFrom = date("Y-m-d",strtotime(date("Y-m-d", strtotime($dateFrom)) . " +1 day"));
            }

            return $lastId;
        }
    }


    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leave.leave'));
    }
}
