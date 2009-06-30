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

class Service extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        $this->appCheck();

        $param = $this->Application->getParameters();
        if($param['appMode'] == 'demo')
        {
            $this->stop->setEnabled(false);
            $this->start->setEnabled(false);
        }
    }

    public function appCheck()
    {
        $this->isRunning();
    }

    public function isRunning()
    {

        $sql = "SELECT * FROM hr_config";
        $command=$this->db->createCommand($sql);
        $dataObj=$command->query();
        $dataObj = $dataObj->read();
        $host = $dataObj['xmlrpc_server'];
        $port = $dataObj['xmlrpc_port'];


        $result = "";
        $content_error = "";
        $param = $this->Application->getParameters();

        if($param['appMode'] == 'demo')
        {
            $this->serverStatus->Text = Prado::localize("The server <i>horuxd</i> is running");
        }
        else
        {

            require_once( 'XML/RPC.php' );
            $client = new XML_RPC_Client("RPC2", $host, $port);

            $msg = new XML_RPC_Message("horux.isEngine");
            @$response = $client->send($msg);

            if($response)
            {
                if (!$response->faultCode())
                {
                    $v = $response->value();

                    $result = html_entity_decode( $v->scalarval() );
                }
                else
                {
                    $content_error = "ERROR - ";
                    $content_error .= "Code: " . $response->faultCode() . " Reason '" . $response->faultString() . "'<br/>";
                };
            }

            //$this->stop->setVisible(true);
            //$this->start->setVisible(true);

            if($content_error != "")
            {
                $this->serverStatus->Text = Prado::localize("The server <i>horuxd</i> is not running");
            }
            else
            {
                if($result != "")
                {
                    $isStopped = $result=='ko' ? true : false;
                    $isStarted = $result=='ok' ? false : true;

                    if($isStopped)
                    {
                        //$this->start->setVisible(true);
                        $this->serverStatus->Text = Prado::localize("The server <i>horuxd</i> is not running");
                    }
                    else
                    {
                        //$this->stop->setVisible(true);
                        $this->serverStatus->Text = Prado::localize("The server <i>horuxd</i> is running");
                    }
                }
                else
                {
                    $this->serverStatus->Text = Prado::localize("The server <i>horuxd</i> is not running");

                }
            }
        }
    }

    public function onStop($sender, $param)
    {
        require_once( 'XML/RPC.php' );

        $sql = "SELECT * FROM hr_config";
        $command=$this->db->createCommand($sql);
        $dataObj=$command->query();
        $dataObj = $dataObj->read();
        $host = $dataObj['xmlrpc_server'];
        $port = $dataObj['xmlrpc_port'];

        $result = "";
        $content_error = "";
        $client = new XML_RPC_Client("RPC2", $host, $port);

        $app = $this->getApplication();

        $userId = $app->getUser()->getUserID();
        $sql = "SELECT * FROM hr_superusers WHERE id=".$userId;
        $command=$this->db->createCommand($sql);
        $dataObj=$command->query();
        $dataObj = $dataObj->read();


        $params = array(new XML_RPC_Value($dataObj['name'], 'string'), new XML_RPC_Value($dataObj['password'], 'string'));


        $msg = new XML_RPC_Message("horux.stopEngine", $params);
        @$response = $client->send($msg);
        $this->isRunning();

        $this->log("Stop horux");
    }


    public function onStart($sender, $param)
    {
        require_once( 'XML/RPC.php' );

        $sql = "SELECT * FROM hr_config";
        $command=$this->db->createCommand($sql);
        $dataObj=$command->query();
        $dataObj = $dataObj->read();
        $host = $dataObj['xmlrpc_server'];
        $port = $dataObj['xmlrpc_port'];

        $result = "";
        $content_error = "";
        $client = new XML_RPC_Client("RPC2", $host, $port);


        $msg = new XML_RPC_Message("horux.startEngine");
        @$response = $client->send($msg);

        $this->isRunning();

        $this->log("Start horux");
    }

}

?>
