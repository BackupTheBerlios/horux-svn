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



class Status extends Page
{
    public $plugins = NULL;
    public $devices = NULL;
    public $port = 0;
    public $host = 0;

    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->IsPostBack)
        {
            $result = $this->getSystemStatus();
            $this->parseResponse($result);
        }
    }


    public function onRefresh($sender, $param)
    {
        $this->hiddenMessage();
        $result = $this->getSystemStatus();
        $result = $this->parseResponse($result);
        $this->Page->CallbackClient->update('list','');
    }

    protected function parseResponse($xmlresp)
    {
        $xml = simplexml_load_string($xmlresp);

        $horuxRepeaterData = array();
        $this->plugins = array();

        $this->devices = array();

        if($xml != "")
        {
            $param = $this->Application->getParameters();
            
            foreach ($xml->controller as $controller)
            {
                $sql = "SELECT * FROM hr_horux_controller WHERE id=".(int)$controller->controllerID;
                $command=$this->db->createCommand($sql);
                $dataObj=$command->query();
                $dataObj = $dataObj->read();

                $horuxRepeaterData[(int)$controller->controllerID]["name"] = $dataObj['name'];

                $horuxRepeaterData[(int)$controller->controllerID]["horuxVersion"] = (String)$controller->appVersion;

                $horuxRepeaterData[(int)$controller->controllerID]["lastUpdate"] = (String)$controller->lastUpdate;


                $horuxRepeaterData[(int)$controller->controllerID]["horuxTimeLive"] = (String)$controller->serverLive;
                

                foreach ($controller->plugins as $plugins)
                {

                    foreach ($plugins as $plugin)
                    {
                        $p = array();
                        $p['name'] = (String)$plugin->name;
                        $p['description'] = (String)$plugin->description;
                        $p['version'] = (String)$plugin->version;
                        $p['author'] = (String)$plugin->author;
                        $p['copyright'] = (String)$plugin->copyright;
                        $p['type'] = (string)$plugins['type'];
                        $p['horuxController'] = $dataObj['name'];
                        $this->plugins[] = $p;
                    }
                }

                $this->PluginsR->DataSource=$this->plugins;
                $this->PluginsR->dataBind();

                foreach ($controller->devices as $devices)
                {

                    foreach ($devices as $device)
                    {
                        $p = array();
                        $p['id'] = (string)$device['id'];
                        $p['name'] = utf8_decode((String)$device->name);
                        $p['serialNumber'] = (String)$device->serialNumber;
                        $p['isConnected'] = (String)$device->isConnected;
                        $p['firmwareVersion'] = (String)$device->firmwareVersion;
                        $p['port'] = (String)$this->port;
                        $p['host'] = (String)$this->host;
                        $p['mode'] = (String)$param['appMode'];
                        $p['saasdbname'] =  md5($this->db->getConnectionString());
                        $p['horuxController'] = $dataObj['name'];
                        $this->devices[] = $p;
                    }
                }


            }

            $this->HoruxRepeater->DataSource = $horuxRepeaterData;
            $this->HoruxRepeater->dataBind();

            $this->DeviceR->DataSource=$this->devices;
            $this->DeviceR->dataBind();
        }
    }

    protected function getSystemStatus()
    {

        $sql = "SELECT * FROM hr_config";
        $command=$this->db->createCommand($sql);
        $dataObj=$command->query();
        $dataObj = $dataObj->read();
        $this->host = $dataObj['xmlrpc_server'];
        $this->port = $dataObj['xmlrpc_port'];

        $param = $this->Application->getParameters();

        if($param['appMode'] === 'production')
        {
            require_once("xmlrpc/lib/xmlrpc.inc");
            
            $result = "";
            $content_error = "";

            $client = new xmlrpc_client( "RPC2", $this->host, $this->port );

            $message = new xmlrpcmsg("horux.getSystemInfo");
            $response = $client->send($message);

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

            if($content_error != "")
            {
                $this->displayMessage( $content_error , false);
                return "";
            }
            else
            {
                if($result != "")
                {
                    return $result;
                }
            }

            $this->displayMessage(Prado::localize('The server horux seems to be down') , false);
            return "";
        }
        else
        {
            if($param['appMode'] === 'demo')
            {
                return file_get_contents('demo.xml');
            }
            
            if($param['appMode'] === 'saas')
            {
                $dbName = md5($this->db->getConnectionString());
                if(file_exists('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'system_status_'.$dbName.'.xml'))
                    return file_get_contents('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'system_status_'.$dbName.'.xml');
                else
                    return "";
            }
        }
    }
}

?>
