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

class TimuxModule extends TComponentModule
{
    public function cleanData($db, $userId)
    {
        $sql = "DELETE FROM hr_timux_activity_counter WHERE user_id=:id";
		$cmd = $db->createCommand($sql);
        $cmd->bindValue(":id",$userId);
        $cmd->execute();

        $sql = "DELETE FROM hr_timux_request_leave WHERE request_id IN (SELECT id FROM hr_timux_request WHERE userId=:id )";
		$cmd = $db->createCommand($sql);
        $cmd->bindValue(":id",$userId);
        $cmd->execute();


        $sql = "DELETE FROM hr_timux_request WHERE userId=:id";
		$cmd = $db->createCommand($sql);
        $cmd->bindValue(":id",$userId);
        $cmd->execute();

        $sql = "DELETE FROM hr_timux_request_workflow WHERE user_id=:id";
		$cmd = $db->createCommand($sql);
        $cmd->bindValue(":id",$userId);
        $cmd->execute();

        $sql = "DELETE FROM hr_timux_workingtime WHERE user_id=:id";
		$cmd = $db->createCommand($sql);
        $cmd->bindValue(":id",$userId);
        $cmd->execute();

        $sql = "DELETE FROM hr_timux_booking WHERE tracking_id IN (SELECT id FROM hr_tracking WHERE id_user=:id )";
		$cmd = $db->createCommand($sql);
        $cmd->bindValue(":id",$userId);
        $cmd->execute();

        $sql = "DELETE FROM hr_tracking WHERE id_user=:id";
		$cmd = $db->createCommand($sql);
        $cmd->bindValue(":id",$userId);
        $cmd->execute();

        $sql = "DELETE FROM hr_superusers WHERE user_id=:id";
		$cmd = $db->createCommand($sql);
        $cmd->bindValue(":id",$userId);
        $cmd->execute();


    }

    public function saveData($db, $form, $userId)
    {

        $name = $form->findControlsByID('su_username');
        $password = $form->findControlsByID('su_password');
        $group_id = $form->findControlsByID('group_id');
        $email1 = $form->findControlsByID('email1');
        $email2 = $form->findControlsByID('email2');

        $cmd = $db->createCommand( "INSERT INTO hr_superusers (
                        `group_id` ,
                        `user_id` ,
                        `name`,
                        `password`,
                        `isLogged`
                  )
                  VALUES (
                        :group_id,
                        :user_id,
                        :name,
                        :password,
                        0
                  )");
        
        $cmd->bindValue(":name",$name[0]->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":password",sha1($password[0]->SafeText),PDO::PARAM_STR);
        $cmd->bindValue(":group_id",$group_id[0]->getSelectedValue(),PDO::PARAM_INT);
        $cmd->bindValue(":user_id",$userId,PDO::PARAM_INT);


        $cmd->execute();

        $guiLog = new TGuiLog();
        $guiLog->log("Add the super user:".$name[0]->SafeText);

        if($email1[0]->SafeText != '' || $email2[0]->SafeText != '')
        {
            
            if($email2[0]->SafeText != '')
            {
                $mailer = new TMailer();
                $mailer->sendSuperUser($email2[0]->SafeText,$name[0]->SafeText, $password[0]->SafeText);
            }
            else
            {
                $mailer = new TMailer();
                $mailer->sendSuperUser($email1[0]->SafeText,$name[0]->SafeText, $password[0]->SafeText);
            }
        }

    }

    public function setData($db, $form)
    {
        $cmd= $db->createCommand("SELECT * FROM hr_superuser_group");
        $data=$cmd->query();
        $data = $data->readAll();

        $s = $form->findControlsByID('group_id');

        $s[0]->DataSource=$data;
        $s[0]->dataBind();
    }
}

?>
