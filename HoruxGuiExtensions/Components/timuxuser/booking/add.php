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
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The sign was not added'));
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
                $pBack = array('okMsg'=>Prado::localize('The sign was added successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The sign was not added'));
                
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
                                            `roundBooking`
                                            )
                                            VALUES (
                                            :tracking_id,
                                            :action,
                                            :roundBooking
                                            );" );

        $cmd->bindParameter(":tracking_id",$lastId,PDO::PARAM_STR);
        $cmd->bindParameter(":action",$this->sign->getSelectedValue(), PDO::PARAM_STR);
        $cmd->bindParameter(":roundBooking",$this->time->SafeText, PDO::PARAM_STR);

        $res1 = $cmd->execute();

        return $lastId;
    }


    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.booking'));
    }
}
