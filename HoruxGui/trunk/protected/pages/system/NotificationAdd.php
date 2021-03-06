<?php

Prado::using('horux.pages.system.sql');

class NotificationAdd extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        $this->superUserList->DataSource = $this->SUList;
        $this->superUserList->dataBind();

        $this->userList->DataSource = $this->UList;
        $this->userList->dataBind();

          $param = $this->Application->getParameters();
          $superAdmin = $this->Application->getUser()->getSuperAdmin();

          if($param['appMode'] == 'demo' && $superAdmin == 0)
          {
                  $this->tbb->Save->setEnabled(false);
                  $this->tbb->apply->setEnabled(false);
          }  
    }

    protected function getSUList()
    { 
        $cmd = $this->db->createCommand( SQL::SQL_GET_SU );
        $data =  $cmd->query();
        $data = $data->readAll();
        
        return $data;
    }

    protected function getUList()
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
        return $data;

    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($lastId = $this->saveData())
          {
            $id = $lastId;
            $pBack = array('okMsg'=>Prado::localize('The notification was added successfully'), 'id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('system.NotificationMod', $pBack));
          }
          else
          {
            $pBack = array('koMsg'=>Prado::localize('The notification was not added'));
          }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The notification was added successfully'));
          }
          else
            $pBack = array('koMsg'=>Prado::localize('The notification was not added'));
          $this->Response->redirect($this->Service->constructUrl('system.Notification',$pBack));
        }
    }

    protected function saveData()
    {
        $res1 = $res2 = $res3 = $res4 = true;


        $cmd = $this->db->createCommand( SQL::SQL_NOTIFICATION );
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":emails",$this->emailToSend->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":description",$this->comment->SafeText, PDO::PARAM_STR);


        $res1 = $cmd->execute();
        $lastId = $this->db->LastInsertID;

        $indices=$this->superUserList->SelectedIndices;
        foreach($indices as $index)
        {
            $item = $this->superUserList->Items[$index];
            $cmd = $this->db->createCommand( SQL::SQL_NOTIFICATION_SU );
            $cmd->bindValue(":id_notification",$lastId,PDO::PARAM_STR);
            $cmd->bindValue(":id_superuser",$item->Value, PDO::PARAM_STR);

            $res2 = $cmd->execute();
        }

        $indices=$this->userList->SelectedIndices;
        foreach($indices as $index)
        {
            $item = $this->userList->Items[$index];
            $cmd = $this->db->createCommand( SQL::SQL_NOTIFICATION_CODE );
            $cmd->bindValue(":id_notification",$lastId,PDO::PARAM_STR);
            $type = "ACCESS";
            $cmd->bindValue(":type",$type, PDO::PARAM_STR);
            $code = 0;
            $cmd->bindValue(":code",$code, PDO::PARAM_STR);
            $cmd->bindValue(":param",$item->Value, PDO::PARAM_STR);

            $res3 = $cmd->execute();
        }

        if($this->n_t_1->getChecked()) $this->insertNotificationCode($lastId, "ACCESS", $this->n_t_1->Value );
        if($this->n_t_2->getChecked()) $this->insertNotificationCode($lastId, "ACCESS", $this->n_t_2->Value );
        if($this->n_t_3->getChecked()) $this->insertNotificationCode($lastId, "ACCESS", $this->n_t_3->Value );
        if($this->n_t_4->getChecked()) $this->insertNotificationCode($lastId, "ACCESS", $this->n_t_4->Value );
        if($this->n_t_5->getChecked()) $this->insertNotificationCode($lastId, "ACCESS", $this->n_t_5->Value );
        if($this->n_t_6->getChecked()) $this->insertNotificationCode($lastId, "ACCESS", $this->n_t_6->Value );
        if($this->n_t_7->getChecked()) $this->insertNotificationCode($lastId, "ACCESS", $this->n_t_7->Value );
        if($this->n_t_8->getChecked()) $this->insertNotificationCode($lastId, "ACCESS", $this->n_t_8->Value );
        if($this->n_t_9->getChecked()) $this->insertNotificationCode($lastId, "ACCESS", $this->n_t_9->Value );
        if($this->n_t_10->getChecked()) $this->insertNotificationCode($lastId, "ACCESS", $this->n_t_10->Value );

        if($this->n_900->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_900->Value );
        if($this->n_1001->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_1001->Value );
        if($this->n_1002->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_1002->Value );
        if($this->n_1003->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_1003->Value );
        if($this->n_1004->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_1004->Value );
        if($this->n_1005->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_1005->Value );
        if($this->n_1006->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_1006->Value );
        if($this->n_1007->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_1007->Value );
        if($this->n_1008->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_1008->Value );
        if($this->n_1009->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_1009->Value );
        if($this->n_1010->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_1010->Value );
        if($this->n_1011->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_1011->Value );
        if($this->n_1012->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_1012->Value );
        if($this->n_1013->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_1013->Value );
        if($this->n_1014->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_1014->Value );
        if($this->n_1015->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_1015->Value );
        if($this->n_1016->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_1016->Value );
        if($this->n_1200->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_1200->Value );
        if($this->n_1300->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_1300->Value );
        if($this->n_1301->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_1301->Value );
        if($this->n_1102->getChecked()) $this->insertNotificationCode($lastId, "ALARM", $this->n_1102->Value );

        $this->log("Add the notification: ".$this->name->SafeText);

	    return $lastId;
    }

    protected function insertNotificationCode($not_id, $type, $code, $value='')
    {
        $cmd = $this->db->createCommand( SQL::SQL_NOTIFICATION_CODE );
        $cmd->bindValue(":id_notification",$not_id,PDO::PARAM_STR);
        $cmd->bindValue(":type",$type, PDO::PARAM_STR);
        $cmd->bindValue(":code",$code, PDO::PARAM_STR);
        $cmd->bindValue(":param",$value, PDO::PARAM_STR);

        $cmd->execute();
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('system.Notification'));
    }
}

?>
