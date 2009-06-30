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

Prado::using('horux.pages.userGroup.sql');

class mod extends Page
{
    protected $listBox = array();

    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {
            $userId=$this->Application->getUser()->getUserId();
            $this->blockRecord('hr_user_group', $this->Request['id'], $userId);

            $cmd = $this->db->createCommand( SQL::SQL_GET_HARDWARE_LINK2GROUP );

            $query = $cmd->query();
            $recordSet2 = $query->readAll();

            $this->readerRepeater->DataSource=$recordSet2;
            $this->readerRepeater->dataBind();

            $this->id->Value = $this->Request['id'];
            $this->setData();
            $this->application->setGlobalState('listBoxAccessTime',$this->listBox);
        }
    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_GET_GROUP );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            $this->id->Value = $data['id'];
            $this->name->Text = $data['name'];
            $this->comment->Text = $data['comment'];
            $this->accessPlugin->Text = $data['accessPlugin'];
        }

        $comp = $this->findControlsByType("TCheckBox");

        foreach($comp as $cb)
        {
            $cmd = $this->db->createCommand( SQL::SQL_GET_ACCESS_GROUP );
            $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
            $cmd->bindParameter(":readerId",$cb->value, PDO::PARAM_INT);
            $query = $cmd->query();

            if($query)
            {
                $data = $query->read();

                if($data)
                {
                    $cb->setChecked(true);
                    $listItem = $this->findControl($this->listBox[$cb->getUniqueID()]);
                    if($data['id_access_level'] > 0)
                    $listItem->setSelectedValue($data['id_access_level']);
                }
            }
        }

    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $this->application->clearGlobalState('listBoxAccessTime');
                $pBack = array('okMsg'=>Prado::localize('The group was modified successfully'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('userGroup.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The group was not modified'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('userGroup.mod', $pBack));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            $pBack = array();

            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The group was modified successfully'));
            }
            else
            {
                $pBack = array('okMsg'=>Prado::localize('The group was not modified'));
            }
            $this->application->clearGlobalState('listBoxAccessTime');

            $this->blockRecord('hr_user_group', $this->id->Value, 0);
            $this->Response->redirect($this->Service->constructUrl('userGroup.UserGroupList',$pBack));

        }
    }

    public function onCancel($sender, $param)
    {
        $this->application->clearGlobalState('listBoxAccessTime');
        $this->blockRecord('hr_user_group', $this->id->Value, 0);
        $this->Response->redirect($this->Service->constructUrl('userGroup.UserGroupList'));
    }

    protected function saveData()
    {

        $cmd = $this->db->createCommand( SQL::SQL_MOD_GROUP );
        $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":comment",$this->comment->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":accessPlugin",$this->accessPlugin->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);

        $cmd->execute();

        $this->addStandalone('sub', $this->id->Value);

        $cmd=$this->db->createCommand(SQL::SQL_REMOVE_ACCESS_GROUP);
        $cmd->bindParameter(":id",$this->id->Value);
        $cmd->execute();

        $comp = $this->findControlsByType("TCheckBox");

        foreach($comp as $cb)
        {
            if($cb->getChecked() == true)
            {
                $id_device = $cb->value;
                $al_array = $this->application->getGlobalState('listBoxAccessTime');

                $al_obj = $this->findControl($al_array[$cb->getUniqueID()]);
                $id_accessLevel = $al_obj->getSelectedValue();

                $cmd = $this->db->createCommand( SQL::SQL_ADD_ACCESS_GROUP );
                $cmd->bindParameter(":lastId",$this->id->Value, PDO::PARAM_INT);
                $cmd->bindParameter(":readerId",$id_device, PDO::PARAM_INT);
                $cmd->bindParameter(":accessLevelId",$id_accessLevel, PDO::PARAM_INT);

                $cmd->execute();
            }
        }

        $this->addStandalone('add', $this->id->Value);

        $this->log("Modify the user group: ".$this->name->SafeText);

        return true;
    }

    protected function addStandalone($function, $idgroup)
    {
        $cmd=$this->db->createCommand("SELECT * FROM hr_user_group_access WHERE id_group=:id");
        $cmd->bindParameter(":id",$idgroup);
        $data = $cmd->query();
        $data = $data->readAll();
        foreach($data as $d)
        {
            $idreader = $d['id_device'];

            $cmd=$this->db->createCommand("SELECT * FROM hr_user_group_attribution WHERE id_group=:id");
            $cmd->bindParameter(":id",$idgroup);
            $data2 = $cmd->query();
            $data2 = $data2->readAll();

            foreach($data2 as $d2)
            {
                $idperson = $d2['id_user'];

                $cmd=$this->db->createCommand("SELECT t.serialNumber, t.isBlocked FROM hr_keys_attribution AS ta LEFT JOIN hr_keys AS t ON t.id=ta.id_key WHERE id_user=:id");
                $cmd->bindParameter(":id",$idperson);
                $data3 = $cmd->query();
                $data3 = $data3->readAll();

                foreach($data3 as $d3)
                {
                    $rfid = $d3['serialNumber'];

                    if( ($d3['isBlocked'] == 0 && $function=='add' ) || $function=='sub')
                    {
                        $cmd=$this->db->createCommand("INSERT INTO hr_standalone_action_service (`type`, `serialNumber`, `rd_id`) VALUES (:func,:rfid,:rdid)");
                        $cmd->bindParameter(":func",$function);
                        $cmd->bindParameter(":rfid",$rfid);
                        $cmd->bindParameter(":rdid",$idreader);
                        $cmd->execute();
                    }
                }
            }

        }
    }

    public function serverValidateName($sender, $param)
    {
        $cmd = $this->db->createCommand( SQL::SQL_IS_NAME_EXIST_EXCEPT_ID);
        $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);
        $array = $cmd->query()->readAll();

        if(count($array) > 0)
        $param->IsValid=false;
        else
        $param->IsValid=true;
    }


    public function repeaterDataBound($sender,$param)
    {
        $cmd = $this->db->createCommand( SQL::SQL_GET_ACCESS_LEVEL );
        $query = $cmd->query();
        $recordSet = $data = $query->readAll();

        $item=$param->Item;

        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->accessLevel->DataSource=$recordSet;
            $item->accessLevel->DataTextField='name';
            $item->accessLevel->DataValueField='id';
            $item->accessLevel->dataBind();
            if(count($recordSet)>0)
            $item->accessLevel->setSelectedIndex(0);
            $this->listBox[$item->reader->getUniqueID()] = $item->accessLevel->getUniqueID();
        }
    }

}
