<?php

class horux
{

    /**
     * @param int $id id of the user to be return
     * @return mixed Return the user data
     * @soapmethod
     */
    public function getUserById($id)
    {
        $app = Prado::getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;
        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        $db->Active=true;

        $sql = "SELECT
                    u.id,
                    u.name,
                    u.firstname,
                    u.birthday,
                    u.picture,
                    u.language,
                    u.sex AS gender,
                    u.validity_date,
                    u.isBlocked,
                    d.name AS department,
                    u.street AS street_private,
                    u.city AS city_private,
                    u.country AS country_private,
                    u.zip AS zip_private,
                    u.phone1 AS phone_private,
                    u.email1 AS email_private,
                    u.firme,
                    u.street_pr AS street_professional,
                    u.city_pr AS city_professional,
                    u.country_pr AS country_professional,
                    u.npa_pr AS zip_professional,
                    u.phone2 AS phone_professional,
                    u.email2 AS email_professional,
                    u.fax AS fax_professional,
                    k.serialNumber AS key_number,
                    uga.id_group AS ugroup
                 FROM hr_user AS u
                 LEFT JOIN hr_department AS d ON u.department = d.id
                 LEFT JOIN hr_keys_attribution AS ka ON ka.id_user = u.id
                 LEFT JOIN hr_keys AS k ON k.id = ka.id_key
                 LEFT JOIN hr_user_group_attribution AS uga ON uga.id_user = u.id
                 WHERE u.id=$id";

        $cmd= $db->createCommand($sql);
        $data = $cmd->query();
        $data = $data->read();

        return $data;
    }

    /**
     * @return mixed Return the users data
     * @soapmethod
     */
    public function getAllUser()
    {
        $app = Prado::getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;
        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        $db->Active=true;

        $sql = "SELECT
                    u.id,
                    u.name,
                    u.firstname,
                    u.birthday,
                    u.picture,
                    u.language,
                    u.sex AS gender,
                    u.validity_date,
                    u.isBlocked,
                    d.name AS department,
                    u.street AS street_private,
                    u.city AS city_private,
                    u.country AS country_private,
                    u.zip AS zip_private,
                    u.phone1 AS phone_private,
                    u.email1 AS email_private,
                    u.firme,
                    u.street_pr AS street_professional,
                    u.city_pr AS city_professional,
                    u.country_pr AS country_professional,
                    u.npa_pr AS zip_professional,
                    u.phone2 AS phone_professional,
                    u.email2 AS email_professional,
                    u.fax AS fax_professional,
                    k.serialNumber AS key_number,
                    uga.id_group AS ugroup
                FROM hr_user AS u
                LEFT JOIN hr_department AS d ON u.department = d.id
                LEFT JOIN hr_keys_attribution AS ka ON ka.id_user = u.id
                LEFT JOIN hr_keys AS k ON k.id = ka.id_key
                LEFT JOIN hr_user_group_attribution AS uga ON uga.id_user = u.id
                WHERE u.id>1 ORDER BY u.name, u.firstname";

        $cmd= $db->createCommand($sql);
        $data = $cmd->query();
        $data = $data->readAll();

        return $data;
    }


    /**
     * @return mixed Return the users groups data
     * @soapmethod
     */
    public function getUserGroup()
    {
        $app = Prado::getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;
        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        $db->Active=true;

        $sql = "SELECT * FROM hr_user_group ORDER BY name";

        $cmd= $db->createCommand($sql);
        $data = $cmd->query();
        $data = $data->readAll();

        return $data;
    }

