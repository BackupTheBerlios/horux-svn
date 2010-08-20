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

class install extends TPage
{

    public function onLoad($param)
    {
        parent::onLoad($param);

        $this->php_Version->Text = version_compare(PHP_VERSION, '5.1.0', '>=') ? Prado::localize('Yes'):'<span style="color:red">'.Prado::localize('No').'<span>';
        $this->xml->Text = extension_loaded('xml') ? Prado::localize('Yes'):'<span style="color:red">'.Prado::localize('No').'<span>';
        $this->mysql->Text = extension_loaded('mysql') ? Prado::localize('Yes'):'<span style="color:red">'.Prado::localize('No').'<span>';
        $this->sqlite->Text = extension_loaded('sqlite') ? Prado::localize('Yes'):'<span style="color:red">'.Prado::localize('No').'<span>';
        //$this->postgre->Text = extension_loaded('pgsql') ? Prado::localize('Yes'):'<span style="color:red">'.Prado::localize('No').'<span>';
        $this->zip->Text = extension_loaded('zip') ? Prado::localize('Yes'):'<span style="color:red">'.Prado::localize('No').'<span>';
        $this->pdo->Text = extension_loaded('pdo') ? Prado::localize('Yes'):'<span style="color:red">'.Prado::localize('No').'<span>';
        $this->pdo_mysql->Text = extension_loaded('pdo_mysql') ? Prado::localize('Yes'):'<span style="color:red">'.Prado::localize('No').'<span>';
        $this->application_xml->Text = is_writable('./protected/application_p.xml') ? Prado::localize('Yes'):'<span style="color:red">'.Prado::localize('No').'<span>';


        $memory_limit = substr(ini_get("memory_limit"),0, strlen(ini_get("memory_limit"))-1);
        $this->memory_limit->Text = $memory_limit>=128 ? Prado::localize('Yes'):'<span style="color:red">'.Prado::localize('No').'<span>';


        spl_autoload_unregister(array('Prado','autoload'));

        @include('XML/RPC2/Client.php');

        $this->pear_xmlrpc->Text = class_exists('XML_RPC2_Client') ? Prado::localize('Yes'):'<span style="color:red">'.Prado::localize('No').'<span>';

        if(
            !version_compare(PHP_VERSION, '5.1.0', '>=') ||
            !extension_loaded('xml') ||
            !extension_loaded('mysql') ||
            //!extension_loaded('sqlite') ||
            !extension_loaded('zip') ||
            !extension_loaded('pdo') ||
            !extension_loaded('pdo_mysql') ||
            !class_exists('XML_RPC2_Client') ||
            !is_writable('./protected/application_p.xml')
        )
        {
            $this->isOk->Value="false";

        }

        spl_autoload_register(array('Prado','autoload'));
        
        $this->safe_mode->Text = ini_get('safe_mode')? Prado::localize('On'):Prado::localize('Off');
        $this->errors->Text = ini_get('display_errors')? Prado::localize('On'):Prado::localize('Off');
        $this->file_transfert->Text = ini_get('file_uploads')? Prado::localize('On'):Prado::localize('Off');
        $this->magic_quotes->Text = ini_get('magic_quotes_runtime')? Prado::localize('On'):Prado::localize('Off');
        $this->register_global->Text = ini_get('register_globals')? Prado::localize('On'):Prado::localize('Off');
        $this->output_beffering->Text = ini_get('output_buffering')? Prado::localize('On'):Prado::localize('Off');
        $this->session_auto_start->Text = ini_get('session.auto_start')? Prado::localize('On'):Prado::localize('Off');

        if($this->dbServer->getSelectedValue() == "sqlite")
        {
            $this->hostname->setEnabled (false);
            $this->username_db->setEnabled (false);
            $this->password_db->setEnabled (false);
            $this->dbname->setEnabled (false);
        }
        else
        {
            $this->hostname->setEnabled (true);
            $this->username_db->setEnabled (true);
            $this->password_db->setEnabled (true);
            $this->hostname->setEnabled (true);
            $this->dbname->setEnabled (true);
        }
    }


