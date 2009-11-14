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

            $this->setData();
        }
    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( "SELECT tr.*, CONCAT(u.name, ' ', u.firstname ) AS employee, rl.* FROM hr_timux_request AS tr LEFT JOIN hr_timux_request_leave AS rl ON rl.request_id=tr.id LEFT JOIN hr_user AS u ON u.id=tr.userId WHERE tr.id=:id");
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();

        if($query)
        {
            $data = $query->read();

            $this->user->Text = $data['employee'];
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

            $status[] = array('Text'=>Prado::localize('Sended'), 'Value'=>'sended');
            $status[] = array('Text'=>Prado::localize('Validating'), 'Value'=>'validating');

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
                $this->status->setEnabled(false);
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
                $pBack = array('okMsg'=>Prado::localize('The leave request validation was modified successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The leave request validation was not modified'));

            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequestvalidation.leaverequestvalidation',$pBack));
        }
    }

    protected function saveData()
    {

        $cmd = $this->db->createCommand( "UPDATE `hr_timux_request` SET
                                          modifyDate=CURDATE(),
                                          modifyUserId=:modifyUserId,
                                          state=:state,
                                          remark=:remark
                                          WHERE id=:id
                                          ;" );

        $cmd->bindParameter(":modifyUserId",$this->userId,PDO::PARAM_STR);

        $remark = $this->remark->Text;
        $remark .= "<hr>";
        $remark .= $this->myremark->Text;

        $cmd->bindParameter(":remark",$remark,PDO::PARAM_STR);

        $validation = '';

        if($this->refuse->getChecked())
            $validation = 'refused';

        if($this->validate->getChecked())
        {

            $cmd2 = $this->db->createCommand( "SELECT u.department FROM hr_timux_request AS tr LEFT JOIN hr_timux_request_leave AS rl ON rl.request_id=tr.id LEFT JOIN hr_user AS u ON u.id=tr.userId WHERE tr.id=:id");
            $cmd2->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
            $query = $cmd2->query();
            $data = $query->read();
            $department = $data['department'];

            $cmd2 = $this->db->createCommand( "SELECT * FROM hr_timux_workflow WHERE departmentId=:id OR departmentId=0");
            $cmd2->bindParameter(":id",$department, PDO::PARAM_INT);
            $query = $cmd2->query();
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

            $isNextValidator = false;
            foreach($v as $s)
            {
                if($s != 0)
                    $isNextValidator = true;

            }

            if($isNextValidator)
            {
                $cmd2=$this->db->createCommand("DELETE FROM hr_timux_request_workflow WHERE request_id =:id");
                $cmd2->bindParameter(":id",$this->id->Value);
                $cmd2->execute();

                foreach($v as $s)
                {
                    if($s != 0)
                    {
                        $cmd2 = $this->db->createCommand( "INSERT `hr_timux_request_workflow` SET
                                                          request_id=:request_id,
                                                          user_id=:user_id
                                                          ;" );

                        $cmd2->bindParameter(":request_id",$this->id->Value,PDO::PARAM_STR);
                        $cmd2->bindParameter(":user_id",$s,PDO::PARAM_STR);
                        $cmd2->execute();

                        // @todo envoyer les emails
                    }
                }

                $validation = 'validating';
            }
            else
                $validation = 'validate';

        }

        $cmd->bindParameter(":state",$validation,PDO::PARAM_STR);


        $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);

        $res1 = $cmd->execute();


        if($validation == 'refused')
        {
            $cmd=$this->db->createCommand("DELETE FROM hr_timux_request_workflow WHERE request_id =:id");
            $cmd->bindParameter(":id",$this->id->Value);
            $cmd->execute();

            // @todo envoyer les emails

        }


        return $res1;
    }


    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequestvalidation.leaverequestvalidation'));
    }
}
