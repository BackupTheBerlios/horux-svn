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

        if(!$this->isPostBack)
        {

            $this->id->Value = $this->Request['id'];
            $this->setData();
        }
    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( "SELECT ac.nbre, ac.id, CONCAT('[',tt.abbreviation,'] - ', tt.name) AS timecode, ac.user_id FROM hr_timux_activity_counter AS ac LEFT JOIN hr_timux_timecode AS tt ON tt.id=ac.timecode_id WHERE ac.id=:id");
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();

        if($query)
        {
            $data = $query->read();
            $this->id->Value = $data['id'];
            $this->nbre->Text = sprintf("%.2f",$data['nbre']);
            $this->timeCode->Text = $data['timecode'];

            $this->userId = $data['user_id'];

            $this->employee = new employee($this->userId );


            $this->tbb->setTitle( Prado::localize("Modify the leave counter")." - ".$this->employee->getFullName() );
        }
    }


    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The leave counter was modified successfully'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.counter.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The leave counter was not modified'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.counter.mod', $pBack));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The leave counter was modified successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The leave counter was not modified'));

            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.counter.counter',$pBack));
        }
    }

    protected function saveData()
    {

        $cmd = $this->db->createCommand( "UPDATE `hr_timux_activity_counter` SET
                                            `nbre`=:nbre
                                            WHERE id=:id
                                            ;" );

        $cmd->bindParameter(":nbre",$this->nbre->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);

        $res1 = $cmd->execute();

        return $res1;
    }


    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.counter.counter'));
    }
}
