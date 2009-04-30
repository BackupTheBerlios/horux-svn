<?php

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
    $cmd->bindParameter(":who",$username,PDO::PARAM_STR);
    $cmd->bindParameter(":what",$log, PDO::PARAM_STR);
    $cmd->execute();
  }
}


?>
