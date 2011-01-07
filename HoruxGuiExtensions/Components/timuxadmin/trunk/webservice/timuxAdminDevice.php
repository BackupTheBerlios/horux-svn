<?php

// load parameters
$confFile = './protected/pages/components/timuxuser/config.xml';
if (file_exists($confFile)) {
  $config = new TApplicationConfiguration;
  $config->loadFromFile($confFile);
  $param = new TMap;
  foreach($config->getParameters() as $id=>$parameter) {
	  if(is_array($parameter)) {
		  $component=Prado::createComponent($parameter[0]);
		  foreach($parameter[1] as $name=>$value)
			  $component->setSubProperty($name,$value);
		  $param->add($id,$component);
	  }
	  else
		  $param->add($id,$parameter);
  }
}
else
  $param = Prado::getApplication()->getParameters();
$computation = $param['computation'];

Prado::using('horux.pages.components.timuxuser.'.$computation);

class timuxAdminDevice
{

    public function syncStandalone($ids)
    {
	$app = Prado::getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;
        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        $db->Active=true;

        $ids = explode(",", $ids);
        $idsOk = array();
        foreach($ids as $id)
        {

            $sql = "SELECT COUNT(*) AS n FROM hr_gantner_standalone_action WHERE id=".$id;
            $cmd= $db->createCommand($sql);
            $data = $cmd->query();
            $data = $data->read();
            if($data['n'] > 0)
            {

                $sql = "DELETE FROM hr_gantner_standalone_action WHERE id=".$id;


                $cmd= $db->createCommand($sql);

                if($cmd->execute())
                {
                    $idsOk[] = $id;
                }
            }
            else
                $idsOk[] = $id;

        }

        return implode(",", $idsOk);
    }


    public function syncBalances()
    {
	$app = Prado::getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;
        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        $db->Active=true;

        $sql = "SELECT id FROM hr_user WHERE name!='??' AND firstname!='??' AND isBlocked=0";
        $cmd= $db->createCommand($sql);
        $data = $cmd->query();
        $data = $data->readAll();

        $val = "";

        foreach($data as $data)
        {
            $val .= $data['id']."/";
            $employee = new employee($data['id']);

            $overTimeLastMonth = $employee->getOvertimeLastMonth(date('n'), date('Y'));

            $overTimeMonth = 0;
            for($day=1; $day<date('j');$day++) {
                $todo = $employee->getDayTodo($day,date('n'), date('Y'));
                $done = $employee->getDayDone($day,date('n'), date('Y'));
                $overTimeMonth = bcadd($overTimeMonth, bcsub($done['done'], $todo ,4),4 );
            }

            //get the info the current day only if the overtime +
            $todo = $employee->getDayTodo(date('j'),date('n'), date('Y'));
            $done = $employee->getDayDone(date('j'),date('n'), date('Y'));

            if(bcsub($done['done'], $todo ,4) > 0) {
                $overTimeMonth = bcadd($overTimeMonth, bcsub($done['done'], $todo ,4),4 );
            }

            $overtime = bcadd($overTimeLastMonth,$overTimeMonth,4);

            $val .= sprintf("%.02f",$overtime)."/";


            $lastYear = $employee->geHolidaystMonth(date('Y')-1,12);

            $nvy = $employee->geHolidaystMonth(date('Y'), date('n'));
            for($month=1; $month<date('n');$month++)
            {
                $nv = $employee->getRequest(date('Y'), $month, $employee->getDefaultHolidaysCounter());
                $nvy -= $nv['nbre'];
            }
            $nvy = bcsub($nvy, $lastYear,4);

            $holidays = $employee->getRequest(date('Y'),date('n'),$defH);

            $holidaysLastMonth = $nvy + $lastYear;

            $holidaysCurrentMonth = bcsub($holidaysLastMonth, $holidays['nbre'],2);

            $val .= sprintf("%.02f",$holidaysCurrentMonth);

            $val .= ";";
        }

        return $val;
    }
}

?>
