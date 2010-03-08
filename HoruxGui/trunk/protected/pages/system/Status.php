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

        if($xml != "")
        {

            $param = $this->Application->getParameters();

            $this->horuxVersion->Text = (String)$xml->appVersion;

            if($param['appMode'] === 'saas')
            {
                $this->lastUpdate->Text = (String)$xml->lastUpdate;
            }
            else
            {
                $this->lastUpdate->Text = '-';
            }
            
            $this->horuxTimeLive->Text = (String)$xml->serverLive;


            $this->plugins = array();

            foreach ($xml->plugins as $plugins)
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
                    $this->plugins[] = $p;
                }
            }

            $this->PluginsR->DataSource=$this->plugins;
            $this->PluginsR->dataBind();

            $this->devices = array();


            foreach ($xml->devices as $devices)
            {

                foreach ($devices as $device)
                {
                    $p = array();
                    $p['id'] = (string)$device['id'];
                    $p['name'] = (String)$device->name;
                    $p['serialNumber'] = (String)$device->serialNumber;
                    $p['isConnected'] = (String)$device->isConnected;
                    $p['firmwareVersion'] = (String)$device->firmwareVersion;
                    $p['port'] = (String)$this->port;
                    $p['host'] = (String)$this->host;
                    $p['mode'] = (String)$param['appMode'];
                    $p['saasdbname'] =  md5($this->db->getConnectionString());
                    $this->devices[] = $p;
                }
            }

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

            require_once( 'XML/RPC.php' );
            $result = "";
            $content_error = "";
            $client = new XML_RPC_Client("RPC2", $this->host, $this->port);
            $msg = new XML_RPC_Message("horux.getSystemInfo");
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
