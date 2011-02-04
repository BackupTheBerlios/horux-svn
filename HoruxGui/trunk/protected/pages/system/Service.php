<?php

class Service extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        $this->appCheck();

        $param = $this->Application->getParameters();
        if($param['appMode'] == 'demo' || $param['appMode'] == 'saas')
        {
            $this->tbb->stop->setEnabled(false);
            $this->tbb->start->setEnabled(false);
        }
    }

    public function appCheck()
    {
        $param = $this->Application->getParameters();
        if($param['appMode'] == 'saas')
        {
            $this->serverStatus->Text = Prado::localize("Cannot use this service in Saas mode");
            return;
        }

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

            include_once("xmlrpc/lib/xmlrpc.inc");
            $client = new xmlrpc_client("RPC2", $host, $port);

            $message = new xmlrpcmsg("horux.isEngine");
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
        $horuxService = new THoruxService();
        $horuxService->onStop();

        $this->isRunning();
        $this->log("Stop horux");
    }


    public function onStart($sender, $param)
    {
        $horuxService = new THoruxService();
        $horuxService->onStart();

        $this->isRunning();
        $this->log("Start horux");
    }

}

?>
