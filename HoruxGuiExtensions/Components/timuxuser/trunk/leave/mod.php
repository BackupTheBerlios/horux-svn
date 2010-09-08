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

$param = Prado::getApplication()->getParameters();
$computation = $param['computation'];

Prado::using('horux.pages.components.timuxuser.'.$computation);

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
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();

        if($query)
        {
            $data = $query->read();

            if($data['state'] == 'closed')
            {
                $pBack = array('koMsg'=>Prado::localize('Cannot modify a closed leave'));
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leave.leave',$pBack));
            }

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

             $this->status->setSelectedValue( $data['state'] );


            $this->remark->Text = $data['remark'];

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

    public function onDelete($sender, $param)
    {
        $cmd=$this->db->createCommand("DELETE FROM hr_timux_request WHERE id =:id");
        $cmd->bindValue(":id",$this->id->Value);
        if($cmd->execute())
        {
            $nDelete++;
        }
        $cmd=$this->db->createCommand("DELETE FROM hr_timux_request_leave WHERE request_id =:id");
        $cmd->bindValue(":id",$this->id->Value);
        $cmd->execute();
        $cmd=$this->db->createCommand("DELETE FROM hr_timux_request_workflow WHERE request_id =:id");
        $cmd->bindValue(":id",$this->id->Value);

        $pBack = array('okMsg'=>Prado::localize('{n} leave was deleted',array('n'=>$nDelete)));


        if($this->Request['back'])
            $this->Response->redirect($this->Service->constructUrl($this->Request['back'], $pBack));
        else
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leave.leave', $pBack));
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The leave was modified successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The leave was not modified'));

            if($this->Request['back'])
                $this->Response->redirect($this->Service->constructUrl($this->Request['back'],$pBack));
            else
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leave.leave',$pBack));
        }
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The leave was modified successfully'), 'id'=>$this->id->Value);

                if($this->Request['back'])
                    $pBack['back'] = $this->Request['back'];

                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leave.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The leave was not modified'), 'id'=>$this->id->Value);

                if($this->Request['back'])
                    $pBack['back'] = $this->Request['back'];

                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leave.mod', $pBack));
            }
        }
    }

    protected function saveData()
    {

        $cmd = $this->db->createCommand( "UPDATE `hr_timux_request` SET
                                          modifyDate=CURDATE(),
                                          modifyUserId=:modifyUserId,
                                          state=:state,
                                          remark=:remark,
                                          timecodeId=:timecodeId
                                          WHERE id=:id
                                          ;" );

        $cmd->bindValue(":modifyUserId",$this->userId,PDO::PARAM_STR);

        if($this->myremark->Text != "")
        {
            $remark = $this->remark->Text;
            $remark .= "<hr>";
            $remark .= $this->myremark->Text;
        }
        else
            $remark = $this->remark->Text;
        
        $cmd->bindValue(":remark",$remark,PDO::PARAM_STR);
        $cmd->bindValue(":state",$this->status->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":timecodeId",$this->timecode->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
        $res1 = $cmd->execute();

        $cmd = $this->db->createCommand( "UPDATE `hr_timux_request_leave` SET
                                          datefrom=:datefrom,
                                          dateto=:dateto,
                                          period=:period
                                          WHERE request_id=:request_id
                                          ;" );

        $cmd->bindValue(":request_id",$this->id->Value,PDO::PARAM_STR);
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
        $res2 = $cmd->execute();

        return $res1 || $res2;
    }


    public function onCancel($sender, $param)
    {
        if($this->Request['back'])
            $this->Response->redirect($this->Service->constructUrl($this->Request['back']));
        else
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leave.leave'));
    }
}
