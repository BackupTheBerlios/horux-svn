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

class VeloparkModule extends TComponentModule {
    public function cleanData($db, $userId) {
        $sql = "UPDATE hr_vp_subscription_attribution SET hr_vp_subscription_attribution.status='finished' WHERE user_id=:id";
        $cmd = $db->createCommand($sql);
        $cmd->bindValue(":id",$userId);
        $cmd->execute();

        $sql = "INSERT INTO hr_vp_deleted_user (user_id) VALUES (:id)";
        $cmd = $db->createCommand($sql);
        $cmd->bindValue(":id",$userId);
        $cmd->execute();
    }

    public function saveData($db, $form, $userId) {
        $s = $form->findControlsByID('Subscription');

        $subId = $s[0]->getSelectedValue();

        if($subId == 0) {
            return;
        }

        $sql = "SELECT COUNT(*) AS n FROM hr_vp_subscription_attribution WHERE user_id=:id AND status='started'";
        $cmd=$db->createCommand($sql);
        $cmd->bindValue(":id",$userId);
        $data = $cmd->query();
        $data = $data->read();

        $nStarted = $data['n'];

        $sql = "SELECT * FROM hr_vp_subscription WHERE id=:id";
        $cmd=$db->createCommand($sql);
        $cmd->bindValue(":id",$subId);
        $data = $cmd->query();
        $data = $data->read();


        // does we have already a subscrion for this user?
        if($nStarted>0) {

            if($data["multiticket"]==1) {
                $sql = "INSERT INTO hr_vp_subscription_attribution (user_id, subcription_id, create_date, status, credit, start, end, create_by,multiticket) VALUES (:user_id,  :subcription_id, NOW(), 'not_start', :credit, 'NULL', 'NULL', :create_by,1)";
            }
            else {
                // is the current is multiticket
                $sql = "SELECT * FROM hr_vp_subscription_attribution WHERE user_id=:id AND status='started'  AND multiticket=1";
                $cmd=$db->createCommand($sql);
                $cmd->bindValue(":id",$userId);
                $datat = $cmd->query();
                $datat = $datat->read();

                if($datat) {

                    // set the multiticket in the waiting status
                    $sql = "UPDATE hr_vp_subscription_attribution SET status='waiting' WHERE id=".$datat['id'];
                    $cmd=$db->createCommand($sql);
                    $cmd->execute();

                    $validity = explode(':', $data["validity"]);
                    $nHours = ($validity[0]*365*24) + ($validity[1]*30*24) + ($validity[2]*24) + ($validity[3]);

                    $sql = "INSERT INTO hr_vp_subscription_attribution (user_id, subcription_id, create_date, status, credit, start, end, create_by,multiticket) VALUES (:user_id,  :subcription_id, NOW(), 'started', :credit, NOW(), NOW()+ INTERVAL ".$nHours." HOUR, :create_by,0)";
                }
                else {
                    $sql = "INSERT INTO hr_vp_subscription_attribution (user_id, subcription_id, create_date, status, credit, start, end, create_by,multiticket) VALUES (:user_id,  :subcription_id, NOW(), 'not_start', :credit, 'NULL', 'NULL', :create_by,0)";

                }


            }
        }
        else {
            $validity = explode(':', $data["validity"]);
            $nHours = ($validity[0]*365*24) + ($validity[1]*30*24) + ($validity[2]*24) + ($validity[3]);

            // is a multi ticket, if yes, do not set de end and the start
            if($data["multiticket"]==1) {
                $sql = "INSERT INTO hr_vp_subscription_attribution (user_id, subcription_id, create_date, status, credit, start, end, create_by,multiticket) VALUES (:user_id,  :subcription_id, NOW(), 'started', :credit, 'NULL', 'NULL', :create_by,1)";
            }
            else {
                $sql = "INSERT INTO hr_vp_subscription_attribution (user_id, subcription_id, create_date, status, credit, start, end, create_by,multiticket) VALUES (:user_id,  :subcription_id, NOW(), 'started', :credit, NOW(), NOW()+ INTERVAL ".$nHours." HOUR, :create_by,0)";
            }

        }

        $cmd=$db->createCommand($sql);

        $cmd->bindValue(":user_id",$userId,PDO::PARAM_STR);
        $cmd->bindValue(":subcription_id",$subId,PDO::PARAM_STR);

        if($data["multiticket"] == 1)
            $cmd->bindValue(":credit",$data["credit"],PDO::PARAM_STR);
        else {
            $cmd->bindValue(":credit",0,PDO::PARAM_STR);
        }


        $user = $this->Application->getUser();
        $createBy = $user->getName() ;
        $cmd->bindValue(":create_by",$createBy,PDO::PARAM_STR);

        $cmd->execute();
    }

    public function setData($db, $form) {
        $sql = "SELECT * FROM  hr_vp_subscription";

        $cmd  = $db->createCommand($sql);
        $data = $cmd->query();
        $data = $data->readAll();

        $s = $form->findControlsByID('Subscription');

        $s[0]->DataSource=$data;
        $s[0]->dataBind();
    }
}

?>
