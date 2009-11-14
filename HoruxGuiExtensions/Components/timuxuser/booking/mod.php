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

class mod extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {

            $this->employee->DataSource = $this->PersonList;
            $this->employee->dataBind();

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
            $this->id->Value = $data['id'];
            $this->employee->setSelectedValue($data['id_user']);
            $this->date->Text = $this->dateFromSql($data['date']);
            $this->time->Text = $data['roundBooking'];

            if($data['action'] == '500' || $data['action'] == '502' || $data['action'] == '255')
                $this->sign->setSelectedValue('502');

            if($data['action'] == '501' || $data['action'] == '503' || $data['action'] == '254')
                $this->sign->setSelectedValue('503');

            if($data['action'] == '100')
            {
                $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_absencecode WHERE id=:id");
                $cmd->bindParameter(":id",$data['actionReason'], PDO::PARAM_INT);
                $query = $cmd->query();
                $data = $query->read();

                if($data['type'] == 'in')
                    $this->sign->setSelectedValue('502');
                else
                    $this->sign->setSelectedValue('503');
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
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The sign was not modified'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.mod', $pBack));
            }
        }
    }

    protected function getPersonList()
    {
        $cmd = NULL;
        $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT(name, ' ', firstname) AS Text FROM hr_user WHERE name<>'??'" );
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
                $pBack = array('okMsg'=>Prado::localize('The sign was modified successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The sign was not modified'));

            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.booking',$pBack));
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
                                            `roundBooking` = :roundBooking
                                            WHERE tracking_id=:id" );

        $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);
        $cmd->bindParameter(":action",$this->sign->getSelectedValue(), PDO::PARAM_STR);
        $cmd->bindParameter(":roundBooking",$this->time->SafeText, PDO::PARAM_STR);

        $res1 = $cmd->execute();

        return $res1;
    }


    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.booking'));
    }
}
