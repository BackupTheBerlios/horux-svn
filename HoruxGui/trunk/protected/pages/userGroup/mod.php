<?php


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
            $this->Session['listBoxAccessTime'] = $this->listBox;
        }
    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_GET_GROUP );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
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
            $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
            $cmd->bindValue(":readerId",$cb->value, PDO::PARAM_INT);
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
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":comment",$this->comment->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":accessPlugin",$this->accessPlugin->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);

        $cmd->execute();

        $this->addStandalone('sub', $this->id->Value);

        $cmd=$this->db->createCommand(SQL::SQL_REMOVE_ACCESS_GROUP);
        $cmd->bindValue(":id",$this->id->Value);
        $cmd->execute();

        $comp = $this->findControlsByType("TCheckBox");

        foreach($comp as $cb)
        {
            if($cb->getChecked() == true)
            {
                $id_device = $cb->value;
                $al_array = $this->Session['listBoxAccessTime'];

                $al_obj = $this->findControl($al_array[$cb->getUniqueID()]);
                $id_accessLevel = $al_obj->getSelectedValue();

                $cmd = $this->db->createCommand( SQL::SQL_ADD_ACCESS_GROUP );
                $cmd->bindValue(":lastId",$this->id->Value, PDO::PARAM_INT);
                $cmd->bindValue(":readerId",$id_device, PDO::PARAM_INT);
                $cmd->bindValue(":accessLevelId",$id_accessLevel, PDO::PARAM_INT);

                $cmd->execute();
            }
        }

        $this->addStandalone('add', $this->id->Value);

        $this->log("Modify the user group: ".$this->name->SafeText);

        return true;
    }

    protected function addStandalone($function, $idgroup)
    {
        $sa = new TStandAlone();
        $sa->addStandalone($function, $idgroup, 'UserGroupMod');
    }

    public function serverValidateName($sender, $param)
    {
        $cmd = $this->db->createCommand( SQL::SQL_IS_NAME_EXIST_EXCEPT_ID);
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
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
