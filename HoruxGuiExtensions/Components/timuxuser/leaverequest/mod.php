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

class mod extends Page
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


            $this->id->Value = $this->Request['id'];

            // Secure the id to be sure that the id correspond to the employee
            $cmd=$this->db->createCommand("SELECT * FROM hr_timux_request WHERE id=:id AND userId=:userId");
            $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);
            $cmd->bindParameter(":userId",$this->userId,PDO::PARAM_STR);
            $data = $cmd->query();
            $data = $data->readAll();
            if(count($data)==0)
            {
                $pBack = array('koMsg'=>Prado::localize('Cannot modify a leave request from an other employee'));
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequest.leaverequest', $pBack));
            }

            $this->setData();
        }
    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_request AS tr LEFT JOIN hr_timux_request_leave AS rl ON rl.request_id=tr.id WHERE tr.id=:id");
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();

        if($query)
        {
            $data = $query->read();

            $this->from->Text = $this->dateFromSql($data['datefrom']);

            if( $data['dateto'] != '0000-00-00')
                $this->to->Text = $this->dateFromSql($data['dateto']);

            switch($data['period'])
            {
                case 'allday':
                    $this->allday->setChecked(true);
                    break;
                case 'morning':
                    $this->morning->setChecked(true);
                    break;
                case 'afternoon':
                    $this->afternoon->setChecked(true);
                    break;
            }
            
            $this->timecode->setSelectedValue( $data['timecodeId'] );


            $status = array();
            if( $data['state'] == 'draft')
            {
                $status[] = array('Text'=>Prado::localize('Draft'), 'Value'=>'draft');
                $status[] = array('Text'=>Prado::localize('Sended'), 'Value'=>'sended');
            }

            if( $data['state'] == 'sended')
            {
                $status[] = array('Text'=>Prado::localize('Sended'), 'Value'=>'sended');
                $status[] = array('Text'=>Prado::localize('Canceled'), 'Value'=>'canceled');
            }

            if( $data['state'] == 'validating')
            {
                $status[] = array('Text'=>Prado::localize('Validating'), 'Value'=>'validating');
                $status[] = array('Text'=>Prado::localize('Canceled'), 'Value'=>'canceled');
            }

            if( $data['state'] == 'validate')
            {
                $status[] = array('Text'=>Prado::localize('Validate'), 'Value'=>'validate');
                $status[] = array('Text'=>Prado::localize('Canceled'), 'Value'=>'canceled');
            }

            if( $data['state'] == 'canceled')
            {
                $pBack = array('koMsg'=>Prado::localize('The leave request was canceled and cannot be modified'));
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequest.leaverequest', $pBack));
            }

            $this->status->DataSource = $status;
            $this->status->dataBind();

            $this->status->setSelectedValue( $data['state'] );
            

            $this->remark->Text = $data['remark'];

            if($data['state'] != 'draft')
            {
                $this->from->setEnabled(false);
                $this->from->setMode('Basic');
                $this->to->setEnabled(false);
                $this->to->setMode('Basic');
                $this->allday->setEnabled(false);
                $this->morning->setEnabled(false);
                $this->timecode->setEnabled(false);
                $this->afternoon->setEnabled(false);
                $this->remark->setVisible(false);
                $this->remark2->setVisible(true);
                $this->remark2->Text =  $data['remark'];
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
                $pBack = array('okMsg'=>Prado::localize('The leave request was modified successfully'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequest.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The leave request was not modified'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequest.mod', $pBack));
            }
        }
    }

    protected function getTimeCodeList()
    {
        $cmd = NULL;
        $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT('[',abbreviation, '] ', name) AS Text FROM hr_timux_timecode WHERE type='leave'" );
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
                $pBack = array('okMsg'=>Prado::localize('The leave request was modified successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The leave request was not modified'));

            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequest.leaverequest',$pBack));
        }
    }

    protected function saveData()
    {

        $cmd = $this->db->createCommand( "UPDATE `hr_timux_request` SET
                                          state=:state,
                                          timecodeId=:timecode,
                                          remark=:remark
                                          WHERE id=:id
                                          ;" );

        $cmd->bindParameter(":state",$this->status->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindParameter(":timecode",$this->timecode->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindParameter(":remark",$this->remark->Text,PDO::PARAM_STR);
        $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);

        $res1 = $cmd->execute();

        $cmd = $this->db->createCommand( "UPDATE `hr_timux_request_leave` SET
                                          datefrom=:datefrom,
                                          dateto=:dateto,
                                          period=:period
                                          WHERE request_id=:request_id
                                          ;" );

        $cmd->bindParameter(":request_id",$this->id->Value,PDO::PARAM_STR);
        $cmd->bindParameter(":datefrom",$this->dateToSql($this->from->SafeText),PDO::PARAM_STR);
        $cmd->bindParameter(":dateto",$this->dateToSql($this->to->SafeText),PDO::PARAM_STR);

        $period = "";

        if($this->allday->getChecked())
            $period = 'allday';
        if($this->morning->getChecked())
            $period = 'morning';
        if($this->afternoon->getChecked())
            $period = 'afternoon';

        $cmd->bindParameter(":period",$period,PDO::PARAM_STR);


        $res2 = $cmd->execute();

        if($this->status->getSelectedValue() == 'sended')
        {
            $department = $this->employee->getDepartmentId();

            $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_workflow WHERE departmentId=:id OR departmentId=0");
            $cmd->bindParameter(":id",$department, PDO::PARAM_INT);
            $query = $cmd->query();
            $data = $query->read();

            $v = array(0,0,0);

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
                        $v[] =  $data['validator3'];
                        $v[] =  $data['validator31'];
                        $v[] =  $data['validator32'];
                    }
                }
                else
                {
                    $v[] =  $data['validator2'];
                    $v[] =  $data['validator21'];
                    $v[] =  $data['validator22'];
                }

            }
            else
            {
                $v[] =  $data['validator1'];
                $v[] =  $data['validator11'];
                $v[] =  $data['validator12'];
            }

            foreach($v as $s)
            {
                if($s != 0)
                {

                    $cmd = $this->db->createCommand( "INSERT `hr_timux_request_workflow` SET
                                                      request_id=:request_id,
                                                      user_id=:user_id
                                                      ;" );

                    $cmd->bindParameter(":request_id",$this->id->Value,PDO::PARAM_STR);
                    $cmd->bindParameter(":user_id",$s,PDO::PARAM_STR);
                    $cmd->execute();

                    // @todo envoyer les emails
                }
            }

        }

        if($this->status->getSelectedValue() == 'canceled')
        {
            $cmd=$this->db->createCommand("DELETE FROM hr_timux_request_workflow WHERE request_id =:id");
            $cmd->bindParameter(":id",$this->id->Value);
            $cmd->execute();

            // @todo envoyer les emails

        }


        return $res1 || $res2;
    }


    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequest.leaverequest'));
    }
}