    public function checkBaseParam($sender,$param)
    {
        if(
            !version_compare(PHP_VERSION, '5.1.0', '>=') ||
            !extension_loaded('xml') ||
            !extension_loaded('mysql') ||
            //!extension_loaded('sqlite') ||
            //!extension_loaded('pgsql') ||
            !extension_loaded('zip') ||
            !is_writable('./protected/application_p.xml')
        )
        {
            $param->IsValid=false;

        }
    }

    public function nextStepChanged($sender,$param)
    {

    }

    public function createDb($sender,$param)
    {
        $param->IsValid = $this->createDatabase();
    }

    protected function createDatabase()
    {
        switch($this->dbServer->getSelectedValue())
        {
            case "sqlite":
                return $this->createSqlite();
                break;
            case "mysql":
                return $this->createMysql();
                break;
            /*case "pgsql":
                $this->createPgsql();
                break;*/

        }

        return false;
    }

    protected function createSqlite()
    {
        if(!is_writable('./protected/sqlitedb'))
        {
            $this->dberror->Text = Prado::localize("The directory ./protected/sqlitedb must be writeable");
            return false;
        }

        if(file_exists('./protected/sqlitedb/horux.db3'))
        {
            $this->dberror->Text = Prado::localize("The database is already existing in ./protected/sqlitedb");
            return false;
        }

        if ($db = new PDO('sqlite:./protected/sqlitedb/horux.db3'))
        {
            chmod("./protected/sqlitedb/horux.db3", 0777);

            if(!(@$buffer = file_get_contents('./protected/pages/install/horux.sqlite')) )
            {
                $this->dberror->Text = Prado::localize("Cannot read the sql installation file :")." ./protected/pages/install/horux.sqlite";
                return false;
            }

            $queries = $this->splitSql($buffer);


            foreach ($queries as $query)
            {
                $query = trim($query);
                if ($query != '' && $query {0} != '#')
                {
                    if (!$db->query($query))
                    {
                        $this->dberror->Text = Prado::localize("Query error: ".$query);
                        unlink('./protected/sqlitedb/horux.db3');

                        return false;
                    }
                }
            }
        }

        return $this->writeApplication('sqlite');
    }

    protected function createPgsql()
    {
    }

    protected function createMysql()
    {
        @$link = mysql_connect($this->hostname->safeText, $this->username_db->safeText, $this->password_db->safeText);

        if(!$link)
        {
            $this->dberror->Text = Prado::localize("Cannot connect to the database, please check your parameters.");

            return false;
        }

        $selectResult = mysql_select_db( $this->dbname->safeText );

        if(!$selectResult)
        {
            $sql = 'CREATE DATABASE '.$this->dbname->safeText.' CHARACTER SET `utf8`';

            if (!mysql_query($sql, $link))
            {
                $this->dberror->Text = Prado::localize("Cannot create the database: ".mysql_error());
                return false;
            }
        }

        $selectResult = mysql_select_db( $this->dbname->safeText );

        if(!$selectResult)
        {
            $this->dberror->Text = Prado::localize("Impossible to select the database {n}", array('n'=>$this->dbname->safeText));
            return false;
        }

        if(!(@$buffer = file_get_contents('./protected/pages/install/horux.mysql')) )
        {
            $this->dberror->Text = Prado::localize("Cannot read the sql installation file :")." ./protected/pages/install/horux.mysql";
            return false;
        }

        $queries = $this->splitSql($buffer);


        foreach ($queries as $query)
        {
            $query = trim($query);
            if ($query != '' && $query {0} != '#')
            {
                if (!mysql_query($query, $link))
                {
                    $this->dberror->Text = Prado::localize("Query error: ".mysql_error());
                    return false;
                }
            }
        }

        return $this->writeApplication('mysql');
    }

