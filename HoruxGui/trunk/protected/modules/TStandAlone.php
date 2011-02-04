<?php


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
