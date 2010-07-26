<?php
/**
 * @version      $Id$
 * @package      Horux
 * @subpackage   Horux
 * @copyright    Copyright (C) 2007  Letux. All rights reserved.
 * @license      GNU/GPL, see LICENSE.php
 * Horus is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */


class attribution extends PageList {
    public function onLoad($param) {
        parent::onLoad($param);

        if(!$this->IsPostBack) {
            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();

            $sql = "SELECT id AS Value, name AS Text FROM hr_vp_subscription";

            $cmd=$this->db->createCommand($sql);
            $data = $cmd->query();
            $data = $data->readAll();

            $c[] = array("Value"=>0, "Text"=>Prado::localize("-- Select one --"));

            $data = array_merge($c,$data);

            $this->subscription->DataSource=$data;
            $this->subscription->dataBind();

            $this->userId->Value = $this->Request['id'];

        }

        if(isset($this->Request['okMsg'])) {
            $this->displayMessage($this->Request['okMsg'], true);
        }
        if(isset($this->Request['koMsg'])) {
            $this->displayMessage($this->Request['koMsg'], false);
        }
    }

    protected function getData() {
        $sql = "SELECT sa.*, s.name AS type, s.description, s.credit AS totalCredit, end FROM hr_vp_subscription_attribution AS sa LEFT JOIN hr_vp_subscription AS s ON s.id=sa.subcription_id WHERE user_id=".$this->Request['id']." ORDER BY id DESC";

        $cmd=$this->db->createCommand($sql);
        $data = $cmd->query();
        $data = $data->readAll();

        foreach($data as $k=>$v) {
            $tmp = $data[$k]["status"];

            $end = explode(" ", $data[$k]["end"]);
            $endDate = explode("-",$end[0]);
            $endTime = explode(":",$end[1]);
            $end = mktime($endTime[0],$endTime[1],$endTime[2],$endDate[1], $endDate[2],$endDate[0]);

            $curentTimeDate = mktime();

            if($end < $curentTimeDate && $data[$k]["end"]!='0000-00-00 00:00:00' && $tmp != 'not_start' && $tmp != 'canceled' )
                $tmp = 'finished';


            switch($tmp) {
                case "not_start":
                    $tmp = "<span style=\"color:black\">".Prado::localize("Not started")."</span>";
                    break;
                case "started":
                    $tmp = "<span style=\"color:green\">".Prado::localize("Started")."</span>";
                    break;
                case "finished":
                    $tmp = "<span style=\"color:red\">".Prado::localize("Finished")."</span>";
                    break;
                case "canceled":
                    $tmp = "<span style=\"color:red\">".Prado::localize("Canceled")."</span>";
                    break;
                case "waiting":
                    $tmp = "<span style=\"color:orange\">".Prado::localize("Waiting")."</span>";
                    break;
            }

            $data[$k]["status_text"] = $tmp;

            $data[$k]["credit"] = ($data[$k]["totalCredit"] - $data[$k]["credit"])." / ".$data[$k]["totalCredit"];

        }

        return $data;
    }

