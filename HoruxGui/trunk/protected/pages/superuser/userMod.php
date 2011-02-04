<?php

Prado::using('horux.pages.superuser.sql');

class userMod extends Page
{
    protected $isPasswordChanged = false;

    public function onLoad($param)
    {
        parent::onLoad($param);

        $superAdmin = $this->Application->getUser()->getSuperAdmin();
        $groupId = $this->Application->getUser()->getGroupID();
        $param = $this->Application->getParameters();

        if($param['appMode'] == 'demo' && $superAdmin == 0)
        {
            $this->tbb->apply->setEnabled(false);
            $this->tbb->Save->setEnabled(false);
        }


        if($this->Request['id'] == 1 && $groupId!=1 && $param['appMode'] == 'saas')
        {
            $pBack = array('koMsg'=>Prado::localize("You don't have the right to modify this user'"));
            $this->Response->redirect($this->Service->constructUrl('superuser.userList',$pBack));
        }

        if(!$this->isPostBack)
        {

            $superAdmin = $this->Application->getUser()->getSuperAdmin();

            $userId=$this->Application->getUser()->getUserId();
            $this->blockRecord('hr_superusers', $this->Request['id'], $userId);

            if($userId != $this->Request['id'] && !$superAdmin)
            {
                $pBack = array('koMsg'=>Prado::localize("You don't have the right to modify this user'"));

                $this->Response->redirect($this->Service->constructUrl('superuser.userList',$pBack));
            }

            $this->id->Value = $this->Request['id'];


            $this->group_id->DataTextField='name';
            $this->group_id->DataValueField='id';
            $this->group_id->DataSource=$this->DataGroup;
            $this->group_id->dataBind();

            if(!$superAdmin)
            {
                $this->group_id->setEnabled(false);
                $this->user_id->setEnabled(false);
                $this->name->setEnabled(false);
            }

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

            $this->setData();
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


    public function setData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_GET_USER_BY_ID );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();

            $this->name->Text = $data['name'];
            $this->email->Text = $data['email'];
            $this->group_id->setSelectedValue($data['group_id']);
            $this->user_id->setSelectedValue($data['user_id']);
            $this->currentPswd->Value = $data['password'];
        }

        $cmd = $this->db->createCommand( "SELECT * FROM hr_superuser_shortcut WHERE superuser_id=:id" );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
            $i = 1;
            foreach($query as $q) {
                $shortcut = "shortcut".$i;

                $this->$shortcut->setSelectedValue($q['shortcut']);

                $i++;
            }
        }

    }

    protected function getDataGroup()
    {
        $param = $this->Application->getParameters();
        $userId = $this->Application->getUser()->getUserID();

        if( ($param['appMode'] == 'saas' && $userId == 1) || $param['appMode'] != 'saas' )
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
            if($this->saveData())
            {
                $id = $this->id->Value;
                if($this->isPasswordChanged)
                $pBack = array('okMsg'=>Prado::localize('The user was modified successfully. The password was changed'), 'id'=>$id);
                else
                $pBack = array('okMsg'=>Prado::localize('The user was modified successfully. The password was not changed'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('superuser.userMod', $pBack));
            }
            else
            {
                $id = $this->id->Value;
                $pBack = array('koMsg'=>Prado::localize('The user was not modified. The password was not changed'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('superuser.userMod',$pBack));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                if($this->isPasswordChanged)
                $pBack = array('okMsg'=>Prado::localize('The user was modified successfully. The password was changed'));
                else
                $pBack = array('okMsg'=>Prado::localize('The user was modified successfully. The password was not changed'));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The user was not modified. The password was not changed'));
            }
            $this->blockRecord('hr_superusers', $this->id->Value, 0);
            $this->Response->redirect($this->Service->constructUrl('superuser.userList',$pBack));
        }
    }

    public function onCancel($sender, $param)
    {
        $this->blockRecord('hr_superusers', $this->id->Value, 0);
        $this->Response->redirect($this->Service->constructUrl('superuser.userList'));
    }

    public function saveData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_MOD_USER );
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":email",$this->email->SafeText,PDO::PARAM_STR);

        if($this->password->SafeText == "")
        {
            $cmd->bindValue(":password",$this->currentPswd->Value,PDO::PARAM_STR);
            $this->isPasswordChanged = false;
        }
        else
        {
            $cmd->bindValue(":password",sha1( $this->password->SafeText),PDO::PARAM_STR);
            $this->isPasswordChanged = true;
        }

        $cmd->bindValue(":group_id",$this->group_id->getSelectedValue(),PDO::PARAM_INT);
        $cmd->bindValue(":user_id",$this->user_id->getSelectedValue(),PDO::PARAM_INT);
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);

        $cmd->execute();


        $cmd = $this->db->createCommand( "DELETE FROM hr_superuser_shortcut WHERE superuser_id=".$this->id->Value );
        $cmd->execute();

        if($this->shortcut1->getSelectedValue() != -1) {
            $cmd = $this->db->createCommand('INSERT INTO hr_superuser_shortcut (superuser_id, shortcut) VALUES (:id, :shortcut)');
            $cmd->bindValue(":shortcut",$this->shortcut1->getSelectedValue(),PDO::PARAM_STR);
            $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
            $cmd->execute();
        }
        if($this->shortcut2->getSelectedValue() != -1) {
            $cmd = $this->db->createCommand('INSERT INTO hr_superuser_shortcut (superuser_id, shortcut) VALUES (:id, :shortcut)');
            $cmd->bindValue(":shortcut",$this->shortcut2->getSelectedValue(),PDO::PARAM_STR);
            $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
            $cmd->execute();
        }
        if($this->shortcut3->getSelectedValue() != -1) {
            $cmd = $this->db->createCommand('INSERT INTO hr_superuser_shortcut (superuser_id, shortcut) VALUES (:id, :shortcut)');
            $cmd->bindValue(":shortcut",$this->shortcut3->getSelectedValue(),PDO::PARAM_STR);
            $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
            $cmd->execute();
        }
        if($this->shortcut4->getSelectedValue() != -1) {
            $cmd = $this->db->createCommand('INSERT INTO hr_superuser_shortcut (superuser_id, shortcut) VALUES (:id, :shortcut)');
            $cmd->bindValue(":shortcut",$this->shortcut4->getSelectedValue(),PDO::PARAM_STR);
            $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
            $cmd->execute();
        }
        if($this->shortcut5->getSelectedValue() != -1) {
            $cmd = $this->db->createCommand('INSERT INTO hr_superuser_shortcut (superuser_id, shortcut) VALUES (:id, :shortcut)');
            $cmd->bindValue(":shortcut",$this->shortcut5->getSelectedValue(),PDO::PARAM_STR);
            $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
            $cmd->execute();
        }

        $this->log("Modify the super user:".$this->name->SafeText);

        return true;
    }

    public function serverValidatePassword($sender, $param)
    {
        if($this->password->Text != $this->confirmation->Text && $this->password->Text != "")
        $param->IsValid=false;
    }

    public function serverValidateName($sender, $param)
    {
        $cmd = $this->db->createCommand( SQL::SQL_IS_NAME_EXIST2 );
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $data = $cmd->query();
        $data = $data->read();


        if($data['nb'] > 0)
        $param->IsValid=false;
    }
}
