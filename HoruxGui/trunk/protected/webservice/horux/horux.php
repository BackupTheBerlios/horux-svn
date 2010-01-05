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
        $db->Active=true;

        $sql = "SELECT
                    u.id,
                    u.name,
                    u.firstname,
                    u.picture,
                    u.language,
                    u.sex,
                    u.validity_date,
                    u.isBlocked,
                    d.name AS department,
                    u.pin_code,
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
                    u.fax AS fax_professional
                 FROM hr_user AS u 
                 LEFT JOIN hr_department AS d ON u.department = d.id
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
        $db->Active=true;

        $sql = "SELECT
                    id,
                    name,
                    firstname,
                    picture,
                    language,
                    sex,
                    validity_date,
                    isBlocked,
                    department AS department_id,
                    pin_code,
                    street AS street_private,
                    city AS city_private,
                    country AS country_private,
                    zip AS zip_private,
                    phone1 AS phone_private,
                    email1 AS email_private,
                    firme,
                    street_pr AS street_professional,
                    city_pr AS city_professional,
                    country_pr AS country_professional,
                    npa_pr AS zip_professional,
                    phone2 AS phone_professional,
                    email2 AS email_professional,
                    fax AS fax_professional
                FROM hr_user WHERE id>1 ORDER BY name, firstname";

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
        $dbName = md5($db->getConnectionString());

        $fp = fopen('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'system_status_'.$dbName.'.xml', 'w');
        fwrite($fp, $status);
        fclose($fp);
    }

    /**
     * @return mixed Return the data of the database or for specofic table
     * @param mixed $tables array of the table who musst be dump. ALL for all tables
     * @soapmethod
     */
    public function reloadDatabaseData($tables)
    {
        return $this->dumpDatabase(false, true, $tables);
    }

    /**
     * @return mixed Return the full schema of the database
     * @soapmethod
     */
    public function reloadDatabaseSchema()
    {
        return $this->dumpDatabase(true, false, 'ALL');
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
}

?>
