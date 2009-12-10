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

class gantner_TimeTerminal_standalone extends TDeviceStandalone
{
    private $db = NULL;

    public function init($config)
    {
        parent::init($config);

    }

    public function addStandalone($function, $id, $param=NULL)
    {
        // do not handle a block or unblock function
        /*if($function == 'block' || $function == 'unblock')
        {
            return;
        }*/

        $this->db = $this->Application->getModule('horuxDb')->DbConnection;
        $this->db->Active=true;

        switch($param)
        {
            case 'KeyList': // del,block,unblock key
                if($function == 'block' || $function == 'unblock')
                {
                   $function =  $function == 'block' ? 'sub' : 'add';
                }

                $this->addSubKey($function, $id);
                break;
            case 'KeyAdd':  // add a key with a user relation
                $this->addSubKey($function, $id);
                break;
            case 'KeyMod':  // mod a key
                $this->addSubKey($function, $id);
                break;
            case 'UserListAdd': // add a new user
                $this->addSubUser($function, $id);
                break;
            case 'UserListMod': // modify the user data
                $this->addSubUser($function, $id);
                break;
            case 'UserList': //block/unblock the user
                if($function == 'block' || $function == 'unblock')
                {
                   $function =  $function == 'block' ? 'sub' : 'add';
                   $this->addSubAllKeys($function, $id);
                }
                else
                    $this->addSubUser($function, $id);
                break;
            case 'UserWizzard':
                $this->addSubUser($function, $id);
                break;
            case 'UserAttributionKey':
                if($function == 'block' || $function == 'unblock')
                {
                   $function =  $function == 'block' ? 'sub' : 'add';
                }
                $this->addSubKey($function, $id);
                break;
            case 'UserAttributionGroup':
                $this->addSubGroup($function, $id);
                break;
            case 'UserGroupMod':
                $this->addSubGroup($function, $id);
                break;
            case 'timuxAddSubReason':
                $this->addSubReason($function, $id);
                break;
            case 'timuxAddBalances':
                $this->addBalances($id);
                break;
            case 'timuxReinit':
                $this->reinit($id);
                break;
        }
    }

    protected function reinit($deviceId)
    {
        $type = "reinit";
        $func = "add";
        $keyId = 0;
        $userId = 0;

        $cmd=$this->db->createCommand("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `userId`,`keyId`, `deviceId`) VALUES (:type,:func,:userId,:keyId,:deviceId)");
        $cmd->bindParameter(":type", $type);
        $cmd->bindParameter(":func", $func);
        $cmd->bindParameter(":userId", $userId );
        $cmd->bindParameter(":keyId", $keyId );
        $cmd->bindParameter(":deviceId", $deviceId );
        $cmd->execute();

    }

    protected function addBalances($userId)
    {
        $cmd=$this->db->createCommand("SELECT * FROM hr_gantner_TimeTerminal");
        $data = $cmd->query();
        $data = $data->readAll();

        foreach($data as $d)
        {
            $deviceId = $d['id_device'];

            $cmd=$this->db->createCommand("SELECT * FROM hr_timux_activity_counter AS ac LEFT JOIN hr_timux_timecode AS t ON t.id=ac.timecode_id WHERE year=0 AND month=0 AND ( type='overtime' OR type='leave') AND ac.user_id=".$userId."  ORDER BY t.type");
            $data2 = $cmd->query();
            $data2 = $data2->readAll();

            $hoursText = Prado::localize("hours");
            $daysText = Prado::localize("days");

            $balances = "";
            foreach($data2 as $d)
            {
                if($d['formatDisplay'] == "day")
                    $balances .= sprintf("%.02f",$d['nbre'])." ".$daysText.";";
                else
                    $balances .= sprintf("%.02f",$d['nbre'])." ".$hoursText.";";
            }

            $type = "balances";
            $func = "add";
            $keyId = 0;

            $cmd=$this->db->createCommand("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `userId`,`keyId`, `deviceId`, `param`) VALUES (:type,:func,:userId,:keyId,:deviceId, :param)");
            $cmd->bindParameter(":type", $type);
            $cmd->bindParameter(":func", $func);
            $cmd->bindParameter(":userId", $userId );
            $cmd->bindParameter(":keyId", $keyId );
            $cmd->bindParameter(":deviceId", $deviceId );
            $cmd->bindParameter(":param", $balances  );
            $cmd->execute();
        }
    }

