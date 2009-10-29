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

Prado::using('horux.pages.key.sql');

class add extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        $this->setHoruxSysTray(true);

        if(isset($this->Request['sn']))
        {
            $this->serialNumber->Text = $this->Request['sn'];
        }

        $this->person->DataSource = $this->PersonList;
        $this->person->dataBind();


        if($this->person->getItemCount() && $this->person->getSelectedValue() == '')
        {
            $this->person->setSelectedIndex(0);
        }
    }

    protected function getPersonList()
    {
        $cmd = NULL;
        if($this->db->DriverName == 'sqlite')
        {
            $cmd = $this->db->createCommand( SQL::SQL_GET_PERSON_SQLITE );
        }
        else
        {
            $cmd = $this->db->createCommand( SQL::SQL_GET_PERSON );
        }
        $data =  $cmd->query();
        $data = $data->readAll();
        $d[0]['Value'] = 'null';
        $d[0]['Text'] = Prado::localize('---- No attribution ----');
        $data = array_merge($d, $data);
        return $data;
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($lastId = $this->saveData())
            {
                $id = $lastId;
                $pBack = array('okMsg'=>Prado::localize('The key was added successfully'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('key.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The key was not added'));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The key was added successfully'));
            }
            else
            $pBack = array('koMsg'=>Prado::localize('The key was not added'));
            $this->Response->redirect($this->Service->constructUrl('key.KeyList',$pBack));
        }
    }

    protected function saveData()
    {
        $res1 = $res2 = true;


        $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
        $cmd->bindParameter(":identificator",$this->identificator->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":serialNumber",$this->serialNumber->SafeText, PDO::PARAM_STR);

        if($this->isBlocked->getChecked())
        {
            $isBlocked = 1;
            $cmd->bindParameter(":isBlocked",$isBlocked, PDO::PARAM_STR);
        }
        else
        {
            $isBlocked = 0;
            $cmd->bindParameter(":isBlocked",$isBlocked, PDO::PARAM_STR);
        }

        if($this->person->getSelectedValue() != 'null')
        $isUsed = 1;
        else
        $isUsed = 0;

        $cmd->bindParameter(":isUsed",$isUsed, PDO::PARAM_STR);


        $res1 = $cmd->execute();
        $lastId = $this->db->LastInsertID;

        $dd = $this->person->getSelectedValue();

        if($this->person->getSelectedValue() != 'null')
        {
            $cmd2=$this->db->createCommand(SQL::SQL_ADD_TAG_ATTRIBUTION);
            $cmd2->bindParameter(":id_key",$lastId);
            $cmd2->bindParameter(":id_user",$this->person->getSelectedValue());
            $res2 = $cmd2->execute();

            if($isBlocked == 0)
                $this->addStandalone('add',$lastId);
        }

        $this->log("Add the key: ".$this->serialNumber->SafeText);


        return $lastId;
    }

    protected function addStandalone($function, $idkey)
    {
        $sa = new TStandAlone();
        $sa->addStandalone($function, $idkey, 'KeyAdd');
    }


    public function serverValidateSerialNumber($sender, $param)
    {
        $cmd = $this->db->createCommand( SQL::SQL_IS_SERIALNUMBER_EXIST);
        $cmd->bindParameter(":serialNumber",$this->serialNumber->SafeText,PDO::PARAM_STR);
        $array = $cmd->query()->readAll();

        if(count($array) > 0)
        $param->IsValid=false;
        else
        $param->IsValid=true;
    }

    public function serverValidateIdentificator($sender, $param)
    {
        $cmd = $this->db->createCommand( SQL::SQL_IS_IDENTIFICATOR_EXIST);
        $cmd->bindParameter(":identificator",$this->identificator->SafeText,PDO::PARAM_STR);
        $array = $cmd->query()->readAll();

        if(count($array) > 0)
        $param->IsValid=false;
        else
        $param->IsValid=true;
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('key.KeyList'));
    }
}
