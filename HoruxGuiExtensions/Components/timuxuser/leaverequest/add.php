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

        $cmd->bindParameter(":userId",$this->userId,PDO::PARAM_STR);
        $cmd->bindParameter(":state",$this->status->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindParameter(":timecode",$this->timecode->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindParameter(":remark",$this->remark->Text,PDO::PARAM_STR);

        $res1 = $cmd->execute();
        $lastId = $this->db->LastInsertID;

        $cmd = $this->db->createCommand( "INSERT `hr_timux_request_leave` SET
                                          request_id=:request_id,
                                          datefrom=:datefrom,
                                          dateto=:dateto,
                                          period=:period
                                          ;" );

        $cmd->bindParameter(":request_id",$lastId,PDO::PARAM_STR);
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


        $res1 = $cmd->execute();

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

                    $cmd->bindParameter(":request_id",$lastId,PDO::PARAM_STR);
                    $cmd->bindParameter(":user_id",$s,PDO::PARAM_STR);
                    $cmd->execute();
                    
                    // @todo envoyer les emails
                }
            }

        }

        return $lastId;
    }


    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequest.leaverequest'));
    }
}
