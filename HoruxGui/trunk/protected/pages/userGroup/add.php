<?php


Prado::using('horux.pages.userGroup.sql');

class add extends Page
{
    protected $listBox = array();

    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->IsPostBack)
        {

            $cmd = $this->db->createCommand( SQL::SQL_GET_HARDWARE_LINK2GROUP );
            $query = $cmd->query();
            $recordSet2 = $query->readAll();

            $this->readerRepeater->DataSource=$recordSet2;
            $this->readerRepeater->dataBind();

            $this->Session['listBoxAccessTime'] = $this->listBox;
        }

    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($lastId = $this->saveData())
            {
                $this->application->clearGlobalState('listBoxAccessTime');
                $pBack = array('okMsg'=>Prado::localize('The group was added successfully'), 'id'=>$lastId);
                $this->Response->redirect($this->Service->constructUrl('userGroup.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The group was not added'));
                $this->Response->redirect($this->Service->constructUrl('userGroup.add',$pBack));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $this->application->clearGlobalState('listBoxAccessTime');
                $pBack = array('okMsg'=>Prado::localize('The group was added successfully'));
            }
            else
            $pBack = array('koMsg'=>Prado::localize('The group was not added'));

            $this->Response->redirect($this->Service->constructUrl('userGroup.UserGroupList',$pBack));
        }
    }

    public function onCancel($sender, $param)
    {
        $this->application->clearGlobalState('listBoxAccessTime');
        $this->Response->redirect($this->Service->constructUrl('userGroup.UserGroupList'));
    }

    protected function saveData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_ADD_GROUP );
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":comment",$this->comment->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":accessPlugin",$this->accessPlugin->SafeText,PDO::PARAM_STR);

        if(!$cmd->execute()) return false;

        $lastId = $this->db->getLastInsertID();

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
                $cmd->bindValue(":lastId",$lastId, PDO::PARAM_INT);
                $cmd->bindValue(":readerId",$id_device, PDO::PARAM_INT);
                $cmd->bindValue(":accessLevelId",$id_accessLevel, PDO::PARAM_INT);

                $cmd->execute();
            }
        }

        $this->log("Add the user group: ".$this->name->SafeText);

        return $lastId;
    }

    public function serverValidateName($sender, $param)
    {
        $cmd = $this->db->createCommand( SQL::SQL_IS_NAME_EXIST);
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
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