    public function attribute($sender, $param) {

        $subId = $this->subscription->getSelectedValue();

        if($subId == 0) {
            $koMsg = Prado::localize("Please, select one subscription in the list");
            $pBack = array('koMsg'=>$koMsg,'id'=>$this->userId->Value);
            $this->Response->redirect($this->Service->constructUrl('components.velopark.attribution',$pBack));
            return;
        }

        $sql = "SELECT COUNT(*) AS n FROM hr_vp_subscription_attribution WHERE user_id=:id AND status='started'";
        $cmd=$this->db->createCommand($sql);
        $cmd->bindValue(":id",$this->userId->Value);
        $data = $cmd->query();
        $data = $data->read();

        $nStarted = $data['n'];

        $sql = "SELECT * FROM hr_vp_subscription WHERE id=:id";
        $cmd=$this->db->createCommand($sql);
        $cmd->bindValue(":id",$subId);
        $data = $cmd->query();
        $data = $data->read();

        $insertAC = false;
        
        // does we have already a subscrion for this user?
        if($nStarted>0) {

            if($data["multiticket"]==1) {
                $sql = "INSERT INTO hr_vp_subscription_attribution (user_id, subcription_id, create_date, status, credit, start, end, create_by,multiticket) VALUES (:user_id,  :subcription_id, NOW(), 'not_start', :credit, 'NULL', 'NULL', :create_by,1)";
            }
            else {
                // is the current is multiticket
                $sql = "SELECT * FROM hr_vp_subscription_attribution WHERE user_id=:id AND status='started'  AND multiticket=1";
                $cmd=$this->db->createCommand($sql);
                $cmd->bindValue(":id",$this->userId->Value);
                $datat = $cmd->query();
                $datat = $datat->read();

                if($datat) {

                    // set the multiticket in the waiting status
                    $sql = "UPDATE hr_vp_subscription_attribution SET status='waiting' WHERE id=".$datat['id'];
                    $cmd=$this->db->createCommand($sql);
                    $cmd->execute();

                    $validity = explode(':', $data["validity"]);
                    $nHours = ($validity[0]*365*24) + ($validity[1]*30*24) + ($validity[2]*24) + ($validity[3]);

                    $sql = "INSERT INTO hr_vp_subscription_attribution (user_id, subcription_id, create_date, status, credit, start, end, create_by,multiticket) VALUES (:user_id,  :subcription_id, NOW(), 'started', :credit, NOW(), NOW()+ INTERVAL ".$nHours." HOUR, :create_by,0)";

                    $insertAC = true;
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

            $insertAC = true;

        }

        $cmd=$this->db->createCommand($sql);

        $cmd->bindValue(":user_id",$this->userId->Value,PDO::PARAM_STR);
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

        if($insertAC) {

            $sql = "SELECT * FROM hr_keys_attribution WHERE id_user=(SELECT user_id FROM hr_vp_subscription_attribution ORDER BY id DESC LIMIT 0,1)";
            $cmd=$this->db->createCommand($sql);
            $cmd->bindValue(":id",$cb->Value);
            $datat = $cmd->query();
            $datat = $datat->read();

            if($datat)
            {
                //new subscription with the status started, we have to add the user in the access control system
                $this->addStandalone('add', $datat['id_key']);
            }
        }

        $pBack = array('id'=>$this->userId->Value);
        $this->Response->redirect($this->Service->constructUrl('components.velopark.attribution',$pBack));
    }

    public function checkboxAllCallback($sender, $param) {
        $cbs = $this->findControlsByType("TActiveCheckBox");
        $isChecked = $sender->getChecked();

        foreach($cbs as $cb) {
            $cb->setChecked($isChecked);
        }

    }

    public function onDelete($sender,$param) {
        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        $koMsg = '';
        $cbChecked = 0;

        foreach($cbs as $cb) {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
                $cbChecked++;
        }

        if($cbChecked==0) {
            $koMsg = Prado::localize('Select one item');
        }
        else {
            foreach($cbs as $cb) {
                if( (bool)$cb->getChecked() && $cb->Value != "0") {
                    $cmd=$this->db->createCommand("UPDATE hr_vp_subscription_attribution SET status='canceled' WHERE id=:id");
                    $cmd->bindValue(":id",$cb->Value);
                    if($cmd->execute())
                    {
                        //check if the user has a subscription with the status "stared" or "waiting". If not, we need to remove the user from the access control
                        $sql = "SELECT * FROM hr_vp_subscription_attribution WHERE user_id=(SELECT user_id FROM hr_vp_subscription_attribution WHERE id=:id) AND (status='started' OR status='waiting' )";
                        $cmd=$this->db->createCommand($sql);
                        $cmd->bindValue(":id",$cb->Value);
                        $datat = $cmd->query();
                        $datat = $datat->read();

                        if(!$datat) {
                            $sql = "SELECT * FROM hr_keys_attribution WHERE id_user=(SELECT user_id FROM hr_vp_subscription_attribution WHERE id=:id)";
                            $cmd=$this->db->createCommand($sql);
                            $cmd->bindValue(":id",$cb->Value);
                            $datat = $cmd->query();
                            $datat = $datat->read();

                            $this->addStandalone('sub', $datat['id_key']);
                        }

                        $nDelete++;
                    }

                }
            }
        }

        if($koMsg !== '')
            $pBack = array('koMsg'=>$koMsg, 'id'=>$this->userId->Value);
        else
            $pBack = array('okMsg'=>Prado::localize('{n} subscription attributed was deleted',array('n'=>$nDelete)), 'id'=>$this->userId->Value);
        $this->Response->redirect($this->Service->constructUrl('components.velopark.attribution',$pBack));
    }

    public function printTicket($sender, $param) {
        $param = $this->Application->getParameters();

        if(file_exists("./protected/pages/components/velopark/printtemplate/".$param["printtemplate"].".php")) {
            include("./protected/pages/components/velopark/printtemplate/".$param["printtemplate"].".php");
            exit;
        }
        else {
            $koMsg = Prado::localize("The template {tmpname} does not exist", array("tmpname"=>$param["printtemplate"]));
            $pBack = array('koMsg'=>$koMsg, 'id'=>$this->userId->Value);
            $this->Response->redirect($this->Service->constructUrl('components.velopark.attribution',$pBack));
        }
    }

    protected function addStandalone($function, $idkey)
    {
        $sa = new TStandAlone();
        $sa->addStandalone($function, $idkey, 'UserAttributionKey');
    }
}

?>