    protected function writeApplication($driver)
    {
        $buffer = "";
        if($driver == 'mysql')
        {
            if(!(@$buffer = file_get_contents('./protected/application_install_mysql.xml')) )
            {
                $this->dberror->Text = Prado::localize("Cannot read the application configuration file :")." ./protected/application_install_mysql.xml";
                return false;
            }

            $data = array($this->hostname->safeText, $this->username_db->safeText,$this->password_db->safeText,$this->dbname->safeText);
            $data_replace = array('%host','%username','%password','%dbname');
            $buffer = str_replace($data_replace, $data, $buffer);

        }

        if($driver == 'sqlite')
        {
            if(!(@$buffer = file_get_contents('./protected/application_install_sqlite.xml')) )
            {
                $this->dberror->Text = Prado::localize("Cannot read the application configuration file :")." ./protected/application_install_sqlite.xml";
                return false;
            }
        }


        if(!(@$f = fopen('./protected/application_p.xml', 'w')) )
        {
            $this->dberror->Text = Prado::localize("Cannot open the application configuration file : ./protected/application_p.xml");
            return false;
        }

        if(!fwrite($f, $buffer))
        {
            $this->dberror->Text = Prado::localize("Cannot write the application configuration file : ./protected/application_p.xml");
            return false;
        }

        return true;
    }

    protected function splitSql($sql)
    {
        $sql = trim($sql);
        $sql = preg_replace("/\n\#[^\n]*/", '', "\n".$sql);
        $buffer = array ();
        $ret = array ();
        $in_string = false;

        for ($i = 0; $i < strlen($sql) - 1; $i ++) {
            if ($sql[$i] == ";" && !$in_string)
            {
                $ret[] = substr($sql, 0, $i);
                $sql = substr($sql, $i +1);
                $i = 0;
            }

            if ($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\")
            {
                $in_string = false;
            }
            elseif (!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset ($buffer[0]) || $buffer[0] != "\\"))
            {
                $in_string = $sql[$i];
            }
            if (isset ($buffer[1]))
            {
                $buffer[0] = $buffer[1];
            }
            $buffer[1] = $sql[$i];
        }

        if (!empty ($sql))
        {
            $ret[] = $sql;
        }
        return ($ret);

    }

    public function addSite()
    {
        if($this->dbServer->getSelectedValue() == "mysql")
        {
            @$link = mysql_connect($this->hostname->safeText, $this->username_db->safeText, $this->password_db->safeText);
            $selectResult = mysql_select_db( $this->dbname->safeText );

            $query = "INSERT INTO `hr_site` (`id`, `name`) VALUES (1, '".$this->sitename->safeText."')";

            mysql_query($query, $link);
        }

        if($this->dbServer->getSelectedValue() == "sqlite")
        {
            if ($db = new PDO('sqlite:./protected/sqlitedb/horux.db3'))
            {
                $query = "INSERT INTO `hr_site` (`id`, `name`) VALUES (1, '".$this->sitename->safeText."')";
                $db->query($query);
            }
        }

       /*if($this->dbServer->getSelectedValue() == "pgsql")
       {
       }*/
    }

    public function wizardCompleted($sender,$param)
    {
        if($this->dbServer->getSelectedValue() == "mysql")
        {
            @$link = mysql_connect($this->hostname->safeText, $this->username_db->safeText, $this->password_db->safeText);
            $selectResult = mysql_select_db( $this->dbname->safeText );

            $password = sha1($this->admin_password->safeText);

            $query = "INSERT INTO hr_superusers (`id` ,`group_id` ,`user_id` ,`name` ,`password` ,`isLogged` ,`locked` ,`session_id` ,`lastConnection`)VALUES ('1' , '1', '0', '".$this->admin_username->safeText."', '".$password."', '0', '0', '', '')";

            mysql_query($query, $link);
        }

        if($this->dbServer->getSelectedValue() == "sqlite")
        {
            if ($db = new PDO('sqlite:./protected/sqlitedb/horux.db3'))
            {
                $password = sha1($this->admin_password->safeText);
                $query = "INSERT INTO hr_superusers (`id` ,`group_id` ,`user_id` ,`name` ,`password` ,`isLogged` ,`locked` ,`session_id` ,`lastConnection`)VALUES ('1' , '1', '0', '".$this->admin_username->safeText."', '".$password."', '0', '0', '', '')";
                $db->query($query);
            }
        }

       /*if($this->dbServer->getSelectedValue() == "pgsql")
       {
       }*/

        fopen("./protected/runtime/.installed", "a");

        $this->Response->redirect($this->Service->constructUrl('login.login'));

    }
}

?>
