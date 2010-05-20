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


    public function syncBalances($id)
    {
		$app = Prado::getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;
        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        $db->Active=true;

        if($id == "0")
            $sql = "SELECT id FROM hr_user WHERE name!='??' AND firstname!='??' AND isBlocked=0";
        else
            $sql = "SELECT id FROM hr_user WHERE id=$id";

        $cmd= $db->createCommand($sql);
        $data = $cmd->query();
        $data = $data->readAll();

        $val = "";

        foreach($data as $data)
        {
            $employee = new employee($data['id']);

            $wt = $employee->getWorkingTime(date('Y'), date('n'));

            if($wt)
            {
                //Balance of holiday fot the last year
                $lastYear = $employee->geHolidaystMonth(date('Y')-1,12);


                //Nbre of holiday that the employee has for the year
                $nvy = $employee->geHolidaystForTheYear(date('Y'), date('n'));

                for($i=1; $i<date('n');$i++)
                {
                    $nv = $employee->getRequest(date('Y'), $i, $employee->getDefaultHolidaysCounter());
                    $nvy -= $nv['nbre'];
                }


                $holidayForTheYear = $nvy;

                // compute the holdiday for the last month
                $holidaysLastMonth = $holidayForTheYear + $lastYear;

                // get the holiday for this month
                $defaultHolidayTimeCode = $employee->getDefaultHolidaysCounter();
                $holidays = $employee->getRequest(date('Y'), date('n'),$defaultHolidayTimeCode);

                // balance of the last month and the current month
                $holidaysTotal = bcsub($holidaysLastMonth, $holidays['nbre'],4);

                if($holidaysTotal>0)
                    $holidaysTotal = sprintf("+%.02f",$holidaysTotal);
                elseif($holidaysTotal<0 || $holidaysTotal==0)
                    $holidaysTotal = sprintf("%.02f",$holidaysTotal);

                $val .= $data['id']."/";

                // get the overtime from the last month
                $lastOvertime = $employee->getOvertimeLastMonth(date('Y'), date('n'));

                $timeWorked = $employee->getMonthTimeWorked(date('Y'), date('n'));

                $todo = $employee->getTimeHoursDayTodo(date('Y'), date('n'));


                $timeWorked['done'] = bcadd($timeWorked['done'], bcmul($holidays['nbre'],$todo,4), 4);

                $untilLastDay = 0;

                for($i=1; $i<=date('d');$i++)
                {
                    if($employee->isWorking(date('Y'), date('n'), $i))
                    {
                        $nwd = $employee->getNonWorkingDay(date('Y'), date('n'), $i);

                        $untilLastDay++;

                        if($nwd>0)
                        {
                            $untilLastDay = $untilLastDay-$nwd;
                        }
                            
                    }
                }

                $untilLastDay = bcmul( $untilLastDay, $todo, 4);


                $overtime = bcsub($timeWorked['done'],$untilLastDay,4);
                $overtime = bcadd($overtime,$lastOvertime,4);

                // display the value
                if($overtime>0)
                    $overtime = sprintf("+%.02f",$overtime);
                elseif($overtime<0 || $overtime==0)
                    $overtime = sprintf("%.02f",$overtime);


                $val .=  $overtime."/";




                $val .= $holidaysTotal;

                $val .= ";";
            }
        }

        return $val;
    }
}

?>
