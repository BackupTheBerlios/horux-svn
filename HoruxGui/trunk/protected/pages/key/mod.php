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

class mod extends Page
{
    public $cards_format;

    public function onLoad($param)
    {
        parent::onLoad($param);

        $this->setHoruxSysTray(true);

        // get the cards format...
        $sql = "SELECT cards_format FROM hr_config WHERE id=1";
        $cmd=$this->db->createCommand($sql);
        $data = $cmd->query();
        $data = $data->read();
        $sn = $this->Request['sn'];

        if($data['cards_format'] != "") {
          $this->cards_format = $data['cards_format'];
        }

        if(!$this->isPostBack)
        {

            if(isset($this->Request['id']))
            {
                $userId=$this->Application->getUser()->getUserId();
                $this->blockRecord('hr_keys', $this->Request['id'], $userId);

                $this->id->Value = $this->Request['id'];
                $this->setData();
                $this->person->DataSource = $this->PersonList;
                $this->person->dataBind();

            }

            if(isset($this->Request['sn']))
            {
                $userId=$this->Application->getUser()->getUserId();

                $cmd = $this->db->createCommand( "SELECT * FROM hr_keys WHERE serialNumber='".$this->Request['sn']."'" );
                $data =  $cmd->query();
                $data = $data->read();
                if($data)
                {

                    $this->blockRecord('hr_keys', $data['id'], $userId);
                    $this->id->Value = $data['id'];
                    $this->setData();
                    $this->person->DataSource = $this->PersonList;
                    $this->person->dataBind();
                }
                else
                $this->Response->redirect($this->Service->constructUrl('key.add',array('sn'=>$this->Request['sn'])));

            }
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

    protected function setData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_GET_KEY );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            $this->id->Value = $data['id'];
            $this->identificator->Text = $data['identificator'];
            $this->serialNumber->Text = $data['serialNumber'];
            $this->isBlocked->setChecked($data['isBlocked']);
            if($data['isUsed'])
            {
                $cmd = $this->db->createCommand( SQL::SQL_GET_ATTRIBUTION );
                $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
                $query = $cmd->query();
                $data = $query->read();

                $this->person->setSelectedValue($data['id']);
            }
            else
            {
                if($this->person->getItemCount())
                $this->person->setSelectedIndex(0);

            }
        }
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The key was modified successfully'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('key.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The key was not modified'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('key.mod', $pBack));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The key was modified successfully'));
            }
            else
            $pBack = array('koMsg'=>Prado::localize('The key was not modified'));

            $this->blockRecord('hr_keys', $this->id->Value, 0);
            $this->Response->redirect($this->Service->constructUrl('key.KeyList',$pBack));
        }
    }

    public function onCancel($sender, $param)
    {
        $this->blockRecord('hr_keys', $this->id->Value, 0);
        $this->Response->redirect($this->Service->constructUrl('key.KeyList'));
    }


    protected function saveData()
    {
        $res1 = $res2 = $res3 = true;

        $cmd = $this->db->createCommand( SQL::SQL_MOD_KEY );
        $cmd->bindValue(":identificator",$this->identificator->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":serialNumber",$this->serialNumber->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_STR);
        $cmd->bindValue(":isBlocked",$this->isBlocked->getChecked(), PDO::PARAM_STR);


        if($this->person->getSelectedValue() != 'null')
        $isUsed = 1;
        else
        $isUsed = 0;

        $cmd->bindValue(":isUsed",$isUsed, PDO::PARAM_STR);

        $res1 = $cmd->execute();

        //remove in all case
        $this->addStandalone('sub', $this->id->Value);

        //remove the current tag attribution
        $cmd1=$this->db->createCommand(SQL::SQL_REMOVE_TAG_ATTRIBUTION);
        $cmd1->bindValue(":id",$this->id->Value);
        $res2 = $cmd1->execute();



        if($this->person->getSelectedValue() != 'null')
        {
            $cmd2=$this->db->createCommand(SQL::SQL_ADD_TAG_ATTRIBUTION);
            $cmd2->bindValue(":id_key",$this->id->Value);
            $cmd2->bindValue(":id_user",$this->person->getSelectedValue());
            $res3 = $cmd2->execute();

            if(!$this->isBlocked->getChecked())
            {
                $this->addStandalone('add', $this->id->Value);
            }
            else
            {
                $this->addStandalone('sub', $this->id->Value);
            }

        }
        

        $this->log("Modify the key: ".$this->serialNumber->SafeText);

        return $res1 || $res3;
    }

    protected function addStandalone($function, $idkey)
    {
        $sa = new TStandAlone();
        $sa->addStandalone($function, $idkey, 'KeyMod');
    }


    public function serverValidateSerialNumber($sender, $param)
    {
        $cmd = $this->db->createCommand( SQL::SQL_IS_SERIALNUMBER_EXIST_EXCEPT_ID);
        $cmd->bindValue(":serialNumber",$this->serialNumber->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
        $array = $cmd->query()->readAll();

        if(count($array) > 0)
        $param->IsValid=false;
        else
        $param->IsValid=true;
    }

    public function serverValidateIdentificator($sender, $param)
    {
        $cmd = $this->db->createCommand( SQL::SQL_IS_IDENTIFICATOR_EXIST_EXCEPT_ID);
        $cmd->bindValue(":identificator",$this->identificator->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
        $array = $cmd->query()->readAll();

        if(count($array) > 0)
        $param->IsValid=false;
        else
        $param->IsValid=true;
    }
}