    /**
     * @return bool Return true if the system status was well updated, else false
     * @param mixed $status status of the system
     * @soapmethod
     */
    public function updateSystemStatus($status)
    {
        $app = Prado::getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;
        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        $dbName = md5($db->getConnectionString());

        $fp = fopen('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'system_status_'.$dbName.'.xml', 'w');
        fwrite($fp, $status);
        fclose($fp);
    }

    /**
     * @return string Return the database dump
     * @soapmethod
     */
    public function backupDatabase($tables)
    {
        return $this->dumpDatabase(true, true, 'ALL');
    }

    /**
     * @param array $tables array of the table who musst be dump. ALL for all tables
     * @return string Return the data of the database or for specofic table
     * @soapmethod
     */
    public function reloadDatabaseData($tables)
    {
        return $this->dumpDatabase(false, true, $tables);
    }

    /**
     * @param array $tables array of the table who musst be dump. ALL for all tables
     * @return string Return the full schema of the database
     * @soapmethod
     */
    public function reloadDatabaseSchema($tables)
    {
        
        return $this->dumpDatabase(true, false, $tables);
    }


    /**
     * @param $structure dump the structure
     * @param $addData dump the data
     * @param $getTable array of the table who musst be dump. ALL for all tables
     * @return mixed Return a dump of the database
     */
    private function dumpDatabase($structure, $addData, $getTable)
    {        
        $app = Prado::getApplication();
        $db = $app->getModule('horuxDb')->DbConnection;
        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        $db->Active=true;

        $cmd= $db->createCommand("SHOW TABLES");
        $data = $cmd->query();
        $data->setFetchMode(PDO::FETCH_NUM);
        $tables = $data->readAll();

        $dump = "";

        foreach($tables as $table)
        {
            $tablename = $table[0];

            if($table !== 'ALL' && is_array($getTable))
            {
                if(!in_array( $tablename , $getTable))
                {
                    continue;
                }
            }

            if ($structure === true)
            {
                $dump .= "DROP TABLE IF EXISTS `$tablename`;\n\n";

                $cmd= $db->createCommand("SHOW CREATE TABLE $tablename");
                $data = $cmd->query();
                $data->setFetchMode(PDO::FETCH_NUM);
                $resCreate = $data->read();

                $schema = $resCreate[1].";";
                $dump .= "$schema\n\n";
            }

            if ($addData === true)
            {
                $cmd= $db->createCommand("SELECT * FROM $tablename");
                $data = $cmd->query();
                $data->setFetchMode(PDO::FETCH_ASSOC);
                if ($data)
                {
                    $rows  = $data->readAll();
                    if($rows)
                    {
                        $sFieldnames = join("`,`", array_keys($rows[0]));

                        $sFieldnames = "(`".$sFieldnames."`)";

                        $sInsert = "INSERT INTO `$tablename` $sFieldnames VALUES\n";


                        $theData = array();

                        foreach($rows as $row)
                        {

                           $r = addslashes(implode("<%%>,<%%>", $row ));
                           $r = str_replace("<%%>", "'", $r);
                           $theData[] = "('".$r."')";
                        }

                        $dump .= $sInsert.implode(",\n", $theData).";\n\n";
                    }
                }
            }
        }


        return $dump;
    }

    /**
     * @param string $xml xml data of the tracking
     * @return string Return the database dump
     * @soapmethod
     */
    public function syncTrackingTable($xml)
    {
        $doc=new TXmlDocument('1.0','utf-8');
        $doc->loadFromString($xml);

        // mains tracking table
        $tableArray["hr_tracking"] = array();
        $tableArray["hr_alarms"] = array();

        $nreOfRecord = 0;

        $ids = '';

        if("trackingDump" == $doc->getTagName())
        {
            if($doc->getHasElement())
            {
                if($doc->getElements()->itemAt(0)->getHasElement())
                {
                    $tables = $doc->getElements()->itemAt(0)->getElements();

                    $tablesName = "";
                    foreach($tables as $table)
                    {
                       if($table->getHasElement())
                       {
                           $tableName = $table->getTagName();

                           $records = $table->getElements()->itemAt(0)->getElements();

                           foreach($records as $record)
                           {
                               if($record->getHasElement())
                               {
                                   $fields = $record->getElements();

                                   $recordArray = array();

                                   foreach($fields as $field)
                                   {                                       
                                        $fieldName = $field->getTagName();
                                        $fieldValue = $field->getValue();

                                        $recordArray[$fieldName] = $fieldValue;
                                   }

                                   $tableArray[$tableName][] = $recordArray;
                               }
                           }
                       }
                    }

                    $ids .= $this->syncAlarm($tableArray["hr_alarms"]);

                    if($ids != "") {
                        $ids .= ",";
                    }

                    $ids .= $this->syncTracking($tableArray);

                    return $ids;
                }
                else
                    return "0";
            }
            else
                return "0";

        }
        else
            return "0";
    }

    protected function syncAlarm($alarms)
    {
        $app = Prado::getApplication();
        $db = $app->getModule('horuxDb')->DbConnection;
        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        $db->Active=true;

        $ids = array();

        foreach($alarms as $alarm)
        {
            $id = "hr_alarms:".$alarm['id'];
            
            unset($alarm['id']);

            $sFieldnames = join("`,`", array_keys($alarm));
            $sFieldnames = "(`".$sFieldnames."`)";

            $sFieldvalues= join("','", array_values($alarm));
            $sFieldvalues = "('".$sFieldvalues."')";

            $cmd= $db->createCommand("INSERT INTO hr_alarms ".$sFieldnames." VALUES ".$sFieldvalues);

            if($cmd->execute())
            {
                $ids[] = $id;
            }
         }

        return implode(",", $ids);
    }

    protected function syncTracking($tracking)
    {
        $app = Prado::getApplication();
        $db = $app->getModule('horuxDb')->DbConnection;
        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        $db->Active=true;

        $ids = array();

        foreach($tracking["hr_tracking"] as $track)
        {
            $id = $track['id'];

            unset($track['id']);

            $sFieldnames = join("`,`", array_keys($track));
            $sFieldnames = "(`".$sFieldnames."`)";

            $sFieldvalues= join("','", array_values($track));
            $sFieldvalues = "('".$sFieldvalues."')";

            $cmd= $db->createCommand("INSERT INTO hr_tracking ".$sFieldnames." VALUES ".$sFieldvalues);

            if($cmd->execute())
            {
                $lastID = $db->getLastInsertID();

                $ids[] = "hr_tracking:".$id;

                if($track['extData'] != '')
                {
                    if(count($tracking[$track['extData']]) > 0 ) {
                        foreach($tracking[$track['extData']] as $extTracking)
                        {
                            if($extTracking['tracking_id'] == $id)
                            {
                                $extTracking['tracking_id'] = $lastID;

                                $sFieldnames = join("`,`", array_keys($extTracking));
                                $sFieldnames = "(`".$sFieldnames."`)";

                                $sFieldvalues= join("','", array_values($extTracking));
                                $sFieldvalues = "('".$sFieldvalues."')";

                                $cmd= $db->createCommand("INSERT INTO ".$track['extData']." ".$sFieldnames." VALUES ".$sFieldvalues);

                                if($cmd->execute())
                                {
                                    $ids[] = $track['extData'].":".$id;
                                }
                            }
                        }
                    }
                }
            }
        }

        return implode(",", $ids);
    }



    /**
     * @param string $ids id's of the trigger which are done
     * @return string Return true
     * @soapmethod
     */
    public function syncDatabaseDataDone($ids)
    {
        $app = Prado::getApplication();
        $db = $app->getModule('horuxDb')->DbConnection;
        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        $db->Active=true;

        if($ids !== "")
        {
            $cmd= $db->createCommand("DELETE FROM hr_trigger_change WHERE id IN ($ids)");
            $cmd->execute() ;

            $cmd= $db->createCommand("OPTIMIZE TABLE hr_trigger_change");
            $cmd->execute() ;


        }

        return true;
    }

    /**
     * @return string Return the database dump
     * @soapmethod
     */
    public function syncDatabaseData()
    {
        $app = Prado::getApplication();
        $db = $app->getModule('horuxDb')->DbConnection;
        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        $db->Active=true;

        $cmd= $db->createCommand("SELECT * FROM hr_trigger_change ORDER BY id");
        $data = $cmd->query();
        $data = $data->readAll();

        $doc=new TXmlDocument('1.0','utf-8');
        $doc->TagName='SyncData';

        foreach($data as $row)
        {
            $trigger = new TXmlElement('Trigger');
            $trigger->setAttribute('id',$row['id']);
            $doc->Elements[] = $trigger;

            $table = new TXmlElement('table');
            $table->setAttribute('name',$row['table']);
            $table->setAttribute('action',$row['action']);
            $table->setAttribute('key',$row['key']);
            $trigger->Elements[] = $table;

            $newValue = new TXmlElement('newValue');
            $newValue->Value=$row['newValue'];
            $table->Elements[] = $newValue;
        }

        return $doc->saveToString();
    }

    /**
     * @param array $tables array of the table who musst be dump.
     * @return string Return the database dump
     * @soapmethod
     */
    public function createTrigger($tables)
    {
        $app = Prado::getApplication();
        $db = $app->getModule('horuxDb')->DbConnection;
        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        $db->Active=true;

        $cmd= $db->createCommand("SHOW TRIGGERS");
        $data = $cmd->query();
        $data->setFetchMode(PDO::FETCH_NUM);
        $data = $data->readAll();

        foreach($data as $d) {
            $cmd= $db->createCommand("DROP TRIGGER ".$d[0]);
            $cmd->execute();
        }

        $cmd= $db->createCommand("SHOW TABLES");
        $data = $cmd->query();
        $data->setFetchMode(PDO::FETCH_NUM);
        $data = $data->readAll();

        $trigger = "";

        foreach($data as $d)
        {

            if(!in_array( $d[0] , $tables))
            {
                continue;
            }


            $cmd= $db->createCommand("SHOW COLUMNS FROM ".$d[0]);
            $data2 = $cmd->query();
            $data2 = $data2->readAll();


            $cmd= $db->createCommand("DROP TRIGGER IF EXISTS ".$d[0]."_trigger_u");
            $cmd->execute();

            $trigger = "";
            $trigger .= "CREATE TRIGGER ".$d[0]."_trigger_u AFTER UPDATE ON ".$d[0]."\n";
            $trigger .= "FOR EACH ROW\n";
            $trigger .= "BEGIN\n";

            $trigger .= "IF (";



            $fields = array();

            foreach($data2 as $d2)
            {
                if($d2['Field'] != 'locked')
                    $fields[] = "NEW.".$d2['Field']." != OLD.".$d2['Field'];
            }

            $trigger .= implode(" OR ", $fields);
            $trigger .= " ) THEN\n";

            $trigger .= " INSERT INTO hr_trigger_change (`table`,`action`,`key`,`newValue`)\n";

            $trigger .= " VALUES ('".$d[0]."','UPDATE',CONCAT('".$data2[0]['Field']."=',NEW.".$data2[0]['Field']."),CONCAT(";

            $fields = array();

            foreach($data2 as $d2)
            {
                $fields[] = "'\'',NEW.".$d2['Field'].",'\''";
            }

            $trigger .= implode(",',',", $fields);

            $trigger .= "));\n";

            $trigger .= "END IF;\n";
            $trigger .= "END\n\n";

            $cmd= $db->createCommand($trigger);
            $cmd->execute();

            $cmd= $db->createCommand("DROP TRIGGER IF EXISTS ".$d[0]."_trigger_i");
            $cmd->execute();

            $trigger = "";
            $trigger .="CREATE TRIGGER ".$d[0]."_trigger_i AFTER INSERT ON ".$d[0]."\n";
            $trigger .= "FOR EACH ROW\n";
            $trigger .= "BEGIN\n";
            $trigger .= " INSERT INTO hr_trigger_change (`table`,`action`,`key`,`newValue`)\n";

            $trigger .= " VALUES ('".$d[0]."','INSERT','".$data2[0]['Field']."',CONCAT(";

            $fields = array();

            foreach($data2 as $d2)
            {
                $fields[] = "'\'',NEW.".$d2['Field'].",'\''";
            }

            $trigger .= implode(",',',", $fields);


            $trigger .= "));\n";

            $trigger .= "END\n\n";

            $cmd= $db->createCommand($trigger);
            $cmd->execute();

            $cmd= $db->createCommand("DROP TRIGGER IF EXISTS ".$d[0]."_trigger_d");
            $cmd->execute();

            $trigger = "";
            $trigger .= "CREATE TRIGGER ".$d[0]."_trigger_d AFTER DELETE ON ".$d[0]."\n";
            $trigger .= "FOR EACH ROW\n";
            $trigger .= "BEGIN\n";
            $trigger .= "INSERT INTO hr_trigger_change (`table`,`action`,`key`,`newValue`)\n";
            $trigger .= " VALUES ('".$d[0]."','DELETE',CONCAT('".$data2[0]['Field']."=',OLD.".$data2[0]['Field']."),'');\n";
            $trigger .= "END\n\n";

            $cmd= $db->createCommand($trigger);
            $cmd->execute();


        }

        return true;
    }

    /**
     * @return string The system's status
     * @soapmethod
     */
    public function getSystemStatus()
    {
        $app = Prado::getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;
        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        $db->Active=true;

        $sql = "SELECT * FROM hr_config";
        $command=$db->createCommand($sql);
        $dataObj=$command->query();
        $dataObj = $dataObj->read();
        $this->host = $dataObj['xmlrpc_server'];
        $this->port = $dataObj['xmlrpc_port'];

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
            return $content_error;
        }
        else
        {
            if($result != "")
            {
                return $result;
            }
        }

        return 'The server horux seems to be down';
    }


