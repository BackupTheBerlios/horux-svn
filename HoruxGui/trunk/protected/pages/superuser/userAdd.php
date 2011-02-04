<?php

Prado::using('horux.pages.superuser.sql');

class userAdd extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {

            $this->group_id->DataTextField='name';
            $this->group_id->DataValueField='id';
            $this->group_id->DataSource=$this->DataGroup;
            $this->group_id->dataBind();

            $this->user_id->DataTextField='name';
            $this->user_id->DataValueField='id';
            $this->user_id->DataSource=$this->DataPerson;
            $this->user_id->dataBind();


            $this->shortcut1->DataTextField='pagename';
            $this->shortcut1->DataValueField='page';
            $this->shortcut1->DataSource=$this->DataPage;
            $this->shortcut1->dataBind();
            $this->shortcut1->setSelectedIndex(0);

            $this->shortcut2->DataTextField='pagename';
            $this->shortcut2->DataValueField='page';
            $this->shortcut2->DataSource=$this->DataPage;
            $this->shortcut2->dataBind();
            $this->shortcut2->setSelectedIndex(0);

            $this->shortcut3->DataTextField='pagename';
            $this->shortcut3->DataValueField='page';
            $this->shortcut3->DataSource=$this->DataPage;
            $this->shortcut3->dataBind();
            $this->shortcut3->setSelectedIndex(0);

            $this->shortcut4->DataTextField='pagename';
            $this->shortcut4->DataValueField='page';
            $this->shortcut4->DataSource=$this->DataPage;
            $this->shortcut4->dataBind();
            $this->shortcut4->setSelectedIndex(0);

            $this->shortcut5->DataTextField='pagename';
            $this->shortcut5->DataValueField='page';
            $this->shortcut5->DataSource=$this->DataPage;
            $this->shortcut5->dataBind();
            $this->shortcut5->setSelectedIndex(0);

        }
    }

    public function getDataPage()
    {
        $cmd = $this->db->createCommand( "SELECT c.menuname AS pagename, c.page, i . * FROM hr_component AS c LEFT JOIN hr_install AS i ON i.id=c.id_install  ORDER BY pagename" );
        $data_ = $cmd->query();
        $data_ = $data_->readAll();

        $n = count($data_);

        $data2[] = array('page'=>-1, 'pagename'=>Prado::localize('-- Select a shortcut --')) ;
        $data2[] = array('page'=>'user.UserList', 'pagename'=>Prado::localize('User List'));



        for($i=0;$i<$n; $i++)
        {
             $data_[$i]['pagename'] = Prado::localize($data_[$i]['pagename'],array(), $data_[$i]['name'])." ({$data_[$i]['name']})" ;
        }

        $data_ = array_merge($data2, $data_);

        return $data_;
    }

    protected function getDataGroup()
    {
        $param = $this->Application->getParameters();
        $groupId = $this->Application->getUser()->getGroupID();

        if( ($param['appMode'] == 'saas' && $groupId == 1) || $param['appMode'] != 'saas' )
        {
            $cmd=$this->db->createCommand(SQL::SQL_GET_ALL_GROUP);
        }
        else
        {
            $cmd=$this->db->createCommand(SQL::SQL_GET_ALL_GROUP_SAAS);
        }
        
        $data=$cmd->query();

        return $data;
    }

    protected function getDataPerson()
    {
        $cmd = NULL;
        if($this->db->DriverName == 'sqlite')
        {
            $cmd=$this->db->createCommand(SQL::SQL_GET_ALL_PERSON_SQLITE);
        }
        else
        {
            $cmd=$this->db->createCommand(SQL::SQL_GET_ALL_PERSON);
        }

        $data=$cmd->query();

        $data = $data->readAll();
        $data1[] = array('id'=>0, 'name'=>'---');

        $data = array_merge($data1, $data);


        return $data;
    }


    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if(($id = $this->saveData()) !== false)
            {
                $pBack = array('okMsg'=>Prado::localize('The user was added successfully'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('superuser.userMod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The user was not added'));
                $this->Response->redirect($this->Service->constructUrl('superuser.userAdd',$pBack));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The user was added successfully'));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The user was not saved'));
            }

            $this->Response->redirect($this->Service->constructUrl('superuser.userList',$pBack));
        }
    }

    public function saveData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_ADD_USER );
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":password",sha1($this->password->SafeText),PDO::PARAM_STR);
        $cmd->bindValue(":email",$this->email->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":group_id",$this->group_id->getSelectedValue(),PDO::PARAM_INT);
        $cmd->bindValue(":user_id",$this->user_id->getSelectedValue(),PDO::PARAM_INT);


        if(!$cmd->execute()) return false;

        $id = $this->db->getLastInsertID();

        $this->log("Add the super user:".$this->name->SafeText);

        if($this->email->SafeText != '')
        {
            $mailer = new TMailer();
            $mailer->sendSuperUser($this->email->SafeText,$this->name->SafeText, $this->password->SafeText);
        }


        if($this->shortcut1->getSelectedValue() != -1) {
            $cmd = $this->db->createCommand('INSERT INTO hr_superuser_shortcut (superuser_id, shortcut) VALUES (:id, :shortcut)');
            $cmd->bindValue(":shortcut",$this->shortcut1->getSelectedValue(),PDO::PARAM_STR);
            $cmd->bindValue(":id",$id, PDO::PARAM_INT);
            $cmd->execute();
        }
        if($this->shortcut2->getSelectedValue() != -1) {
            $cmd = $this->db->createCommand('INSERT INTO hr_superuser_shortcut (superuser_id, shortcut) VALUES (:id, :shortcut)');
            $cmd->bindValue(":shortcut",$this->shortcut2->getSelectedValue(),PDO::PARAM_STR);
            $cmd->bindValue(":id",$id, PDO::PARAM_INT);
            $cmd->execute();
        }
        if($this->shortcut3->getSelectedValue() != -1) {
            $cmd = $this->db->createCommand('INSERT INTO hr_superuser_shortcut (superuser_id, shortcut) VALUES (:id, :shortcut)');
            $cmd->bindValue(":shortcut",$this->shortcut3->getSelectedValue(),PDO::PARAM_STR);
            $cmd->bindValue(":id",$id, PDO::PARAM_INT);
            $cmd->execute();
        }
        if($this->shortcut4->getSelectedValue() != -1) {
            $cmd = $this->db->createCommand('INSERT INTO hr_superuser_shortcut (superuser_id, shortcut) VALUES (:id, :shortcut)');
            $cmd->bindValue(":shortcut",$this->shortcut4->getSelectedValue(),PDO::PARAM_STR);
            $cmd->bindValue(":id",$id, PDO::PARAM_INT);
            $cmd->execute();
        }
        if($this->shortcut5->getSelectedValue() != -1) {
            $cmd = $this->db->createCommand('INSERT INTO hr_superuser_shortcut (superuser_id, shortcut) VALUES (:id, :shortcut)');
            $cmd->bindValue(":shortcut",$this->shortcut5->getSelectedValue(),PDO::PARAM_STR);
            $cmd->bindValue(":id",$id, PDO::PARAM_INT);
            $cmd->execute();
        }


        return $id;
    }

    public function serverValidatePassword($sender, $param)
    {
        if($this->password->Text != $this->confirmation->Text)
        $param->IsValid=false;
    }

    public function serverValidateName($sender, $param)
    {
        $cmd = $this->db->createCommand( SQL::SQL_IS_NAME_EXIST );
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $data = $cmd->query();
        $data = $data->read();


        if($data['nb'] > 0)
        $param->IsValid=false;
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('superuser.userList'));
    }
}
