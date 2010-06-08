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

class TGuiLog extends TModule
{
  private $db = NULL;

  public function init($config)
  {
    parent::init($config);
  }

  public function log($log)
  {
    $db = $this->Application->getModule('horuxDb')->DbConnection;
    $db->Active=true;

    $username = $this->Application->getUser()->getName();

    $sql = "INSERT INTO hr_gui_log (
                        `who` ,
                        `what`
                  )
                  VALUES (
                        :who,
                        :what
                  )";

    $cmd = $db->createCommand( $sql );
    $cmd->bindValue(":who",$username,PDO::PARAM_STR);
    $cmd->bindValue(":what",$log, PDO::PARAM_STR);
    $cmd->execute();
  }
}


?>