    /**
     * @param string $xmlresp The status to parse (current if not specified)
     * @return mixed The parsed status
     * @soapmethod
     */
    function parseStatus($xmlresp)
    {
        if (!$xmlresp)
          $xmlresp = $this->getSystemStatus();
        $ret = array();
        $app = Prado::getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;
        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        $db->Active=true;

        $xml = simplexml_load_string($xmlresp);

        $horuxRepeaterData = array();
        $plugins = array();

        $devices = array();

        if($xml != "")
        {
            $param = $app->getParameters();
            
            foreach ($xml->controller as $controller)
            {
                $sql = "SELECT * FROM hr_horux_controller WHERE id=".(int)$controller->controllerID;
                $command=$db->createCommand($sql);
                $dataObj=$command->query();
                $dataObj = $dataObj->read();

                $horuxRD = array();

                $horuxRD["controllerId"] = (int)$controller->controllerID;
                $horuxRD["name"] = $dataObj['name'];
                $horuxRD["horuxVersion"] = (String)$controller->appVersion;
                $horuxRD["lastUpdate"] = (String)$controller->lastUpdate;
                $horuxRD["horuxTimeLive"] = (String)$controller->serverLive;
                $horuxRepeaterData[] = $horuxRD;

                foreach ($controller->plugins as $plugs)
                {
                    foreach ($plugs as $plugin)
                    {
                        $p = array();
                        $p['name'] = (String)$plugin->name;
                        $p['description'] = (String)$plugin->description;
                        $p['version'] = (String)$plugin->version;
                        $p['author'] = (String)$plugin->author;
                        $p['copyright'] = (String)$plugin->copyright;
                        $p['type'] = (string)$plugins['type'];
                        $p['horuxController'] = $dataObj['name'];
                        $plugins[] = $p;
                    }
                }

                foreach ($controller->devices as $devs)
                {
                    foreach ($devs as $device)
                    {
                        $p = array();
                        $p['id'] = (string)$device['id'];
                        $p['name'] = utf8_decode((String)$device->name);
                        $p['serialNumber'] = (String)$device->serialNumber;
                        $p['isConnected'] = (String)$device->isConnected;
                        $p['firmwareVersion'] = (String)$device->firmwareVersion;
                        $p['port'] = (String)$port;
                        $p['host'] = (String)$host;
                        $p['mode'] = (String)$param['appMode'];
                        $p['saasdbname'] =  md5($db->getConnectionString());
                        $p['horuxController'] = $dataObj['name'];
                        $devices[] = $p;
                    }
                }

                $ret['horuxRepeaterData'] = $horuxRepeaterData;
                $ret['plugins'] = $plugins;
                $ret['devices'] = $devices;
            }
        }
      return $ret;
    }

    /**
     * @return mixed Return the horux controllers
     * @soapmethod
     */
    public function getControllers()
    {
        $app = Prado::getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;
        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        $db->Active=true;

        $sql = "SELECT * FROM hr_horux_controller";

        $cmd= $db->createCommand($sql);
        $data = $cmd->query();
        $data = $data->readAll();

        return $data;
    }

    /**
     * @param string $date The date (Y-m-d)
     * @return mixed Return the daily tracking infos
     * @soapmethod
     */
    public function getDailyTracking($date)
    {
      if (!$date)
        $date = date("Y-m-d");
      $app = Prado::getApplication();
    	$db = $app->getModule('horuxDb')->DbConnection;
      $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
      $db->Active=true;
      $sql = "SELECT * FROM hr_tracking WHERE date = :date";
      $cmd = $db->createCommand($sql);
      $cmd->bindParameter(":date",$date,PDO::PARAM_STR);
      $data = $cmd->query();
      $data = $data->readAll();

      return $data;
    }
}

?>
