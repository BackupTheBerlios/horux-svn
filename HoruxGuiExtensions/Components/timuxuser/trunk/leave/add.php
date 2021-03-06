<?php


$param = Prado::getApplication()->getParameters();
$computation = $param['computation'];

Prado::using('horux.pages.components.timuxuser.'.$computation);

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

            $cmd->bindValue(":userId",$this->user->getSelectedValue(),PDO::PARAM_STR);
            $cmd->bindValue(":modifyUserId",$this->userId,PDO::PARAM_STR);
            $cmd->bindValue(":remark",$this->remark->Text,PDO::PARAM_STR);
            $cmd->bindValue(":timecodeId",$this->timecode->getSelectedValue(),PDO::PARAM_STR);
            $res1 = $cmd->execute();
            $lastId = $this->db->LastInsertID;

            $cmd = $this->db->createCommand( "INSERT `hr_timux_request_leave` SET
                                              request_id=:request_id,
                                              datefrom=:datefrom,
                                              dateto=:dateto,
                                              period=:period
                                              ;" );

            $cmd->bindValue(":request_id",$lastId,PDO::PARAM_STR);
            $cmd->bindValue(":datefrom",$dateFrom,PDO::PARAM_STR);


            $cmd->bindValue(":dateto",$dateto,PDO::PARAM_STR);

            $period = "";

            if($this->allday->getChecked())
                $period = 'allday';
            if($this->morning->getChecked())
                $period = 'morning';
            if($this->afternoon->getChecked())
                $period = 'afternoon';

            $cmd->bindValue(":period",$period,PDO::PARAM_STR);
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

                    $cmd->bindValue(":userId",$this->user->getSelectedValue(),PDO::PARAM_STR);
                    $cmd->bindValue(":modifyUserId",$this->userId,PDO::PARAM_STR);
                    $cmd->bindValue(":remark",$this->remark->Text,PDO::PARAM_STR);
                    $cmd->bindValue(":timecodeId",$this->timecode->getSelectedValue(),PDO::PARAM_STR);
                    $res1 = $cmd->execute();
                    $lastId = $this->db->LastInsertID;

                    $cmd = $this->db->createCommand( "INSERT `hr_timux_request_leave` SET
                                                      request_id=:request_id,
                                                      datefrom=:datefrom,
                                                      dateto=:dateto,
                                                      period=:period
                                                      ;" );

                    $cmd->bindValue(":request_id",$lastId,PDO::PARAM_STR);
                    $cmd->bindValue(":datefrom",$dateFrom,PDO::PARAM_STR);


                    $cmd->bindValue(":dateto",$dateFrom,PDO::PARAM_STR);

                    $period = "";

                    if($this->allday->getChecked())
                        $period = 'allday';
                    if($this->morning->getChecked())
                        $period = 'morning';
                    if($this->afternoon->getChecked())
                        $period = 'afternoon';

                    $cmd->bindValue(":period",$period,PDO::PARAM_STR);
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

    public function isNotClosed($sender,$param)
    {
        $date = explode("-",$this->from->SafeText);

        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE user_id=:id AND year=:year AND month=:month");
        $cmd->bindValue(":id",$this->user->getSelectedValue(), PDO::PARAM_INT);
        $cmd->bindValue(":year",$date[2], PDO::PARAM_INT);
        $cmd->bindValue(":month",$date[1], PDO::PARAM_INT);
        $query = $cmd->query();
        $query = $query->read();

        if($query)
            $param->IsValid=false;

    }
}
