<?php

 Prado::using('horux.pages.components.timuxuser.employee');

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

            $overtime = $employee->getOvertimeMonth(date('Y'), date('n'));


            $computeBookings = $employee->getComputeBookings(date('Y'), date('n'), date('j'));

            foreach($computeBookings as $b)
            {
                $overtime = bcadd($overtime,$b['overtime'],2);
            }

            $val .= $overtime."/";


            $y = date('Y');
            $m = date('n');
            if($m == 1)
            {
                $y--;
                $m = 12;
            }
            else
            {
                $m--;
            }

            $wt = $employee->getWorkingTime($y, $m);

            if(!$wt)
            {
                $holidaysLastMonth = $employee->geHolidaystMonth(date('Y'), date('n'));
            }
            else
            {
                $holidaysLastMonth = $employee->geHolidaystLastMonth(date('Y'), date('n'));
            }

            $defaultHolidayTimeCode = $employee->getDefaultHolidaysCounter();
            $holidays = $employee->getRequest(date('Y'), date('n'),$defaultHolidayTimeCode);

            $holidaysTotal = bcsub($holidaysLastMonth, $holidays['nbre'],2);

            $val .= $holidaysTotal;

            $val .= ";";
        }

        return $val;
    }
}

?>
