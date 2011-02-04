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
            $this->status->setSelectedIndex(0);

            $this->timecode->DataSource = $this->TimeCodeList;
            $this->timecode->dataBind();


            if($this->timecode->getItemCount() && $this->timecode->getSelectedValue() == '')
            {
                $this->timecode->setSelectedIndex(0);
            }
        }
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($lastId = $this->saveData())
            {
                $id = $lastId;
                $pBack = array('okMsg'=>Prado::localize('The leave request was added successfully'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequest.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The leave request was not added'));
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequest.mod', $pBack));
            }
        }
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
                $pBack = array('okMsg'=>Prado::localize('The leave request was added successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The leave request was not added'));

            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequest.leaverequest',$pBack));
        }
    }

    protected function saveData()
    {

        $cmd = $this->db->createCommand( "INSERT `hr_timux_request` SET
                                          userId=:userId,
                                          type='leave',
                                          state=:state,
                                          createDate=CURDATE(),
                                          modifyDate='0000-00-00',
                                          modifyUserId=0,
                                          timecodeId=:timecode,
                                          remark=:remark
                                          ;" );

        $cmd->bindValue(":userId",$this->userId,PDO::PARAM_STR);
        $cmd->bindValue(":state",$this->status->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":timecode",$this->timecode->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":remark",$this->remark->Text,PDO::PARAM_STR);

        $res1 = $cmd->execute();
        $lastId = $this->db->LastInsertID;

        $cmd = $this->db->createCommand( "INSERT `hr_timux_request_leave` SET
                                          request_id=:request_id,
                                          datefrom=:datefrom,
                                          dateto=:dateto,
                                          period=:period
                                          ;" );

        $cmd->bindValue(":request_id",$lastId,PDO::PARAM_STR);
        $cmd->bindValue(":datefrom",$this->dateToSql($this->from->SafeText),PDO::PARAM_STR);

        $dateto = $this->dateToSql($this->to->SafeText) == '' ? $this->dateToSql($this->from->SafeText) : $this->dateToSql($this->to->SafeText);

        $cmd->bindValue(":dateto",$dateto,PDO::PARAM_STR);

        $period = "";

        if($this->allday->getChecked())
            $period = 'allday';
        if($this->morning->getChecked())
            $period = 'morning';
        if($this->afternoon->getChecked())
            $period = 'afternoon';

        $cmd->bindValue(":period",$period,PDO::PARAM_STR);


        $res1 = $cmd->execute();

        if($this->status->getSelectedValue() == 'sended')
        {
            $department = $this->employee->getDepartmentId();
            
            $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_workflow WHERE departmentId=:id OR departmentId=0");
            $cmd->bindValue(":id",$department, PDO::PARAM_INT);
            $query = $cmd->query();
            $data = $query->read();

            $v = array(0,0,0);
            $level = 1;

            if( $data['validator1'] == $this->userId || $data['validator11'] == $this->userId || $data['validator12'] == $this->userId)
            {
                if( $data['validator2'] == $this->userId || $data['validator21'] == $this->userId || $data['validator22'] == $this->userId)
                {
                    if( $data['validator3'] == $this->userId || $data['validator31'] == $this->userId || $data['validator32'] == $this->userId)
                    {
                        // @todo PROBLEM AUCUN VALIDATOR POSSIBLE
                    }
                    else
                    {
                        $v[0] =  $data['validator3'];
                        $v[1] =  $data['validator31'];
                        $v[2] =  $data['validator32'];
                        $level = 3;
                    }
                }
                else
                {
                    $v[0] =  $data['validator2'];
                    $v[1] =  $data['validator21'];
                    $v[2] =  $data['validator22'];
                    $level = 2;
                }
                
            }
            else
            {
                $v[0] =  $data['validator1'];
                $v[1] =  $data['validator11'];
                $v[2] =  $data['validator12'];
                $level = 1;
            }

            foreach($v as $s)
            {
                if($s != 0)
                {

                    $cmd = $this->db->createCommand( "INSERT `hr_timux_request_workflow` SET
                                                      request_id=:request_id,
                                                      user_id=:user_id,
                                                      validatorLevel=:validatorLevel
                                                      ;" );

                    $cmd->bindValue(":request_id",$lastId,PDO::PARAM_STR);
                    $cmd->bindValue(":user_id",$s,PDO::PARAM_STR);
                    $cmd->bindValue(":validatorLevel",$level,PDO::PARAM_STR);
                    $cmd->execute();
                    
               }
            }


            $this->sendEmail($lastId);

        }

        return $lastId;
    }


    protected function sendEmail($lastId)
    {
        $cmd=$this->db->createCommand("SELECT * FROM hr_timux_request_workflow WHERE request_id=:id");
        $cmd->bindValue(":id",$lastId);
        $query = $cmd->query();
        $data = $query->readAll();

        $mailer = new TMailer();
        foreach($data as $d)
        {
            $user_id = $d['user_id'];

            $cmd=$this->db->createCommand("SELECT u.email1, u.email2, su.email AS email3 FROM hr_user AS u LEFT JOIN hr_superusers AS su ON su.user_id=u.id WHERE u.id=:id");
            $cmd->bindValue(":id",$user_id);
            $query = $cmd->query();
            $data2 = $query->read();

            if($data2['email1'] != '' || $data2['email2'] != '' || $data2['email3'] != '')
            {
                if($data2['email2'] != '')
                {
                    $mailer->addRecipient($data2['email2']);
                }
                elseif($data2['email3'] != '')
                {
                    $mailer->addRecipient($data2['email3']);
                }
                elseif($data2['email1'] != '')
                {
                    $mailer->addRecipient($data2['email1']);
                }

            }
        }
        $mailer->setObject(Prado::localize("New Leave request"));

        $body = Prado::localize("A new leave request from {name} was added in your validation task<br/><br/>Timux", array('name'=>$this->employee->getFullName()));
        $mailer->setBody($body);
        $mailer->sendHtmlMail();

    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequest.leaverequest'));
    }

    public function isNotClosed($sender,$param)
    {
        $date = explode("-",$this->from->SafeText);

        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE user_id=:id AND year=:year AND month=:month");
        $cmd->bindValue(":id",$this->userId, PDO::PARAM_INT);
        $cmd->bindValue(":year",$date[2], PDO::PARAM_INT);
        $cmd->bindValue(":month",$date[1], PDO::PARAM_INT);
        $query = $cmd->query();
        $query = $query->read();

        if($query)
            $param->IsValid=false;

    }
}
