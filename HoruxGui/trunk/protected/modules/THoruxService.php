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

class THoruxService extends TModule
{
  private $db = NULL;

    public function init($config)
    {
    parent::init($config);
    }

    public function onStop()
    {
        $param = $this->Application->getParameters();

        if($param['appMode'] == 'saas') return;

        require_once( 'XML/RPC.php' );

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
        $client = new XML_RPC_Client("RPC2", $host, $port);

        $app = $this->getApplication();

        $userId = $app->getUser()->getUserID();
        $sql = "SELECT * FROM hr_superusers WHERE id=".$userId;
        $command = $db->createCommand($sql);
        $dataObj = $command->query();
        $dataObj = $dataObj->read();


        $params = array(new XML_RPC_Value($dataObj['name'], 'string'), new XML_RPC_Value($dataObj['password'], 'string'));


        $msg = new XML_RPC_Message("horux.stopEngine", $params);
        @$response = $client->send($msg);

    }


    public function onStart()
    {
        $param = $this->Application->getParameters();
        if($param['appMode'] == 'saas') return;


        require_once( 'XML/RPC.php' );

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
        $client = new XML_RPC_Client("RPC2", $host, $port);


        $msg = new XML_RPC_Message("horux.startEngine");
        @$response = $client->send($msg);

    }

    public function onStopDevice($deviceId)
    {
        $param = $this->Application->getParameters();
        if($param['appMode'] == 'saas') return;


        require_once( 'XML/RPC.php' );

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
        $client = new XML_RPC_Client("RPC2", $host, $port);

        $app = $this->getApplication();

        $userId = $app->getUser()->getUserID();
        $sql = "SELECT * FROM hr_superusers WHERE id=".$userId;
        $command = $db->createCommand($sql);
        $dataObj = $command->query();
        $dataObj = $dataObj->read();


        $params = array(    new XML_RPC_Value($dataObj['name'], 'string'),
                            new XML_RPC_Value($dataObj['password'], 'string'),
                            new XML_RPC_Value($deviceId, 'string'));


        $msg = new XML_RPC_Message("horux.stopDevice", $params);
        @$response = $client->send($msg);

    }


    public function onStartDevice($deviceId)
    {
        $param = $this->Application->getParameters();
        if($param['appMode'] == 'saas') return;


        require_once( 'XML/RPC.php' );

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
        $client = new XML_RPC_Client("RPC2", $host, $port);

        $app = $this->getApplication();

        $userId = $app->getUser()->getUserID();
        $sql = "SELECT * FROM hr_superusers WHERE id=".$userId;
        $command = $db->createCommand($sql);
        $dataObj = $command->query();
        $dataObj = $dataObj->read();


        $params = array(    new XML_RPC_Value($dataObj['name'], 'string'),
                            new XML_RPC_Value($dataObj['password'], 'string'),
                            new XML_RPC_Value($deviceId, 'string'));


        $msg = new XML_RPC_Message("horux.startDevice", $params);
        @$response = $client->send($msg);

    }
}


?>
