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
            $this->id->Value = $this->Request['id'];
            $this->setData();
        }
    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_tracking AS t LEFT JOIN hr_timux_booking AS tb ON t.id=tb.tracking_id WHERE t.id=:id");
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();

        if($query)
        {
            $data = $query->read();

            if(!$data['internet'])
            {
                $pBack = array('koMsg'=>Prado::localize('Cannot modified this physical sign'));

                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.booking',$pBack));
            }

            $this->id->Value = $data['id'];
            $this->date->Text = $this->dateFromSql($data['date']);
            $this->time->Text = $data['roundBooking'];

            if($data['action'] == '254' || $data['action'] == '255')
                $this->sign->setSelectedValue($data['action']);

            if($data['action'] == '100')
            {
                $ar = explode('_', $data['actionReason']);

                if(count($ar)>1)
                {
                    $this->sign->setSelectedValue("_".$ar[1]);
                }
                else
                {
                    $cmd2=$this->db->createCommand("SELECT *  FROM hr_timux_timecode WHERE id=".$ar[0]);

                    $data2 = $cmd2->query();
                    $data2 = $data2->read();

                    if($data2['signtype'] == 'in')
                    {
                        $this->sign->setSelectedValue("_IN");
                    }
                    if($data2['signtype'] == 'out')
                    {
                        $this->sign->setSelectedValue("_OUT");
                    }

                }
            }

            $this->onSignChange(NULL,NULL);

            if($data['action'] == '100')
            {
                $ar = explode('_', $data['actionReason']);

                $this->timecode->setSelectedValue($ar[0]);
            }
        }
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The sign was modified successfully'), 'id'=>$this->id->Value);

                if(isset($this->Request['back']))
                    $pBack = $this->Request['back'];
                    
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.mybooking.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The sign was not modified'), 'id'=>$this->id->Value);

                if(isset($this->Request['back']))
                    $pBack = $this->Request['back'];

                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.mybooking.mod', $pBack));
            }
        }
    }

    protected function getPersonList()
    {
        $cmd = NULL;
        $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT(name, ' ', firstname) AS Text FROM hr_user WHERE name<>'??' AND department>0" );
        $data =  $cmd->query();
        $data = $data->readAll();
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
        $d[0]['Value'] = 0;
        $d[0]['Text'] = Prado::localize('---- Choose a timecode ----');
        $data = array_merge($d, $data);
        return $data;
    }


    public function onSignChange($sender, $param)
    {
        if($this->sign->getSelectedValue() == 254 || $this->sign->getSelectedValue() == 255 )
        {
            $this->timecode->setEnabled(false);
        }
        else
        {
            $this->timecode->setEnabled(true);
            $cmd = NULL;
            if($this->sign->getSelectedValue() == '_IN')
            {
                $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT('[',abbreviation, '] ', name) AS Text FROM hr_timux_timecode WHERE signtype='in' OR signtype='both'" );
            }
            if($this->sign->getSelectedValue() == '_OUT')
            {
                $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT('[',abbreviation, '] ', name) AS Text FROM hr_timux_timecode  WHERE signtype='out' OR signtype='both'" );
            }

            $data =  $cmd->query();
            $data = $data->readAll();
            $d[0]['Value'] = 0;
            $d[0]['Text'] = Prado::localize('---- Choose a timecode ----');
            $data = array_merge($d, $data);
            $this->timecode->DataSource = $data;
            $this->timecode->dataBind();
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The sign was modified successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The sign was not modified'));

            if(isset($this->Request['back']))
                $this->Response->redirect($this->Service->constructUrl($this->Request['back'],$pBack));
            else
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.mybooking.mybooking',$pBack));
        }
    }

    protected function saveData()
    {

        $cmd = $this->db->createCommand( "UPDATE `hr_tracking` SET
                                            `time` = :time,
                                            `date` = :date
                                            WHERE id=:id" );

        $cmd->bindParameter(":time",$this->time->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":date",$this->dateToSql( $this->date->SafeText ), PDO::PARAM_STR);
        $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);

        $res1 = $cmd->execute();

        $cmd = $this->db->createCommand( "UPDATE`hr_timux_booking` SET
                                            `action` = :action,
                                            `actionReason`=:actionReason,
                                            `roundBooking` = :roundBooking,
                                            `internet`=1
                                            WHERE tracking_id=:id" );

        $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);
        $action = $this->sign->getSelectedValue();
        if($this->sign->getSelectedValue() == '_IN' || $this->sign->getSelectedValue() == '_OUT')
        {
            $action = 100;
            $cmd->bindParameter(":action",$action, PDO::PARAM_STR);
            
            $cmd2=$this->db->createCommand("SELECT *  FROM hr_timux_timecode WHERE id=".$this->timecode->getSelectedValue());

            $data2 = $cmd2->query();
            $data2 = $data2->read();

            if($data2['signtype'] == 'both')
            {
                $actionReason = $this->timecode->getSelectedValue().$this->sign->getSelectedValue();
            }
            else
            {
                $actionReason = $this->timecode->getSelectedValue();
            }
            $cmd->bindParameter(":actionReason",$actionReason, PDO::PARAM_STR);
        }
        else
        {
            $actionReason = 0;
            $cmd->bindParameter(":action",$action, PDO::PARAM_STR);
            $cmd->bindParameter(":actionReason",$actionReason, PDO::PARAM_STR);

        }

        $cmd->bindParameter(":roundBooking",$this->time->SafeText, PDO::PARAM_STR);

        $res1 = $cmd->execute();

        return $res1;
    }


    public function onCancel($sender, $param)
    {
        if(isset($this->Request['back']))
            $this->Response->redirect($this->Service->constructUrl($this->Request['back']));
        else
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.mybooking.mybooking'));
    }
}
