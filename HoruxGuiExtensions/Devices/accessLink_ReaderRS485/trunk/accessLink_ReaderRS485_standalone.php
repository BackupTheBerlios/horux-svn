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

class accessLink_ReaderRS485_standalone extends TModule
{
  private $db = NULL;

    public function init($config)
    {
        parent::init($config);


    }

    public function addStandalone($function, $id, $param=NULL)
    {

        $this->db = $this->Application->getModule('horuxDb')->DbConnection;
        $this->db->Active=true;

        switch($param)
        {
            case 'KeyList':
                $this->addSubKey($function, $id);
                break;
            case 'KeyAdd':
                $this->addSubKey($function, $id);
                break;
            case 'KeyMod':
                $this->addSubKey($function, $id);
                break;
            case 'UserList':
                $this->addSubUser($function, $id);
                break;
            case 'UserWizzard':
                $this->addSubUser($function, $id);
                break;
            case 'UserAttributionKey':
                $this->addSubKey($function, $id);
                break;
            case 'UserAttributionGroup':
                $this->addSubGroup($function, $id);
                break;
            case 'UserGroupMod':
                $this->addSubGroup($function, $id);
                break;
        }
    }

    protected function addSubKey($function, $idkey)
    {
        $cmd=$this->db->createCommand("SELECT * FROM hr_keys WHERE id=:id");
        $cmd->bindParameter(":id",$idkey);
        $data = $cmd->query();
        $data = $data->read();

        $rfid = $data['serialNumber'];
        $idtag = $data['id'];

        if( ($data['isBlocked'] == 0 && $function=='add' ) || $function=='sub' )
        {

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

                //if the user is blocked, do nothing
                if($data_u['isBlocked'])
                    return;


                $cmd=$this->db->createCommand("SELECT gac.id_device FROM hr_user_group_attribution AS ga
                                               LEFT JOIN hr_user_group_access AS gac ON gac.id_group=ga.id_group
                                               LEFT JOIN hr_device AS d ON d.id = gac.id_device
                                               WHERE ga.id_user=:id AND d.type='accessLink_ReaderRS485'");
                
                $cmd->bindParameter(":id",$idperson);
                $data3 = $cmd->query();
                $data3 = $data3->readAll();

                foreach($data3 as $d3)
                {
                    $idreader = $d3['id_device'];

                    if($idreader == '') continue;

                    $cmd=$this->db->createCommand("INSERT INTO hr_standalone_action_service (`type`, `serialNumber`, `rd_id`) VALUES (:func,:rfid,:rdid)");
                    $cmd->bindParameter(":func",$function);
                    $cmd->bindParameter(":rfid",$rfid);
                    $cmd->bindParameter(":rdid",$idreader);
                    $cmd->execute();
                }

            }
        }
    }

    protected function addSubUser($function, $userId)
    {
        $cmd=$this->db->createCommand("SELECT t.id, t.identificator, t.serialNumber, t.isBlocked FROM hr_keys AS t LEFT JOIN hr_keys_attribution AS ta ON ta.id_key=t.id LEFT JOIN hr_user AS pe ON pe.id=ta.id_user WHERE pe.id=:id");
        $cmd->bindParameter(":id",$userId);
        $data = $cmd->query();
        $data = $data->readAll();

        //pour chaque rfid
        foreach($data as $d)
        {
            $rfid = $d['serialNumber'];
            if( $d['isBlocked'] == 0 )
            {
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
                                                   WHERE id_group=:id AND d.type='accessLink_ReaderRS485'");
                    $cmd->bindParameter(":id",$idgroup);
                    $data3 = $cmd->query();
                    $data3 = $data3->readAll();

                    foreach($data3 as $d3)
                    {
                        $idreader = $d3['id_device'];

                        $cmd=$this->db->createCommand("INSERT INTO hr_standalone_action_service (`type`, `serialNumber`, `rd_id`) VALUES (:func,:rfid,:rdid)");
                        $cmd->bindParameter(":func",$function);
                        $cmd->bindParameter(":rfid",$rfid);
                        $cmd->bindParameter(":rdid",$idreader);
                        $cmd->execute();
                    }

                }
            }
        }
    }

    protected function addSubGroup($function, $idgroup)
    {
        $cmd=$this->db->createCommand("SELECT uga.id_device FROM hr_user_group_access AS uga
                                       LEFT JOIN hr_device AS d ON d.id = uga.id_device
                                       WHERE id_group=:id AND d.type='accessLink_ReaderRS485'");
        
        $cmd->bindParameter(":id",$idgroup);
        $data = $cmd->query();
        $data = $data->readAll();
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

                //i the user is blocked, do nothing
                if($data_u['isBlocked'])
                    return;


                $cmd=$this->db->createCommand("SELECT t.serialNumber, t.isBlocked FROM hr_keys_attribution AS ta LEFT JOIN hr_keys AS t ON t.id=ta.id_key WHERE id_user=:id");
                $cmd->bindParameter(":id",$idperson);
                $data3 = $cmd->query();
                $data3 = $data3->readAll();

                foreach($data3 as $d3)
                {
                    $rfid = $d3['serialNumber'];

                    if( ($d3['isBlocked'] == 0 && $function=='add' ) || $function=='sub')
                    {
                        $cmd=$this->db->createCommand("INSERT INTO hr_standalone_action_service (`type`, `serialNumber`, `rd_id`) VALUES (:func,:rfid,:rdid)");
                        $cmd->bindParameter(":func",$function);
                        $cmd->bindParameter(":rfid",$rfid);
                        $cmd->bindParameter(":rdid",$idreader);
                        $cmd->execute();
                    }
                }
            }

        }
    }

}


?>
