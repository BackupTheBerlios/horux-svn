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

class Notification
{
    /**
     * @param array $param parameters of the notification
     * @return string return the result
     * @soapmethod
     */
    public function sendMail($param)
    {
        $ret = "";
        $p_tmp = array();
        foreach($param as $p)
        {
             $key = "";
             //$param[$v[0]] = $v[1];
             foreach($p as $k=>$v)
             {
                if($k == "key")
                    $key = $v;
                if($k== "value")
                {
                   $p_tmp[$key] = $v;
                   $ret .= $key.":".$v.",";
                }
             }
        }

        $param = $p_tmp;

        $type = $param["type"];
        $code = $param["code"];

        $userId = false;
        $serialNumber = false;
        $entryId = false;
        $object = false;


        switch($type)
        {
            case "ALARM":
                $object = $param["object"];
                break;
            case "ACCESS":
                $userId = $param["userId"];
                $serialNumber = $param["serialNumber"];
                $entryId = $param["entryId"];
                break;
            default:
                return "Type mismatch";
        }

		$app = Prado::getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;
        $db->Active=true;
        
        $sql = "SELECT * FROM hr_notification";

        $cmd= $db->createCommand($sql);
        $data = $cmd->query();
        $data = $data->readAll();

        foreach($data as $notification)
        {
            $not_id = $notification['id'];
            $emails = explode(',', $notification['emails']);

            $sql = "SELECT su.email FROM hr_notification_su AS nsu LEFT JOIN hr_superusers AS su ON su.id=nsu.id_superuser WHERE nsu.id_notification=$not_id";
            $cmd= $db->createCommand($sql);
            $sus = $cmd->query();
            $sus = $sus->readAll();
            foreach($sus as $su)
               $emails[] = $su['email'];

            $sql = "SELECT * FROM hr_notification_code WHERE id_notification=$not_id AND type='$type' AND code='$code'";

            $cmd= $db->createCommand($sql);
            $ncode = $cmd->query();
            $ncode = $ncode->read();

            if($ncode)
            {
                $mailer = new TMailer();

                if($type == "ALARM")
                {
                    $sql = "SELECT * FROM hr_device WHERE id=$object";
                    $cmd= $db->createCommand($sql);
                    $device = $cmd->query();
                    $device = $device->read();
                    $device = $device['name'];

                    $site = "";
                    $sql = "SELECT * FROM hr_site WHERE id=1";
                    $cmd= $db->createCommand($sql);
                    $site = $cmd->query();
                    $site = $site->read();
                    $site = $site['name'];

                    $sql = "SELECT * FROM hr_install WHERE `default`=1 AND type='language'";
                    $cmd=$db->createCommand($sql);
                    $data = $cmd->query();
                    $data = $data->read();
                    $lang = $data['param'];
                    Prado::getApplication()->getGlobalization()->setCulture($lang);
                    $body = "";


                    $body = file_get_contents("./protected/webservice/notification/alarm/$lang/$code.txt");

                    $body = str_replace("%site%", $site, $body);
                    $body = str_replace("%device%", $device, $body);
                    $body = str_replace("%date%", date("d.m.y"), $body);
                    $body = str_replace("%time%", date("H:i:s"), $body);

                    switch($code)
                    {
                        case 1001:
                            $mailer->setObject(Prado::localize("Horux notification: Antivandale acivated on {device}", array('device'=>$device)));
                            break;
                        case 1002:
                            $mailer->setObject(Prado::localize("Horux notification: Antivandale cleared on {device}", array('device'=>$device)));
                            break;
                        case 1003:
                            $mailer->setObject(Prado::localize("Horux notification: Device communication opened on {device}", array('device'=>$device)));
                            break;
                        case 1004:
                            $mailer->setObject(Prado::localize("Horux notification: Device communication closed on {device}", array('device'=>$device)));
                            break;
                        case 1005:
                            $mailer->setObject(Prado::localize("Horux notification: Device ajar on {device}", array('device'=>$device)));
                            break;
                        case 1006:
                            $mailer->setObject(Prado::localize("Horux notification: End device ajar on {device}", array('device'=>$device)));
                            break;
                        case 1007:
                            $mailer->setObject(Prado::localize("Horux notification: Door forced on {device}", array('device'=>$device)));
                            break;
                        case 1008:
                            $mailer->setObject(Prado::localize("Horux notification: Too many PIN on {device}", array('device'=>$device)));
                            break;
                        case 1009:
                            $mailer->setObject(Prado::localize("Horux notification: Temperature alarm on {device}", array('device'=>$device)));
                            break;
                        case 1010:
                            $mailer->setObject(Prado::localize("Horux notification: Memory full on {device}", array('device'=>$device)));
                            break;
                        case 1011:
                            $mailer->setObject(Prado::localize("Horux notification: Memory warning on {device}", array('device'=>$device)));
                            break;
                        case 1012:
                            $mailer->setObject(Prado::localize("Horux notification: Memory key inserted error on {device}", array('device'=>$device)));
                            break;
                        case 1013:
                            $mailer->setObject(Prado::localize("Horux notification: Memory key removed error on {device}", array('device'=>$device)));
                            break;
                        case 1014:
                            $mailer->setObject(Prado::localize("Horux notification: Device antenna enabled on {device}", array('device'=>$device)));
                            break;
                        case 1015:
                            $mailer->setObject(Prado::localize("Horux notification: Device antenna disabled on {device}", array('device'=>$device)));
                            break;
                        case 1016:
                            $mailer->setObject(Prado::localize("Horux notification: Device connection not opened on {device}", array('device'=>$device)));
                            break;
                        case 1200:
                            $mailer->setObject(Prado::localize("Horux notification: Cannot start Horux XMLRPC server"));
                            break;
                    }
                }

                if($type == "ACCESS")
                {
                    $user = "";
                    if($userId && $userId>0)
                    {
                        $sql = "SELECT * FROM hr_user WHERE id=$userId";

                        $cmd= $db->createCommand($sql);
                        $user = $cmd->query();
                        $user = $user->read();
                        $user = $user['name'].' '.$user['firstname'];
                    }

                    $deviceName = "";
                    if($entryId && $entryId>0)
                    {
                        $sql = "SELECT * FROM hr_device WHERE id=$entryId";

                        $cmd= $db->createCommand($sql);
                        $entryId = $cmd->query();
                        $entryId = $entryId->read();
                        $deviceName = $entryId['name'];
                    }

                    $site = "";
                    $sql = "SELECT * FROM hr_site WHERE id=1";
                    $cmd= $db->createCommand($sql);
                    $site = $cmd->query();
                    $site = $site->read();
                    $site = $site['name'];

                    $sql = "SELECT * FROM hr_install WHERE `default`=1 AND type='language'";
                    $cmd=$db->createCommand($sql);
                    $data = $cmd->query();
                    $data = $data->read();
                    $lang = $data['param'];
                    $this->getApplication()->getGlobalization()->setCulture($lang);
                    $body = "";

                    $body = file_get_contents("./protected/webservice/notification/access/$lang/$code.txt");

                    $body = str_replace("%user%", $user, $body);
                    $body = str_replace("%site%", $site, $body);
                    $body = str_replace("%device%", $deviceName, $body);
                    $body = str_replace("%key%", $serialNumber, $body);
                    $body = str_replace("%date%", date("d.m.y"), $body);
                    $body = str_replace("%time%", date("H:i:s"), $body);


                    switch($code)
                    {
                        case 0:
                            $mailer->setObject(Prado::localize("Horux notification: Access by {user}", array('user'=>$user)));

                            break;
                        case 1:
                            $mailer->setObject(Prado::localize("Horux notification: Acces bloked - Key blocked"));
                            break;
                        case 2:
                            $mailer->setObject(Prado::localize("Horux notification: Acces bloked - Key unknown"));
                            break;
                        case 3:
                            $mailer->setObject(Prado::localize("Horux notification: Acces bloked - Key not attributed"));
                            break;
                        case 4:
                            $mailer->setObject(Prado::localize("Horux notification: Acces bloked - User not in a group"));
                            break;
                        case 5:
                            $mailer->setObject(Prado::localize("Horux notification: Acces bloked - Key blocked during the week-end"));
                            break;
                        case 6:
                            $mailer->setObject(Prado::localize("Horux notification: Acces bloked - Key blocked during the non working day"));
                            break;
                        case 7:
                            $mailer->setObject(Prado::localize("Horux notification: Acces bloked - Out of date"));
                            break;
                        case 8:
                            $mailer->setObject(Prado::localize("Horux notification: Acces bloked - Out of time"));
                            break;
                        case 9:
                            $mailer->setObject(Prado::localize("Horux notification: Acces bloked - No access right defined for the group"));
                            break;
                        case 11:
                            $mailer->setObject(Prado::localize("Horux notification: Acces bloked - User blocked"));
                            break;
                    }
                }


                foreach($emails as $email)
                    $mailer->addRecipient($email);

                $mailer->setBody($body);

                $mailer->sendTextMail(true);
            }
        }

        return "ok";
    }

    /*public function dispatch($params)
    {
        if($params->getNumParams() == 1)
        {
            $param = $params->getParam(0);

            $password = $param->structmem("password");
            if($password)
                $password = $password->scalarval();
                
            $username = $param->structmem("username");
            if($username)
                $username = $username->scalarval();

            $app = $this->getApplication();
            $db = $app->getModule('horuxDb')->DbConnection;
            $db->Active=true;

            if($username != $db->getUsername()  || $password != $db->getPassword() )
            {
                return new XML_RPC_Response(0, 1, "Bad authentication");
            }

            $class = $param->structmem("class");
            if($class)
                $class = $class->scalarval();
            $function = $param->structmem("function");
            if($function)
                $function = $function->scalarval();

            if($class && $function)
            {
                Prado::using($class);
                $service = new $class;
                $service->$function($param);
            }
            else
            {
                $this->sendMail($param);
            }
            
            return "ok";
        }
        else
        {
           return "Bad parameter";
        }
    }*/
}

?>