    protected function addSubReason($function, $reasonId)
    {
        $type = "reason";

        $cmd=$this->db->createCommand("SELECT * FROM hr_timux_timecode WHERE id=:id");
        $cmd->bindParameter(":id",$reasonId);
        $data = $cmd->query();
        $data = $data->read();

        $displayText = $data["deviceDisplay"];

        if($data["signtype"] == 'out' || $data["signtype"] == 'in')
        {
            $sign = $data["signtype"] == "out" ? 2 : 1;

            $cmd=$this->db->createCommand("SELECT * FROM hr_gantner_TimeTerminal");
            $data = $cmd->query();
            $data = $data->readAll();

            foreach($data as $d)
            {
                $deviceId = $d['id_device'];

                $cmd=$this->db->createCommand("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `reasonId`, `deviceId`, `param`, `param2`) VALUES (:type, :func, :reasonId, :deviceId, :param, :param2)");
                $cmd->bindParameter(":type",$type);
                $cmd->bindParameter(":func",$function);
                $cmd->bindParameter(":reasonId",$reasonId);
                $cmd->bindParameter(":deviceId",$deviceId);
                $cmd->bindParameter(":param",$displayText);
                $cmd->bindParameter(":param2",$sign);
                $cmd->execute();
            }
        }

        if($data["signtype"] == 'both')
        {
            $reasonId_out = $reasonId.'_OUT';
            $reasonId_in = $reasonId.'_IN';
            $cmd=$this->db->createCommand("SELECT * FROM hr_gantner_TimeTerminal");
            $data = $cmd->query();
            $data = $data->readAll();

            foreach($data as $d)
            {
                $deviceId = $d['id_device'];

                $sign = 2;
                $cmd=$this->db->createCommand("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `reasonId`, `deviceId`, `param`, `param2`) VALUES (:type, :func, :reasonId, :deviceId, :param, :param2)");
                $cmd->bindParameter(":type",$type);
                $cmd->bindParameter(":func",$function);
                $cmd->bindParameter(":reasonId",$reasonId_out);
                $cmd->bindParameter(":deviceId",$deviceId);
                $cmd->bindParameter(":param",$displayText);
                $cmd->bindParameter(":param2",$sign);
                $cmd->execute();

                $sign = 1;
                $cmd=$this->db->createCommand("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `reasonId`, `deviceId`, `param`, `param2`) VALUES (:type, :func, :reasonId, :deviceId, :param, :param2)");
                $cmd->bindParameter(":type",$type);
                $cmd->bindParameter(":func",$function);
                $cmd->bindParameter(":reasonId",$reasonId_in);
                $cmd->bindParameter(":deviceId",$deviceId);
                $cmd->bindParameter(":param",$displayText);
                $cmd->bindParameter(":param2",$sign);
                $cmd->execute();

            }
        }
    }

    protected function addSubAllKeys($function, $userId)
    {
        $cmd=$this->db->createCommand("SELECT t.id, t.serialNumber, t.isBlocked FROM hr_keys_attribution AS ta LEFT JOIN hr_keys AS t ON t.id=ta.id_key WHERE id_user=:id");
        $cmd->bindParameter(":id",$userId);
        $data = $cmd->query();
        $data = $data->readAll();

        //pour chaque groupe
        foreach($data as $d)
        {
            $this->addSubKey($function, $d['id']);

            
            $cmd=$this->db->createCommand("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `userId`,`keyId`, `deviceId`, `param`) VALUES (:type,:func,:userId,:keyId,:deviceId, :param)");
            $cmd->bindParameter(":func",$function);
            $cmd->bindParameter(":type",$type);
            $cmd->bindParameter(":userId",$idperson);
            $cmd->bindParameter(":keyId",$idtag);
            $cmd->bindParameter(":deviceId",$idreader);
            $cmd->bindParameter(":param",$rfid);
            $cmd->execute();
        }
    }

