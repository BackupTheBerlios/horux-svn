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

include_once("xmlrpc/lib/xmlrpc.inc");

class THoruxService extends TModule
{
    public function init($config)
    {
        parent::init($config);
    }

    public function onStop()
    {
        $param = $this->Application->getParameters();

        if($param['appMode'] == 'saas') return;



        $db = $this->Application->getModule('horuxDb')->DbConnection;
        $db->Active=true;

        $sql = "SELECT * FROM hr_config";
        $command = $db->createCommand($sql);
        $dataObj = $command->query();
        $dataObj = $dataObj->read();
        $host = $dataObj['xmlrpc_server'];
        $port = $dataObj['xmlrpc_port'];

        $result = "";
        $content_error = "";
        $client = new xmlrpc_client("RPC2", $host, $port);

        $app = $this->getApplication();

        $userId = $app->getUser()->getUserID();
        $sql = "SELECT * FROM hr_superusers WHERE id=".$userId;
        $command = $db->createCommand($sql);
        $dataObj = $command->query();
        $dataObj = $dataObj->read();


        $params = array(new xmlrpcval($dataObj['name'], 'string'), new xmlrpcval($dataObj['password'], 'string'));

        $message = new xmlrpcmsg("horux.stopEngine", $params);
        $resp = $client->send($message);

    }


    public function onStart()
    {
        $param = $this->Application->getParameters();
        if($param['appMode'] == 'saas') return;


        $db = $this->Application->getModule('horuxDb')->DbConnection;
        $db->Active=true;

        $sql = "SELECT * FROM hr_config";
        $command = $db->createCommand($sql);
        $dataObj = $command->query();
        $dataObj = $dataObj->read();
        $host = $dataObj['xmlrpc_server'];
        $port = $dataObj['xmlrpc_port'];

        $result = "";
        $content_error = "";

        $client = new xmlrpc_client("RPC2", $host, $port);

        $msg = new xmlrpcmsg("horux.startEngine");
        @$response = $client->send($msg);

    }

    public function onStopDevice($deviceId)
    {
        $param = $this->Application->getParameters();
        if($param['appMode'] == 'saas') return;


        $db = $this->Application->getModule('horuxDb')->DbConnection;
        $db->Active=true;

        $sql = "SELECT * FROM hr_config";
        $command = $db->createCommand($sql);
        $dataObj = $command->query();
        $dataObj = $dataObj->read();
        $host = $dataObj['xmlrpc_server'];
        $port = $dataObj['xmlrpc_port'];

        $result = "";
        $content_error = "";
        $client = new xmlrpc_client("RPC2", $host, $port);

        $app = $this->getApplication();

        $userId = $app->getUser()->getUserID();
        $sql = "SELECT * FROM hr_superusers WHERE id=".$userId;
        $command = $db->createCommand($sql);
        $dataObj = $command->query();
        $dataObj = $dataObj->read();


        $params = array(    new xmlrpcval($dataObj['name'], 'string'),
                            new xmlrpcval($dataObj['password'], 'string'),
                            new xmlrpcval($deviceId, 'string'));


        $msg = new xmlrpcmsg("horux.stopDevice", $params);
        @$response = $client->send($msg);

    }


    public function onStartDevice($deviceId)
    {
        $param = $this->Application->getParameters();
        if($param['appMode'] == 'saas') return;

        $db = $this->Application->getModule('horuxDb')->DbConnection;
        $db->Active=true;

        $sql = "SELECT * FROM hr_config";
        $command = $db->createCommand($sql);
        $dataObj = $command->query();
        $dataObj = $dataObj->read();
        $host = $dataObj['xmlrpc_server'];
        $port = $dataObj['xmlrpc_port'];

        $result = "";
        $content_error = "";
        $client = new xmlrpc_client("RPC2", $host, $port);

        $app = $this->getApplication();

        $userId = $app->getUser()->getUserID();
        $sql = "SELECT * FROM hr_superusers WHERE id=".$userId;
        $command = $db->createCommand($sql);
        $dataObj = $command->query();
        $dataObj = $dataObj->read();


        $params = array(    new xmlrpcval($dataObj['name'], 'string'),
                            new xmlrpcval($dataObj['password'], 'string'),
                            new xmlrpcval($deviceId, 'string'));


        $msg = new xmlrpcmsg("horux.startDevice", $params);
        @$response = $client->send($msg);

    }
}


?>
