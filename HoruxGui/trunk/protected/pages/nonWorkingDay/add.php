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

Prado::using('horux.pages.nonWorkingDay.sql');

class add extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {
            $date = getDate();

            if(Prado::getApplication()->getSession()->contains('nonWorkingDayYear'))
            {
                $this->until->setTimeStamp(mktime(0,0,0,1,1,$this->Session['nonWorkingDayYear']));
                $this->from->setTimeStamp(mktime(0,0,0,1,1,$this->Session['nonWorkingDayYear']));
            }
            else
            {
                $this->until->setTimeStamp(mktime(0,0,0,1,1,$date['year']));
                $this->from->setTimeStamp(mktime(0,0,0,1,1,$date['year']));
            }
        }
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $id = $this->db->getLastInsertID();
                $pBack = array('okMsg'=>Prado::localize('The non working day was added successfully'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('nonWorkingDay.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The non working day was not added'));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The non working day was added successfully'));
            }
            else
            $pBack = array('koMsg'=>Prado::localize('The non working day was not added'));
            $this->Response->redirect($this->Service->constructUrl('nonWorkingDay.nonWorkingDay',$pBack));
        }
    }

    protected function saveData()
    {

        $from = $this->dateToSql($this->from->SafeText);

        if($this->until->SafeText != "")
        {
            $until = $this->dateToSql($this->until->SafeText);
        }
        else
        $until = $from;

        $cmd = $this->db->createCommand( SQL::SQL_ADD_NONWORKINGDAY );
        $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":from",$from, PDO::PARAM_STR);
        $cmd->bindParameter(":until",$until, PDO::PARAM_STR);
        $cmd->bindParameter(":comment",$this->comment->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":color",$this->color->SafeText, PDO::PARAM_STR);

        $period = "";

        if($this->allday->getChecked())
            $period = 'allday';
        if($this->morning->getChecked())
            $period = 'morning';
        if($this->afternoon->getChecked())
            $period = 'afternoon';

        $cmd->bindParameter(":period",$period,PDO::PARAM_STR);


        $this->log("Add the non working day: ".$this->name->SafeText);


        $res = $cmd->execute();

        if($res)
        {
            $cmd = $this->db->createCommand( "SELECT * FROM hr_device WHERE accessPoint=1" );
            $data = $cmd->query();
            $row = $data->readAll();

            foreach($row as $r)
            {
                $sa = new TStandAlone();
                $sa->addStandalone('add', $r['id'], 'reinit');
            }
        }
        
        return $res;
    }

    protected function serverUntilValidate($sender, $param)
    {
        if( $this->until->SafeText == "" ) return;

        $until = strtotime($this->until->SafeText);
        $from = strtotime($this->from->SafeText);
        if($until<$from)
        $param->IsValid=false;
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('nonWorkingDay.nonWorkingDay'));
    }
}
