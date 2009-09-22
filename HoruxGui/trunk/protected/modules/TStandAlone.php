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

class TStandAlone extends TModule
{
  public function init($config)
  {
    parent::init($config);

  }

  public function addStandalone($function, $id, $param=NULL)
  {
    $db = $this->Application->getModule('horuxDb')->DbConnection;
    $db->Active=true;

    $sql = "SELECT type FROM hr_device GROUP BY type";

    $cmd = $db->createCommand( $sql );
    $data = $cmd->query();
    $data = $data->readAll();

    foreach($data as $d)
    {
        $type = $d['type'];

        try
        {
            Prado::using('horux.pages.hardware.device.'.$type.'.'.$type.'_standalone');
            $class = $type.'_standalone';
            if(class_exists($class))
            {
                $sa = new $class();
                $sa->addStandalone($function, $id, $param);
            }
        }
        catch(Exception $e)
        {
            //! do noting
        }
    }
  }
}

abstract class TDeviceStandalone extends TModule
{
    abstract public function addStandalone($function, $id, $param=NULL);
}

?>
