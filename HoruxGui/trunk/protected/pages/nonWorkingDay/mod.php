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

class mod extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {
            $userId=$this->Application->getUser()->getUserId();
            $this->blockRecord('hr_non_working_day', $this->Request['id'], $userId);

            $this->id->Value = $this->Request['id'];
            $this->setData();
        }

    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_GET_NONWORKINGDAY );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();

            $from = $this->dateFromSql($data['from']);
            $until = $this->dateFromSql($data['until']);

            $this->id->Value = $data['id'];
            $this->name->Text = $data['name'];
            $this->color->Text = $data['color'];
            $this->from->Text = $from;
            $this->until->Text = $until;
            $this->comment->Text = $data['comment'];

            
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

        }
    }

    public function onDelete($sender, $param)
    {
        $this->log("Delete the non working day: ".$this->name->SafeText);

        $cmd = $this->db->createCommand( SQL::SQL_DELETE_NONWORKINGDAY );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);

        $res = $cmd->execute();

        $pBack = array('okMsg'=>Prado::localize('The non working day was deleted successfully'), 'id'=>$this->id->Value);

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

        if($this->Request['back'])
            $this->Response->redirect($this->Service->constructUrl($this->Request['back'], $pBack));
        else
            $this->Response->redirect($this->Service->constructUrl('nonWorkingDay.nonWorkingDay', $pBack));
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The non working day was modified successfully'), 'id'=>$this->id->Value);

                if($this->Request['back'])
                    $pBack['back'] = $this->Request['back'];

                $this->Response->redirect($this->Service->constructUrl('nonWorkingDay.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The non workong day was not modified'), 'id'=>$this->id->Value);

                if($this->Request['back'])
                    $pBack['back'] = $this->Request['back'];

                $this->Response->redirect($this->Service->constructUrl('nonWorkingDay.mod', $pBack));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The non working day was modified successfully'));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The non working day was not modified'));
            }
            
            $this->blockRecord('hr_non_working_day', $this->id->Value, 0);


            if($this->Request['back'])
                $this->Response->redirect($this->Service->constructUrl($this->Request['back'],$pBack));
            else
                $this->Response->redirect($this->Service->constructUrl('nonWorkingDay.nonWorkingDay',$pBack));
        }
    }

    public function onCancel($sender, $param)
    {
        $this->blockRecord('hr_non_working_day', $this->id->Value, 0);

        if($this->Request['back'])
            $this->Response->redirect($this->Service->constructUrl($this->Request['back']));
        else
            $this->Response->redirect($this->Service->constructUrl('nonWorkingDay.nonWorkingDay'));
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

        $cmd = $this->db->createCommand( SQL::SQL_UPDATE_NONWORKINGDAY );
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":from",$from, PDO::PARAM_STR);
        $cmd->bindValue(":until",$until, PDO::PARAM_STR);
        $cmd->bindValue(":comment",$this->comment->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $cmd->bindValue(":color",$this->color->SafeText, PDO::PARAM_STR);

        $period = "";

        if($this->allday->getChecked())
            $period = 'allday';
        if($this->morning->getChecked())
            $period = 'morning';
        if($this->afternoon->getChecked())
            $period = 'afternoon';

        $cmd->bindValue(":period",$period,PDO::PARAM_STR);


        $this->log("Modify the non working day: ".$this->name->SafeText);

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
}