    protected function addSubKey($function, $idkey)
    {
        $cmd=$this->db->createCommand("SELECT * FROM hr_keys WHERE id=:id");
        $cmd->bindParameter(":id",$idkey);
        $data = $cmd->query();
        $data = $data->read();
        
        $type = "key";

        $rfid = $data['serialNumber'];
        $idtag = $data['id'];

        $cmd=$this->db->createCommand("SELECT * FROM hr_keys_attribution WHERE id_key=:id");
        $cmd->bindParameter(":id",$idtag);
        $data2 = $cmd->query();
        $data2 = $data2->readAll();

        //pour chaque groupe
        foreach($data2 as $d2)
        {
            $idperson = $d2['id_user'];

            $cmd=$this->db->createCommand("SELECT * FROM hr_user WHERE id=:id");
            $cmd->bindParameter(":id",$idperson);
            $data_u = $cmd->query();
            $data_u = $data_u->read();


            $cmd=$this->db->createCommand("SELECT gac.id_device FROM hr_user_group_attribution AS ga
                                           LEFT JOIN hr_user_group_access AS gac ON gac.id_group=ga.id_group
                                           LEFT JOIN hr_device AS d ON d.id = gac.id_device
                                           WHERE ga.id_user=:id AND d.type='gantner_TimeTerminal'");

            $cmd->bindParameter(":id",$idperson);
            $data3 = $cmd->query();
            $data3 = $data3->readAll();

            foreach($data3 as $d3)
            {
                $idreader = $d3['id_device'];

                if($idreader == '') continue;

                $cmd=$this->db->createCommand("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `userId`,`keyId`, `deviceId`, `param`) VALUES (:type,:func,:userId,:keyId,:deviceId, :param)");
                $cmd->bindParameter(":func",$function);
                $cmd->bindParameter(":type",$type);
                $cmd->bindParameter(":userId",$idperson);
                $cmd->bindParameter(":keyId",$idtag);
                $cmd->bindParameter(":deviceId",$idreader);
                $cmd->bindParameter(":param",$rfid);
                $cmd->execute();
            }

        }
    }

    protected function addSubUser($function, $userId)
    {
        $cmd=$this->db->createCommand("SELECT CONCAT(name, ' ', firstname) AS fullname, language FROM hr_user WHERE id=:id");
        $cmd->bindParameter(":id",$userId);
        $data = $cmd->query();
        $data = $data->read();

        $fullname = $data['fullname'];
        $lang = $data['language'];

        $type = "user";

        $cmd=$this->db->createCommand("SELECT pg.id, pg.name, pg.comment FROM hr_user_group AS pg LEFT JOIN hr_user_group_attribution AS ga ON ga.id_group=pg.id LEFT JOIN hr_user AS pe ON pe.id=ga.id_user WHERE pe.id=:id");
        $cmd->bindParameter(":id",$userId);
        $data2 = $cmd->query();
        $data2 = $data2->readAll();

        //pour chaque groupe
        foreach($data2 as $d2)
        {
            $idgroup = $d2['id'];
            $cmd=$this->db->createCommand("SELECT uga.id_device FROM hr_user_group_access AS uga
                                           LEFT JOIN hr_device AS d ON d.id = uga.id_device
                                           WHERE id_group=:id AND d.type='gantner_TimeTerminal'");
            $cmd->bindParameter(":id",$idgroup);
            $data3 = $cmd->query();
            $data3 = $data3->readAll();

            $idtag = 0;

            foreach($data3 as $d3)
            {
                $idreader = $d3['id_device'];

                $cmd=$this->db->createCommand("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `userId`,`keyId`, `deviceId`, `param`, `param2`) VALUES (:type,:func,:userId,:keyId,:deviceId, :param, :param2)");
                $cmd->bindParameter(":func",$function);
                $cmd->bindParameter(":type",$type);
                $cmd->bindParameter(":userId",$userId);
                $cmd->bindParameter(":keyId",$idtag);
                $cmd->bindParameter(":deviceId",$idreader);
                $cmd->bindParameter(":param",$fullname);
                $cmd->bindParameter(":param2",$lang);
                $cmd->execute();
            }

        }
    }


    protected function addSubGroup($function, $idgroup)
    {
        $cmd=$this->db->createCommand("SELECT uga.id_device FROM hr_user_group_access AS uga
                                       LEFT JOIN hr_device AS d ON d.id = uga.id_device
                                       WHERE id_group=:id AND d.type='gantner_TimeTerminal'");

        $cmd->bindParameter(":id",$idgroup);
        $data = $cmd->query();
        $data = $data->readAll();

        $type = 'key_user';

        foreach($data as $d)
        {
            $idreader = $d['id_device'];

            $cmd=$this->db->createCommand("SELECT * FROM hr_user_group_attribution WHERE id_group=:id");
            $cmd->bindParameter(":id",$idgroup);
            $data2 = $cmd->query();
            $data2 = $data2->readAll();

            foreach($data2 as $d2)
            {
                $idperson = $d2['id_user'];

                $cmd=$this->db->createCommand("SELECT * FROM hr_user WHERE id=:id");
                $cmd->bindParameter(":id",$idperson);
                $data_u = $cmd->query();
                $data_u = $data_u->read();

                $cmd=$this->db->createCommand("SELECT t.id, t.serialNumber, t.isBlocked FROM hr_keys_attribution AS ta LEFT JOIN hr_keys AS t ON t.id=ta.id_key WHERE id_user=:id");
                $cmd->bindParameter(":id",$idperson);
                $data3 = $cmd->query();
                $data3 = $data3->readAll();

                if(count($data3) > 0)
                {
                    foreach($data3 as $d3)
                    {
                        $rfidId = $d3['id'];
                        $rfidSn = $d3['serialNumber'];
                        $fullName = $data_u['name'].' '.$data_u['firstname'];
                        $cmd=$this->db->createCommand("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `userId`,`keyId`, `deviceId`, `param`, `param2`) VALUES (:type,:func,:userId,:keyId,:deviceId,:param,:param2)");
                        $cmd->bindParameter(":func",$function);
                        $cmd->bindParameter(":type",$type);
                        $cmd->bindParameter(":userId",$idperson);
                        $cmd->bindParameter(":keyId",$rfidId);
                        $cmd->bindParameter(":deviceId",$idreader);
                        $cmd->bindParameter(":param",$fullName);
                        $cmd->bindParameter(":param2",$rfidSn);
                        $cmd->execute();
                    }
                }
                else
                {
                    $rfidId = "0";
                    $rfidSn = "0";
                    $type = "user";
                    $fullName = $data_u['name'].' '.$data_u['firstname'];
                    $cmd=$this->db->createCommand("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `userId`,`keyId`, `deviceId`, `param`, `param2`) VALUES (:type,:func,:userId,:keyId,:deviceId,:param,:param2)");
                    $cmd->bindParameter(":func",$function);
                    $cmd->bindParameter(":type",$type);
                    $cmd->bindParameter(":userId",$idperson);
                    $cmd->bindParameter(":keyId",$rfidId);
                    $cmd->bindParameter(":deviceId",$idreader);
                    $cmd->bindParameter(":param",$fullName);
                    $cmd->bindParameter(":param2",$rfidSn);
                    $cmd->execute();
                
                }
            }

        }
    }
}


?>
