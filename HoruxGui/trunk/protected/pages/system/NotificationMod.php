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

Prado::using('horux.pages.system.sql');

class NotificationMod extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {
            $userId=$this->Application->getUser()->getUserId();
            $this->blockRecord('hr_notification', $this->Request['id'], $userId);

            $this->superUserList->DataSource = $this->SUList;
            $this->superUserList->dataBind();

            $this->userList->DataSource = $this->UList;
            $this->userList->dataBind();

            $this->id->Value = $this->Request['id'];
            $this->setData();

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

    protected function setData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_GET_NOTIFICATION );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
             $data = $query->read();
             $this->id->Value = $data['id'];
             $this->name->Text = $data['name'];
             $this->emailToSend->Text = $data['emails'];
             $this->comment->Text = $data['description'];
        }

        for( $i=0; $i< $this->superUserList->ItemCount; $i++)
        {
            $item = $this->superUserList->Items[$i];
            $cmd = $this->db->createCommand( SQL::SQL_GET_NOTIFICATION_SU );
            $cmd->bindParameter(":id_notification", $this->id->Value, PDO::PARAM_STR);
            $cmd->bindParameter(":id_superuser", $item->Value, PDO::PARAM_STR);
            $query = $cmd->query();
            if($query)
            { 
                 $data = $query->read();
                 if($data['n'] > 0)
                    $this->superUserList->Items[$i]->setSelected(true);
            }
        }

        for( $i=0; $i< $this->userList->ItemCount; $i++)
        {
            $item = $this->userList->Items[$i];
            $cmd = $this->db->createCommand( SQL::SQL_GET_NOTIFICATION_CODE_USER );
            $cmd->bindParameter(":id_notification", $this->id->Value, PDO::PARAM_STR);
            $cmd->bindParameter(":param", $item->Value, PDO::PARAM_STR);
            $query = $cmd->query();
            if($query)
            {
                 $data = $query->read();
                 if($data['n'] > 0)
                    $this->userList->Items[$i]->setSelected(true);
            }
        }

        $this->setCheckBox($this->n_t_1, "ACCESS", 4);
        $this->setCheckBox($this->n_t_2, "ACCESS", 5);
        $this->setCheckBox($this->n_t_3, "ACCESS", 6);
        $this->setCheckBox($this->n_t_4, "ACCESS", 7);
        $this->setCheckBox($this->n_t_5, "ACCESS", 8);
        $this->setCheckBox($this->n_t_6, "ACCESS", 9);
        $this->setCheckBox($this->n_t_7, "ACCESS", 2);
        $this->setCheckBox($this->n_t_8, "ACCESS", 3);
        $this->setCheckBox($this->n_t_9, "ACCESS", 1);
        $this->setCheckBox($this->n_t_10, "ACCESS", 11);

        $this->setCheckBox($this->n_1001, "ALARM", 1001);
        $this->setCheckBox($this->n_1002, "ALARM", 1002);
        $this->setCheckBox($this->n_1003, "ALARM", 1003);
        $this->setCheckBox($this->n_1004, "ALARM", 1004);
        $this->setCheckBox($this->n_1005, "ALARM", 1005);
        $this->setCheckBox($this->n_1006, "ALARM", 1006);
        $this->setCheckBox($this->n_1007, "ALARM", 1007);
        $this->setCheckBox($this->n_1008, "ALARM", 1008);
        $this->setCheckBox($this->n_1009, "ALARM", 1009);
        $this->setCheckBox($this->n_1010, "ALARM", 1010);
        $this->setCheckBox($this->n_1011, "ALARM", 1011);
        $this->setCheckBox($this->n_1012, "ALARM", 1012);
        $this->setCheckBox($this->n_1013, "ALARM", 1013);
        $this->setCheckBox($this->n_1014, "ALARM", 1014);
        $this->setCheckBox($this->n_1015, "ALARM", 1015);
        $this->setCheckBox($this->n_1016, "ALARM", 1016);
        $this->setCheckBox($this->n_1200, "ALARM", 1200);

    }

    protected function setCheckBox($cb, $type, $code)
    {
            $cmd = $this->db->createCommand( SQL::SQL_GET_NOTIFICATION_CODE );
            $cmd->bindParameter(":id_notification", $this->id->Value, PDO::PARAM_STR);
            $cmd->bindParameter(":type", $type, PDO::PARAM_STR);
            $cmd->bindParameter(":code", $code, PDO::PARAM_STR);

            $query = $cmd->query();
            if($query)
            {
                 $data = $query->read();
                 if($data['n'] > 0) 
                    $cb->setChecked(true);
            }

    }

	public function onCancel($sender, $param)
	{
		$this->blockRecord('hr_notification', $this->id->Value, 0);
        $this->Response->redirect($this->Service->constructUrl('system.Notification'));
	}

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The notification was modified successfully'), 'id'=>$this->id->Value);
            $this->Response->redirect($this->Service->constructUrl('system.NotificationMod', $pBack));
          }
          else
          {
            $pBack = array('koMsg'=>Prado::localize('The notification was not modified'), 'id'=>$this->id->Value);
            $this->Response->redirect($this->Service->constructUrl('system.NotificationMod', $pBack));
          }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The notification was modified successfully'));
          }
          else
            $pBack = array('koMsg'=>Prado::localize('The notification was not modified'));

          $this->blockRecord('hr_notification', $this->id->Value, 0);
          $this->Response->redirect($this->Service->constructUrl('system.Notification',$pBack));
        }
    }

    protected function saveData()
    {
        $res1 = $res2 = $res3 = $res4 = true;


        $cmd = $this->db->createCommand( SQL::SQL_NOTIFICATION_UPDATE );
        $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);
        $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":emails",$this->emailToSend->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":description",$this->comment->SafeText, PDO::PARAM_STR);

        $res1 = $cmd->execute();
        $lastId = $this->id->Value;

        $cmd=$this->db->createCommand(SQL::SQL_REMOVE_NOTIFICATION_CODE);
        $cmd->bindParameter(":id",$lastId);
        $cmd->execute();

        $cmd=$this->db->createCommand(SQL::SQL_REMOVE_NOTIFICATION_SU);
        $cmd->bindParameter(":id",$lastId);
        $cmd->execute();

        $indices=$this->superUserList->SelectedIndices;
        foreach($indices as $index)
        {
            $item = $this->superUserList->Items[$index];
            $cmd = $this->db->createCommand( SQL::SQL_NOTIFICATION_SU );
            $cmd->bindParameter(":id_notification",$lastId,PDO::PARAM_STR);
            $cmd->bindParameter(":id_superuser",$item->Value, PDO::PARAM_STR);

            $res2 = $cmd->execute();
        }

        $indices=$this->userList->SelectedIndices;
        foreach($indices as $index)
        {
            $item = $this->userList->Items[$index];
            $cmd = $this->db->createCommand( SQL::SQL_NOTIFICATION_CODE );
            $cmd->bindParameter(":id_notification",$lastId,PDO::PARAM_STR);
            $type = "ACCESS";
            $cmd->bindParameter(":type",$type, PDO::PARAM_STR);
            $code = 0;
            $cmd->bindParameter(":code",$code, PDO::PARAM_STR);
            $cmd->bindParameter(":param",$item->Value, PDO::PARAM_STR);

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

        $this->log("Modify the notification: ".$this->name->SafeText);

	    return 1;
    }

    protected function insertNotificationCode($not_id, $type, $code, $value='')
    {
        $cmd = $this->db->createCommand( SQL::SQL_NOTIFICATION_CODE );
        $cmd->bindParameter(":id_notification",$not_id,PDO::PARAM_STR);
        $cmd->bindParameter(":type",$type, PDO::PARAM_STR);
        $cmd->bindParameter(":code",$code, PDO::PARAM_STR);
        $cmd->bindParameter(":param",$value, PDO::PARAM_STR);

        $cmd->execute();
    }
}

?>
