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

class add extends Page
{

    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {

            $this->employee->DataSource = $this->PersonList;
            $this->employee->dataBind();


            if($this->employee->getItemCount() && $this->employee->getSelectedValue() == '')
            {
                $this->employee->setSelectedIndex(0);
            }

            $this->timecode->DataSource = $this->TimeCodeList;
            $this->timecode->dataBind();

            $this->timecode->setEnabled(false);

            if(isset($this->Request['date']))
            {
                $this->date->Text = $this->Request['date'];
            }

            if(isset($this->Request['userId']))
            {
                $this->employee->setSelectedValue($this->Request['userId']);
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
                $pBack = array('okMsg'=>Prado::localize('The sign was added successfully'), 'id'=>$id);

                if(isset($this->Request['back']))
                    $pBack['back'] = $this->Request['back'];

                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The sign was not added'));

                if(isset($this->Request['back']))
                    $pBack = $this->Request['back'];

                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.mod', $pBack));
            }
        }
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
            $this->timecode->setEnabled(false);
        else
        {
            $this->timecode->setEnabled(true);
            $cmd = NULL;
            if($this->sign->getSelectedValue() == '_IN')
                $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT('[',abbreviation, '] ', name) AS Text FROM hr_timux_timecode WHERE signtype='in' OR signtype='both'" );
            if($this->sign->getSelectedValue() == '_OUT')
                $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT('[',abbreviation, '] ', name) AS Text FROM hr_timux_timecode  WHERE signtype='out' OR signtype='both'" );

            $data =  $cmd->query();
            $data = $data->readAll();
            $d[0]['Value'] = 0;
            $d[0]['Text'] = Prado::localize('---- Choose a timecode ----');
            $data = array_merge($d, $data);
            $this->timecode->DataSource = $data;
            $this->timecode->dataBind();
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

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The sign was added successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The sign was not added'));
                

            if(isset($this->Request['back']))
                $this->Response->redirect($this->Service->constructUrl($this->Request['back'],$pBack));
            else
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.booking',$pBack));
        }
    }

    protected function saveData()
    {

        $cmd = $this->db->createCommand( "INSERT INTO `hr_tracking` (
                                            `id_user` ,
                                            `time`,
                                            `date`,
                                            `is_access`
                                            )
                                            VALUES (
                                            :id_user,
                                            :time,
                                            :date,
                                            '1'
                                            );" );

        $cmd->bindParameter(":id_user",$this->employee->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindParameter(":time",$this->time->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":date",$this->dateToSql( $this->date->SafeText ), PDO::PARAM_STR);

        $res1 = $cmd->execute();
        $lastId = $this->db->LastInsertID;

        $cmd = $this->db->createCommand( "INSERT INTO `hr_timux_booking` (
                                            `tracking_id` ,
                                            `action`,
                                            `roundBooking`,
                                            `actionReason`,
                                            `internet`
                                            )
                                            VALUES (
                                            :tracking_id,
                                            :action,
                                            :roundBooking,
                                            :actionReason,
                                            1
                                            );" );

        $cmd->bindParameter(":tracking_id",$lastId,PDO::PARAM_STR);
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

        return $lastId;
    }


    public function isNotClosed($sender,$param)
    {
        $date = explode("-",$this->date->SafeText);

        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_closed_month WHERE user_id=:id AND year=:year AND month=:month");
        $cmd->bindParameter(":id",$this->employee->getSelectedValue(), PDO::PARAM_INT);
        $cmd->bindParameter(":year",$date[2], PDO::PARAM_INT);
        $cmd->bindParameter(":month",$date[1], PDO::PARAM_INT);
        $query = $cmd->query();
        $query = $query->read();

        if($query)
            $param->IsValid=false;

    }

    public function onCancel($sender, $param)
    {
        if(isset($this->Request['back']))
            $this->Response->redirect($this->Service->constructUrl($this->Request['back']));
        else
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.booking'));
    }
}
