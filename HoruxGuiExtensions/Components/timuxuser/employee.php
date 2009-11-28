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

class employee
{
    protected $employeeId = NULL;
    protected $db = NULL;

    function __construct($userId) {
        $this->db = Prado::getApplication()->getModule('horuxDb')->DbConnection;
        $this->db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);

        $this->db->Active=true;

        $this->employeeId = $userId;
    }

    /**
     *  Return the name of the employee
     */
    public function getName()
    {
        $cmd = $this->db->createCommand( "SELECT name FROM hr_user WHERE id=:id" );
        $cmd->bindParameter(":id",$this->employeeId,PDO::PARAM_STR);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            return $data['name'];
        }

        return "";

    }

    /**
     *  Return the firstname of the employee
     */
    public function getFirstName()
    {
        $cmd = $this->db->createCommand( "SELECT firstname FROM hr_user WHERE id=:id" );
        $cmd->bindParameter(":id",$this->employeeId,PDO::PARAM_STR);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            return $data['firstname'];
        }

        return "";
    }

    /**
     *  Return the concatenation of the name et the firsname of the employee
     */
    public function getFullName()
    {
        $cmd = $this->db->createCommand( "SELECT CONCAT(name, ' ', firstname) AS fullname FROM hr_user WHERE id=:id" );
        $cmd->bindParameter(":id",$this->employeeId,PDO::PARAM_STR);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            return $data['fullname'];
        }

        return "";
    }

    /*
     * Return the bookings of the employee
     * @param status Status of the booking. 0->OUT, 1->IN, all->IN and OUT
     * @param from Date where starting the booking
     * @param until Date where stopping the booking
     * @param order Order the booking DESC or ASC
     * @param timeFrom Time where start the booking in the day. False->all the day
     * @param timeUntil Time where stop the booking in the day. False->all the day
     */
    public function getBookings($status, $from, $until, $order="DESC", $timeFrom = false, $timeUntil=false)
    {

       if($timeFrom && $timeUntil)
       {
            $time = " tb.roundBooking>='$timeFrom' AND tb.roundBooking<='$timeUntil' AND ";
       }
       else
        $time = "";

       $statusTmp = $status;

        switch($status)
        {
            case '1':
                $status = ' (tb.action=255  OR  tb.action=100 ) AND';
                break;
            case '0':
                $status = ' (tb.action=254  OR  tb.action=100 ) AND ';
                break;
            default:
                $status = '';

         }

        $date = "";

        if($from == "" && $until == "")
        {
            //take the current month

            $date = getdate();
            $from = $date['year'].'-'.$date['mon'].'-1';
            $until = $date['year'].'-'.$date['mon'].'-'.date("t");

            $this->from->Text = "1-".$date['mon']."-".$date['year'];
            $this->until->Text = date("t")."-".$date['mon']."-".$date['year'];

            $date = " t.date>='$from' AND t.date<='$until' AND ";
        }
        else
        {
            if($from != "" && $until != "")
            {
                $date = " t.date>='$from' AND t.date<='$until' AND ";
            }
            if($from != "" && $until == "")
            {
                $date = " t.date>='$from' AND ";
            }
            if($from == "" && $until != "")
            {
                $date = " t.date<='$until' AND ";
            }

        }

        $cmd=$this->db->createCommand("SELECT CONCAT(u.name, ' ' , u.firstname) AS employee, t.id, t.date, tb.roundBooking AS time, tb.action, tb.actionReason, d.name AS department, tb.internet FROM hr_tracking AS t LEFT JOIN hr_timux_booking AS tb ON tb.tracking_id=t.id LEFT JOIN hr_user AS u ON u.id=t.id_user LEFT JOIN hr_department AS d ON d.id=u.department WHERE $date $status $time u.id='.$this->employeeId.' AND tb.action!='NULL' ORDER BY t.date $order, t.time $order  LIMIT 0,1000");

        $data = $cmd->query();
        $data = $data->readAll();

        $dataTmp = array();
        foreach($data as $d)
        {
            if($d['action'] == '255' || $d['action'] == '254')
            {
               $d['inout'] = $d['action'] == '255' ? 'in' : 'out';
               $dataTmp[] = $d;
            }

            if($d['action'] == '100')
            {
                $ar = explode("_",$d['actionReason']);

                if(count($ar)>1)
                {
                    $cmd=$this->db->createCommand("SELECT *  FROM hr_timux_timecode WHERE id=".$ar[0]);

                    $data = $cmd->query();
                    $data = $data->read();

                    if($ar[1] == 'IN' && $statusTmp == 1)
                    {
                       $d['inout'] = 'in';
                       $dataTmp[] = $d;
                    }
                    else
                    {
                        if($ar[1] == 'OUT' && $statusTmp === 0)
                        {
                            $d['inout'] = 'out';
                            $d['timeworked'] = $data['timeworked'];
                            $d['timecodeid'] = $ar[0];
                            $d['timecode'] = $data['name'];
                            $dataTmp[] = $d;
                        }
                        else
                        {
                            if($statusTmp === 'all')
                            {
                                $d['inout'] = $ar[1] == 'IN' ? 'in' : 'out';
                                $d['timeworked'] = $data['timeworked'];
                                $d['timecodeid'] = $ar[0];
                                $d['timecode'] = $data['name'];
                                $dataTmp[] = $d;
                            }
                        }
                    }
                }
                else
                {
                    $cmd=$this->db->createCommand("SELECT *  FROM hr_timux_timecode WHERE id=".$ar[0]);

                    $data = $cmd->query();
                    $data = $data->read();

                    if($data['signtype'] == 'in' && $statusTmp == 1)
                    {
                        $d['inout'] = 'in';
                        $d['timeworked'] = $data['timeworked'];
                        $d['timecodeid'] = $ar[0];
                        $d['timecode'] = $data['name'];
                        $dataTmp[] = $d;
                    }
                    else
                    {
                        if($data['signtype'] == 'out' && $statusTmp == 0)
                        {
                           $d['inout'] = 'out';
                           $d['timeworked'] = $data['timeworked'];
                           $d['timecodeid'] = $ar[0];
                           $d['timecode'] = $data['name'];
                           $dataTmp[] = $d;
                        }
                        else
                        {
                            if($statusTmp === 'all')
                            {
                                $d['inout'] = $data['signtype'] == 'in' ? 'in' : 'out';
                                $d['timeworked'] = $data['timeworked'];
                                $d['timecodeid'] = $ar[0];
                                $d['timecode'] = $data['name'];
                                $dataTmp[] = $d;
                            }
                        }
                    }

                }
            }
        }

        return $dataTmp;
    }

    /*
     * Get the role of the employee. Return employee|manager|rh
     */
    public function getRole()
    {
        $cmd = $this->db->createCommand( "SELECT role FROM hr_timux_workingtime WHERE user_id=:id ORDER BY id DESC LIMIT 0,1" );
        $cmd->bindParameter(":id",$this->employeeId,PDO::PARAM_STR);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            return $data['role'];
        }

        return "";
    }

    /*
     * Get the department of the employee
     */
    public function getDepartmentId()
    {
        $cmd = $this->db->createCommand( "SELECT department FROM hr_user WHERE id=:id" );
        $cmd->bindParameter(":id",$this->employeeId,PDO::PARAM_STR);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            return $data['department'];
        }

        return "";
    }

    /*
     * Return the working time of the employee according to the year and the month
     *
     */
    public function getWorkingTime($year, $month)
    {
        $date = $year."-".$month."-".date('t', mktime(0,0,0,$month,1,$year));
        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_workingtime WHERE user_id=".$this->employeeId." AND startDate<'".$date."' ORDER BY startDate DESC" );
        $query = $cmd->query();
        return $query->read();

    }

    /*
     * Check if have error in the bookings for a month
     */
    public function getError($year=0, $month=0)
    {
        $result = array();

        $nbreOfDay = 0;
        // Get the current month
        if($year == 0 && $month == 0)
        {
            $year = date("Y");
            $month = date("n");
            $nbreOfDay = date("j")-1;
        }
        else
        {
            if($month > date("n") && $year >= date("Y"))
                return array();

            if($month == date("n") && $year == date("Y"))
                $nbreOfDay = date("j")-1;
            else
                $nbreOfDay = date("t",mktime(0,0,0,$month,1,$year));
        }

        // check all days in the month
        for($i=1; $i<=$nbreOfDay;$i++)
        {
            $date = sprintf("%02d",$i).'-'.sprintf("%02d",$month).'-'.$year;
            $dateSql = $year.'-'.$month.'-'.$i;


            $bookings = $this->getBookings('all', $dateSql, $dateSql,'ASC');
            $bookings_in = $this->getBookings(1, $dateSql, $dateSql,'ASC');
            $bookings_out = $this->getBookings(0, $dateSql, $dateSql,'ASC');

            if(count($bookings_in) != count($bookings_out))
            {
               $remark = "";
               $typeText = Prado::localize("Signing missing");
               foreach($bookings as $b)
               {
                  $remark .= substr($b['time'],0,5).'/';
                  $remark .= $b['inout'] == 'in' ? Prado::localize('In') : Prado::localize('Out');
                  $remark .= $b['internet'] ? "*" :"";
                  $remark .= "&nbsp;&nbsp;&nbsp;";
               }

               $result[] = array('date'=>  $date, 'typeText'=>$typeText, 'remark'=>$remark, 'type'=>'sign');
            }
        }

        return $result;
    }

    /*
     * Get the occupancy of the employee
     */
    public function getPercentage($year,$month)
    {

        $wt = $this->getWorkingTime($year, $month);
        return $wt['workingPercent'];

    }

    /*
     * Get the timux config
     */
    public function getConfig()
    {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_config" );
        $query = $cmd->query();
        return $query->read();
    }

    /*
     * Get the number of days worked by week
     */
    function getDaysByWeek()
    {
        $config = $this->getConfig();
        return $config['daysByWeek'];
    }

    /*
     * Get the number of hours by day
     */
    function getHoursByDay()
    {
        $config = $this->getConfig();

        return bcdiv($config['hoursByWeek'], $config['daysByWeek'], 4);
    }

    
    /*
     * Return the number of hours that musst be done in a month
     */
    public function getHoursMonth($year, $month)
    {
        $nbreOfDay =  date("t",mktime(0,0,0,$month,1,$year));

        $hoursByDay = $this->getHoursByDay();

        $res = 0.0;
        
        for($i=1;$i<=$nbreOfDay; $i++)
        {
            $day =  date("w",mktime(0,0,0,$month,$i,$year));
            $sqlDate = "$year-$month-$i";

            if($day >= 1 && $day <= $this->getDaysByWeek())
            {
               $res = bcadd($hoursByDay,$res,2);
            }
        }

        //substract the non working day
        $res = bcsub($res, bcmul($this->getAllNonWorkingDay($year,$month),$hoursByDay,4),4);

        // return the result according to the occupancy
        return bcdiv(bcmul($res, $this->getPercentage($year,$month),2),100.00,4);
    }


    /*
     * Return the number of non working day for a day
     * @param year Year to get the non working day
     * @param month Month to get the non working day
     * @return Return the number of day
     */
    public function getNonWorkingDay($year,$month, $day)
    {
        $date = $year."-".$month."-".$day;

        $cmd = $this->db->createCommand( "SELECT period FROM hr_non_working_day AS n WHERE n.from<='$date' AND n.until>='$date'" );
        $query = $cmd->query();
        $data = $query->read();

        if($data)
        {
            if($data['period'] == 'allday')
                return 1;
            else
                return 0.5;
        }

        return 0;
    }

    /*
     * Return the period of non working day for
     * @param year Year to get the non working day
     * @param month Month to get the non working day
     * @return Return the number of day
     */
    public function getNonWorkingDayPeriod($year,$month, $day)
    {
        $date = $year."-".$month."-".$day;

        $cmd = $this->db->createCommand( "SELECT period FROM hr_non_working_day AS n WHERE n.from<='$date' AND n.until>='$date'" );
        $query = $cmd->query();
        $data = $query->read();

        if($data)
        {
           return $data['period'];
        }

        return false;
    }

    /*
     * Return the number of non working day for a month
     * @param year Year to get the non working day
     * @param month Month to get the non working day
     * @return Return the number of day
     */
    public function getAllNonWorkingDay($year,$month)
    {
        $dateFrom = $year."-".$month."-1";
        $dateTo = $year."-".$month."-".date("t",mktime(0,0,0,$month,1,$year));

        $cmd = $this->db->createCommand( "SELECT * FROM hr_non_working_day AS n WHERE n.from>='$dateFrom' AND n.until<='$dateTo'" );
        $query = $cmd->query();
        $data = $query->readAll();

        $nbre = 0;
        foreach($data as $d)
        {
            if($d['period'] == 'allday')
            {
                if($d['from'] == $d['until'])
                {
                    $nbre += 1;
                }
                else
                {
                    $nbre += 1;
                    while($d['from'] != $d['until'])
                    {
                        $d['from'] = date("Y-m-d",strtotime(date("Y-m-d", strtotime($d['from'])) . " +1 day"));
                        $nbre += 1;
                    }
                }
            }
            else
            {
                if($d['from'] == $d['until'])
                {
                    $nbre += 0.5;
                }
                else
                {
                    $nbre += 0.5;
                    while($d['from'] != $d['until'])
                    {
                        $d['from'] = date("Y-m-d",strtotime(date("Y-m-d", strtotime($d['from'])) . " +1 day"));
                        $nbre += 0.5;
                    }
                }
            }
        }

        return $nbre;
    }


    /*
     * Return the number of hours worked in a day
     */
    public function getTimeWorked($year, $month, $day)
    {
        $date = $year."-".$month."-".$day;

        $config = $this->getConfig();
        $hoursBlockMorning4 = strtotime ($config['hoursBlockMorning4'] );
        $hoursBlockAfternoon1= strtotime ($config['hoursBlockAfternoon1']);

        $data = $this->getBookings('all',$date,$date,'ASC');

        if(count($data) == 0) return 0;

        $diff = 0;

        $outWT = 0.0;

        $timeworkedInM = false;
        $timeworkedOutM = false;
        $timeworkedInA = false;
        $timeworkedOutA = false;

        $signFirstM = false;
        $signLastM = false;
        $signFirstA = false;
        $signLastA = false;

        // find the first and last signing at the morning
        // and the first and last signing at the afternoon
        for($i=0; $i<count($data);$i++)
        {
            $time = strtotime ($data[$i]['time']);

            if($i == 0 && $data[$i]['inout'] == 'in')
            {
                $signFirstM = $time;
            }

            if($data[$i]['inout'] == 'out' && $time <= $hoursBlockMorning4)
            {
                $signLastM = $time;
            }

            if($i == count($data)-1 && $data[$i]['inout'] == 'out')
            {
                $signLastA = $time;
            }

            if(!$signFirstA && $data[$i]['inout'] == 'in' && $time >= $hoursBlockMorning4)
            {
                $signFirstA = $time;
            }
        }

        //remove the uncompute timeworked signing
        $dTmp = array();
        for($i=0; $i<count($data);$i++)
        {
            $time = strtotime ($data[$i]['time']);

            if($time === $signFirstA ||
               $time === $signLastA ||
               $time === $signFirstM ||
               $time === $signLastM
            )
            {
                $dTmp[] = $data[$i];
            }
            else
            {
                // this kind of signing musst computed
                if($data[$i]['timeworked'] == '0' )
                {
                    $dTmp[] = $data[$i];
                }
            }
        }
        
        $data = $dTmp;
        for($i=0; $i<count($data);$i+=2)
        {
            // take the in signing
            $in = strtotime ($data[$i]['time']);

            // the first sign in at the morning is a time worked time code
            if($signFirstM == $in && $data[$i]['timeworked'] == '1')
            {
                $timeworkedInM = true;
            }

            // the first sign in at the afternonn is a time worked time code
            if($signFirstA == $in && $data[$i]['timeworked'] == '1')
            {
                $timeworkedInA = true;
            }

            // check if the out signing is exsting
            if(isset($data[$i+1]))
            {
                // take the in signing
                $out = strtotime( $data[$i+1]['time'] );

                // the last sign out at the afternoon is a time worked time code
                if($signLastA == $out && $data[$i+1]['timeworked'] == '1')
                {
                    $timeworkedOutA = true;
                }


                // the last sign out at the morning is a time worked time code
                if($signLastM == $out && $data[$i+1]['timeworked'] == '1')
                {
                    $timeworkedOutM = true;
                }

                // do the difference
                $diff += $out - $in;
            }
        }

        $diff = bcdiv($diff,3600,4);

        $hoursByDayTodo = $this->getTimeHoursDayTodo($year, $month);



        // compensation
        if($timeworkedInM || $timeworkedOutM)
        {
           $diffC = $signLastM - $signFirstM;

           if(bcdiv($diffC,3600,4) < bcdiv($hoursByDayTodo,2,4) )
           {
               $diff = bcadd(bcsub(bcdiv($hoursByDayTodo,2,4), bcdiv($diffC,3600,4),4), $diff, 4);
           }
           
        }

        if($timeworkedOutA || $timeworkedInA)
        {
           $diffC = $signLastA - $signFirstA;

           if(bcdiv($diffC,3600,4) < bcdiv($hoursByDayTodo,2,4) )
           {
               $diff = bcadd(bcsub(bcdiv($hoursByDayTodo,2,4), bcdiv($diffC,3600,4),4),$diff,4);
           }

        }

        //check the break
        if($config['minimumBreaks']>0)
        {
            $break = bcdiv($config['minimumBreaks'], 60, 4);
            // no sign at the noon
            if($signLastM===false && $signFirstA===false)
            {
                $diff = bcsub($diff, $break, 4);
            }
            else
            {
                if($signLastM>0 && $signFirstA>0)
                {
                    $realBreak = bcdiv($signFirstA,3600,4)-bcdiv($signLastM,3600,4);
                    if( $realBreak <  $break)
                    {
                        $diff = bcsub($diff, $break-$realBreak, 4);
                    }
                }
            }
        }

        return  $diff;
    }

    public function isBreakOk($year, $month, $day)
    {
        $date = $year."-".$month."-".$day;

        $config = $this->getConfig();
        $hoursBlockMorning4 = strtotime ($config['hoursBlockMorning4'] );
        $hoursBlockAfternoon1= strtotime ($config['hoursBlockAfternoon1']);
        
        $data = $this->getBookings('all',$date,$date,'ASC');

        if(count($data) == 0) return true;


        $signFirstM = false;
        $signLastM = false;
        $signFirstA = false;
        $signLastA = false;

        // find the first and last signing at the morning
        // and the first and last signing at the afternoon
        for($i=0; $i<count($data);$i++)
        {
            $time = strtotime ($data[$i]['time']);

            if($i == 0 && $data[$i]['inout'] == 'in')
            {
                $signFirstM = $time;
            }

            if($data[$i]['inout'] == 'out' && $time <= $hoursBlockMorning4)
            {
                $signLastM = $time;
            }

            if($i == count($data)-1 && $data[$i]['inout'] == 'out')
            {
                $signLastA = $time;
            }

            if(!$signFirstA && $data[$i]['inout'] == 'in' && $time >= $hoursBlockMorning4)
            {
                $signFirstA = $time;
            }
        }

        //check the break
        if($config['minimumBreaks']>0)
        {
            $break = bcdiv($config['minimumBreaks'], 60, 4);
            // no sign at the noon
            if($signLastM===false && $signFirstA===false)
            {
                return false;
            }
            else
            {
                if($signLastM>0 && $signFirstA>0)
                {
                    $realBreak = bcdiv($signFirstA,3600,4)-bcdiv($signLastM,3600,4);
                    if( $realBreak <  $break)
                    {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function getTimeHoursDayTodo($year, $month)
    {
        $hoursByMonth = $this->getHoursMonth($year, $month);

        $nbreOfDay =  date("t",mktime(0,0,0,$month,1,$year));

        $nDay = 0;

        for($i=1; $i<=$nbreOfDay; $i++)
        {
            if($this->isWorking($year, $month, $i))
            {
                $nwd = $this->getNonWorkingDay($year, $month, $i);
                if($nwd>0 && $nwd<1)
                {
                    $nDay += $nwd;
                }
                elseif($nwd==0)
                    $nDay++;
            }
        }

        if($nDay>0)
            return bcdiv($hoursByMonth, $nDay,4);
        else
            return 0;
    }



    public function isWorking($year, $month, $day)
    {
        $wt = $this->getWorkingTime($year, $month);

        $dayName = strtolower(date("l",mktime(0,0,0,$month,$day,$year)));

        if($wt[$dayName.'Time_m'] > 0 || $wt[$dayName.'Time_a'] > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
       
    }

    /*
     * Return the number of general absence for a day
     * @param year Year to get the holiday
     * @param month Month to get the holiday
     * @return Return the number of day
     */
    public function getAbsence($year,$month, $day)
    {
        $date = $year."-".$month."-".$day;

        $cmd = $this->db->createCommand( "SELECT l.period FROM hr_timux_request AS r LEFT JOIN hr_timux_request_leave AS l ON l.request_id=r.id  WHERE r.userId=".$this->employeeId." AND l.datefrom<='$date' AND l.dateto>='$date' AND ( r.state='validate' OR r.state='closed' )" );
        $query = $cmd->query();
        $data = $query->read();

        if($data)
        {
            if($data['period'] == 'allday')
                return 1;
            else
                return 0.5;
        }

        return 0;
    }

    /*
     * Return the period of general absence for a day
     * @param year Year to get the holiday
     * @param month Month to get the holiday
     * @return Return the number of day
     */
    public function getAbsencePeriod($year,$month, $day)
    {
        $date = $year."-".$month."-".$day;

        $cmd = $this->db->createCommand( "SELECT l.period FROM hr_timux_request AS r LEFT JOIN hr_timux_request_leave AS l ON l.request_id=r.id  WHERE r.userId=".$this->employeeId." AND l.datefrom<='$date' AND l.dateto>='$date' AND ( r.state='validate' OR r.state='closed' )" );
        $query = $cmd->query();
        $data = $query->read();

        if($data)
        {
            return $data['period'];
        }

        return false;
    }


    public function getTimeCode($date, $period='allday')
    {
        if($period=='morning' || $period=='afternoon')
        {
            $period =  " AND ( period='$period' OR period='allday') ";
        }
        else
            $period =  " AND period='$period'";


        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_request AS r LEFT JOIN hr_timux_request_leave AS l ON l.request_id=r.id LEFT JOIN hr_timux_timecode AS t ON t.id=r.timecodeId WHERE r.userId=".$this->employeeId." AND l.datefrom<='$date' AND l.dateto>='$date' AND ( r.state='validate' OR r.state='closed' ) $period" );
        $query = $cmd->query();
        $data = $query->read();

        return $data;
    }

    public function getOvertimeLastMonth($year, $month)
    {
        if($month == 1)
        {
            $month = 12;
            $year -=1;
        }
        else
            $month -= 1;

        $timeCode = $this->getDefaultOvertimeCounter();

        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE year=$year AND month=$month AND timecode_id=$timeCode AND user_id=".$this->employeeId );
        $query = $cmd->query();
        $data = $query->read();

        return $data['nbre'];
    }


    public function getDefaultOvertimeCounter()
    {
        $cmd = $this->db->createCommand( "SELECT id FROM hr_timux_timecode WHERE defaultOvertime=1" );
        $query = $cmd->query();
        $data = $query->read();
        return $data['id'];

    }

    public function getDefaultHolidaysCounter()
    {
        $cmd = $this->db->createCommand( "SELECT id FROM hr_timux_timecode WHERE defaultHoliday=1" );
        $query = $cmd->query();
        $data = $query->read();
        return $data['id'];

    }


    public function getRequest($year,$month, $timecode)
    {
        $hoursByDay = $this->getHoursByDay();

        $dateFrom = $year."-".$month."-1";
        $dateTo = $year."-".$month."-".date("t",mktime(0,0,0,$month,1,$year));

        $wt = $this->getWorkingTime($year, $month);

        //update the counter according to the leave request
        $cmd=$this->db->createCommand("SELECT * FROM hr_timux_request AS r LEFT JOIN hr_timux_request_leave AS rl ON rl.request_id=r.id LEFT JOIN hr_timux_timecode AS t ON t.id=r.timecodeId WHERE rl.datefrom>='".$dateFrom."' AND rl.dateto<='".$dateTo."' AND ( r.state='validate' OR  r.state='closed') AND r.userId=".$this->employeeId." AND t.id=".$timecode);

        $data = $cmd->query();
        $data = $data->readAll();

        $nbreOfHours['nbre'] = 0.0;
        $nbreOfHours['disp'] = $format;

        foreach($data AS $d)
        {
            $dateStart = $d['datefrom'];
            $dateTo = $d['dateto'];
            $period = $d['period'];
            $format = $d['formatDisplay'];

            if($dateTo == '0000-00-00' ) $dateTo = $dateStart;


            if($dateTo == $dateStart)
            {
                $date = explode("-",$dateTo);

                $nwd = $this->getNonWorkingDay($date[0], $date[1], $date[2]);

                if($this->isWorking($date[0], $date[1], $date[2]))
                {
                   if($period == 'allday')
                     $nbreOfHours['nbre'] += 1;
                   else
                     $nbreOfHours['nbre'] += 0.5;

                   $nbreOfHours['nbre'] -= $nwd;
                }

            }
            else
            {

                while($dateStart != $dateTo)
                {
                    $date = explode("-",$dateStart);
                    $nwd = $this->getNonWorkingDay($date[0], $date[1], $date[2]);

                    if($this->isWorking($date[0], $date[1], $date[2]))
                    {
                       if($period == 'allday')
                         $nbreOfHours['nbre'] += 1;
                       else
                         $nbreOfHours['nbre'] += 0.5;
                    }

                    $nbreOfHours['nbre'] -= $nwd;

                    $dateStart = date("Y-m-d",strtotime(date("Y-m-d", strtotime($dateStart)) . " +1 day"));

                }

                $date = explode("-",$dateTo);

                if($this->isWorking($date[0], $date[1], $date[2]))
                {
                   $nwd = $this->getNonWorkingDay($date[0], $date[1], $date[2]);
                    
                   if($period == 'allday')
                     $nbreOfHours['nbre'] += 1;
                   else
                     $nbreOfHours['nbre'] += 0.5;

                   $nbreOfHours['nbre'] -= $nwd;
                }

            }

        }

        if($nbreOfHours['disp'] == 'hour')
        {
           //$nbreOfHours['nbre'] = bcdiv($nbreOfHours['nbre'], $hoursByDay, 4);
        }

        return $nbreOfHours;
    }

    public function geHolidaystLastMonth($year, $month)
    {
        if($month == 1)
        {
            $month = 12;
            $year -=1;
        }
        else
            $month -= 1;

        $timeCode = $this->getDefaultHolidaysCounter();

        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE year=$year AND month=$month AND timecode_id=$timeCode AND user_id=".$this->employeeId );
        $query = $cmd->query();
        $data = $query->read();

        return $data['nbre'];
    }


    public function getMonthOvertime($year, $month)
    {
        $nbreOfDay = date("t",mktime(0,0,0,$month,1,$year));

        $todoDay = $this->getTimeHoursDayTodo($year, $month);
        $todoMonth = $this->getHoursMonth($year, $month);

        $timeWorked = 0.0;

        for($i=1; $i<=$nbreOfDay;$i++)
        {
            $done = $this->getTimeWorked($year, $month, $i);

            if($this->isWorking($year, $month, $i))
            {
                $nwd = $this->getNonWorkingDay($year, $month, $i);
                $h = $this->getAbsence($year, $month, $i);

                if($nwd == 0 && $h == 0)
                {
                    $timeWorked = bcadd($done,$timeWorked,4);
                }
                else
                {
                    $t = $todoDay;
                    $tNwd = bcmul($todoDay,$nwd,4);
                    if(round($tNwd,2)==0)
                        $tNwd = 0.0;
                    $tH = 0;
                    if($h>0)
                        $tH = bcmul($todoDay,$h-$nwd,4);
                    if(round($tH,2)==0)
                        $tH = 0.0;

                    $t = bcsub($t,$tNwd,4);


                    $donept = bcadd($tH,$done,4);
                    $timeWorked = bcadd($donept,$timeWorked,4);
                }
            }
            else
            {
                $timeWorked = bcadd($done,$timeWorked,4);
            }
        }

        return bcsub($timeWorked,$todoMonth, 4);
    }



    /*public function getLeaveRequest($state)
    {
        if($state != 'all')
            $state = ' tr.state=\''.$state.'\' AND ';
        else
            $state = '';


        $cmd = $this->db->createCommand( "SELECT tr.*, CONCAT(u.name, ' ', u.firstname ) AS modUser, tt.name AS timcodeName, rl.* FROM hr_timux_request AS tr LEFT JOIN hr_user AS u ON u.id=tr.modifyUserId LEFT JOIN hr_timux_timecode AS tt ON tt.id=tr.timecodeId LEFT JOIN hr_timux_request_leave AS rl ON rl.request_id=tr.id  WHERE $state tr.userId=:id AND tr.state<>'closed' AND tr.state<>'canceled' AND rl.datefrom > CURDATE() ORDER BY tr.createDate DESC" );
        $cmd->bindParameter(":id",$this->employeeId,PDO::PARAM_STR);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->readAll();
            return $data;
        }

        return array();        
    }

    public function isNeedWorking($year, $month, $nday, $period='allday', $day=1)
    {
        $wt = $this->getWorkingTime($year, $month);
        $nw_m = $this->isNonWorkingDay($year."-".$month."-".$day, "morning");
        $nw_a = $this->isNonWorkingDay($year."-".$month."-".$day, "afternoon");
        $var = 'nw_'.$period;


        switch($nday)
        {
            case 1:
                if($period=='allday')
                {
                    if(!$nw_m && !$nw_a>0)
                    {
                        return bcadd($wt['mondayTime_m'], $wt['mondayTime_a'],2);
                    }
                    else
                    {
                        if(!$nw_m)
                            return $wt['mondayTime_m'];
                        elseif(!$nw_a)
                            return $wt['mondayTime_a'];
                    }
                }
                else
                    if(!$$var) return $wt['mondayTime_'.$period];
                break;
            case 2:
                if($period=='allday')
                {
                    if(!$nw_m && !$nw_a>0)
                    {
                        return bcadd($wt['tuesdayTime_m'], $wt['tuesdayTime_a'],2);
                    }
                    else
                    {
                        if(!$nw_m)
                            return $wt['tuesdayTime_m'];
                        elseif(!$nw_a)
                            return $wt['tuesdayTime_a'];
                    }
                }
                else
                    if(!$$var) return $wt['tuesdayTime_'.$period];
                break;
            case 3:
                if($period=='allday')
                {
                    if(!$nw_m && !$nw_a>0)
                    {
                        return bcadd($wt['wednesdayTime_m'], $wt['wednesdayTime_a'],2);
                    }
                    else
                    {
                        if(!$nw_m)
                            return $wt['wednesdayTime_m'];
                        elseif(!$nw_a)
                            return $wt['wednesdayTime_a'];
                    }
                }
                else
                    if(!$$var) return $wt['wednesdayTime_'.$period];
                break;
            case 4:
                if($period=='allday')
                {
                    if(!$nw_m && !$nw_a>0)
                    {
                        return bcadd($wt['thursdayTime_m'], $wt['thursdayTime_a'],2);
                    }
                    else
                    {
                        if(!$nw_m)
                            return $wt['thursdayTime_m'];
                        elseif(!$nw_a)
                            return $wt['thursdayTime_a'];
                    }
                }
                else
                    if(!$$var) return $wt['thursdayTime_'.$period];
                break;
            case 5:
                if($period=='allday')
                {
                    if(!$nw_m && !$nw_a>0)
                    {
                        return bcadd($wt['fridayTime_m'], $wt['fridayTime_a'],2);
                    }
                    else
                    {
                        if(!$nw_m)
                            return $wt['fridayTime_m'];
                        elseif(!$nw_a)
                            return $wt['fridayTime_a'];
                    }
                }
                else
                    if(!$$var) return $wt['fridayTime_'.$period];
                break;
            case 6:
                if($period=='allday')
                {
                    if(!$nw_m && !$nw_a>0)
                    {
                        return bcadd($wt['saturdayTime_m'], $wt['saturdayTime_a'],2);
                    }
                    else
                    {
                        if(!$nw_m)
                            return $wt['saturdayTime_m'];
                        elseif(!$nw_a)
                            return $wt['saturdayTime_a'];
                    }
                }
                else
                    if(!$$var) return $wt['saturdayTime_'.$period];
                break;
            case 7:
                if($period=='allday')
                {
                    if(!$nw_m && !$nw_a>0)
                    {
                        return bcadd($wt['sundayTime_m'], $wt['sundayTime_a'],2);
                    }
                    else
                    {
                        if(!$nw_m)
                            return $wt['sundayTime_m'];
                        elseif(!$nw_a)
                            return $wt['sundayTime_a'];
                    }
                }
                else
                    if(!$$var) return $wt['sundayTime_'.$period];
                break;
        }
        
        return false;
        
    }


    public function isNonWorkingDay($date, $period='allday')
    {
        if($period=='morning' || $period=='afternoon')
        {
            $period =  " AND ( period='$period' OR period='allday') ";
        }
        else
            $period =  " AND period='$period'";

        $cmd = $this->db->createCommand( "SELECT COUNT(*) AS nbre FROM hr_non_working_day AS n WHERE n.from<='$date' AND n.until>='$date' $period " );
        $query = $cmd->query();
        $data = $query->read();

        return $data['nbre'];
    }

    public function isVacation($date, $period='allday')
    {
        if($period=='morning' || $period=='afternoon')
        {
            $period =  " AND ( period='$period' OR period='allday') ";
        }
        else
            $period =  " AND period='$period'";


        $cmd = $this->db->createCommand( "SELECT COUNT(*) AS nbre FROM hr_timux_request AS r LEFT JOIN hr_timux_request_leave AS l ON l.request_id=r.id  WHERE r.userId=".$this->employeeId." AND l.datefrom<='$date' AND l.dateto>='$date' AND ( r.state='validate' OR r.state='closed' ) $period" );
        $query = $cmd->query();
        $data = $query->read();

        return $data['nbre'];
    }

    public function getTimeCode($date, $period='allday')
    {
        if($period=='morning' || $period=='afternoon')
        {
            $period =  " AND ( period='$period' OR period='allday') ";
        }
        else
            $period =  " AND period='$period'";


        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_request AS r LEFT JOIN hr_timux_request_leave AS l ON l.request_id=r.id LEFT JOIN hr_timux_timecode AS t ON t.id=r.timecodeId WHERE r.userId=".$this->employeeId." AND l.datefrom<='$date' AND l.dateto>='$date' AND ( r.state='validate' OR r.state='closed' ) $period" );
        $query = $cmd->query();
        $data = $query->read();

        return $data;
    }

    public function getConfigTimeBooking()
    {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_config" );
        $query = $cmd->query();
        $data = $query->read();

        $res[1] = $data['hoursBlockMorning1'];
        $res[2] = $data['hoursBlockMorning2'];
        $res[3] = $data['hoursBlockMorning3'];
        $res[4] = $data['hoursBlockMorning4'];
        $res[5] = $data['hoursBlockAfternoon1'];
        $res[6] = $data['hoursBlockAfternoon2'];
        $res[7] = $data['hoursBlockAfternoon3'];
        $res[8] = $data['hoursBlockAfternoon4'];

        return $res;
    }



    public function getHoursByDay()
    {
        $config = $this->getConfig();

        return bcdiv($config['hoursByWeek'] , $config['daysByWeek'], 2);
    }

    public function closeMonth($year, $month)
    {
        
        $hoursByDay = $this->getHoursByDay();

        // check if the month is already closed
        $cmd=$this->db->createCommand("SELECT * FROM hr_timux_closed_month WHERE user_id=".$this->employeeId);
        $data = $cmd->query();
        $data = $data->readAll();

        $error = $this->getError($year, $month);

        if(count($data)==0 && count($error) == 0)
        {
            $request = $this->getRequest($year, $month);

            foreach($request as $k=>$v)
            {
                $cmd=$this->db->createCommand("SELECT * FROM hr_timux_timecode WHERE id=".$k);
                $data = $cmd->query();
                $data = $data->read();

                if($data['type'] == 'leave')
                    $this->updateCounter($k,$v['nbre'],$year, $month,"-");
                if($data['type'] == 'absence')
                    $this->updateCounter($k,$v['nbre'],$year, $month,"+");
            }

            //update the counter according to overtime
            $timeCodeId = $this->getDefaultOvertimeCounter();
            $nbreOfHours = $this->getMonthOvertime($year, $month);
            $this->updateCounter($timeCodeId,$nbreOfHours,$year, $month,"+");

            // add the vacation day to the counter
            if($month == 12)
            {
                $wt = $this->getWorkingTime($year, $month);
                $nbre = $wt['holidaysByYear'];
                $nbre = bcdiv( bcmul($nbre, $wt['workingPercent'],2), 100.0, 2);
                $timeCodeId = $this->getDefaultHolidaysCounter();
                $cmd=$this->db->createCommand("UPDATE hr_timux_activity_counter SET nbre=nbre+$nbre WHERE year=0 AND month=0 AND user_id=".$this->employeeId." AND timecode_id=".$timeCodeId);
                $cmd->execute();

            }


            // set the month as closed
            $cmd = $this->db->createCommand( "INSERT INTO hr_timux_closed_month SET user_id=".$this->employeeId." , year=$year, month=$month" );
            $query = $cmd->execute();

            // set the validate request to closed
            $this->closeRequest($year,$month);

        }
    }

    public function closeRequest($year,$month)
    {
        $dateFrom = $year."-".$month."-1";
        $dateTo = $year."-".$month."-".date("t",mktime(0,0,0,$month,1,$year));

        $cmd=$this->db->createCommand("SELECT r.id FROM hr_timux_request AS r LEFT JOIN hr_timux_request_leave AS rl ON rl.request_id=r.id LEFT JOIN hr_timux_timecode AS t ON t.id=r.timecodeId WHERE rl.datefrom>='".$dateFrom."' AND rl.dateto<='".$dateTo."' AND r.state='validate' AND r.userId=".$this->employeeId);

        $data = $cmd->query();
        $data = $data->readAll();

        $app = Prado::getApplication();
        $usedId = $app->getUser()->getUserID() == null ? 0 : $app->getUser()->getUserID();

        $cmd=$this->db->createCommand("SELECT user_id FROM hr_superusers WHERE id=$usedId");
        $data2 = $cmd->query();
        $dataUser = $data2->read();
        $userId = $dataUser['user_id'];

        foreach($data as $d)
        {
            $cmd=$this->db->createCommand("UPDATE hr_timux_request SET state='closed', modifyDate=CURDATE(),modifyUserId=".$userId." WHERE id=".$d['id']);
            $cmd->execute();
        }
    }



    public function getRequest($year,$month)
    {

        $hoursByDay = $this->getHoursByDay();

        $dateFrom = $year."-".$month."-1";
        $dateTo = $year."-".$month."-".date("t",mktime(0,0,0,$month,1,$year));

        $wt = $this->getWorkingTime($year, $month);

        //update the counter according to the leave request
        $cmd=$this->db->createCommand("SELECT * FROM hr_timux_request AS r LEFT JOIN hr_timux_request_leave AS rl ON rl.request_id=r.id LEFT JOIN hr_timux_timecode AS t ON t.id=r.timecodeId WHERE rl.datefrom>='".$dateFrom."' AND rl.dateto<='".$dateTo."' AND ( r.state='validate' OR  r.state='closed') AND r.userId=".$this->employeeId);

        $data = $cmd->query();
        $data = $data->readAll();

        $nbreOfHours = array();

        foreach($data AS $d)
        {
            $dateStart = $d['datefrom'];
            $dateTo = $d['dateto'];
            $period = $d['period'];
            $timecodeId = $d['timecodeId'];
            $format = $d['formatDisplay'];

            if(!isset($nbreOfHours[$timecodeId]))
            {
                $nbreOfHours[$timecodeId]['nbre'] = 0.0;
                $nbreOfHours[$timecodeId]['disp'] = $format;

            }

            if($dateTo == '0000-00-00' ) $dateTo = $dateStart;


            if($dateTo == $dateStart)
            {
                $date = explode("-",$dateTo);
                $hoursM = $this->isNeedWorking($date[0], $date[1],  date("N",mktime(0,0,0,$date[1],$date[2],$date[0])),'m', $date[2]);
                $hoursA = $this->isNeedWorking($date[0], $date[1],  date("N",mktime(0,0,0,$date[1],$date[2],$date[0])),'a', $date[2]);

                if($period == 'morning') $hoursA = 0.0;
                if($period == 'afternoon') $hoursM = 0.0;

                if($hoursM || $hoursA )
                {
                   if($this->isNonWorkingDay($dateTo,'morning')) $hoursM = 0;
                   if($this->isNonWorkingDay($dateTo,'afternoon')) $hoursA = 0;
                   $nbreOfHours[$timecodeId]['nbre'] += ($hoursM + $hoursA);
                }

            }
            else
            {

                while($dateStart != $dateTo)
                {
                    $date = explode("-",$dateStart);

                    $hoursM = $this->isNeedWorking($date[0], $date[1],  date("N",mktime(0,0,0,$date[1],$date[2],$date[0])),'m',$date[2]);
                    $hoursA = $this->isNeedWorking($date[0], $date[1],  date("N",mktime(0,0,0,$date[1],$date[2],$date[0])),'a',$date[2]);

                    if($period == 'morning') $hoursA = 0.0;
                    if($period == 'afternoon') $hoursM = 0.0;

                    if($hoursM || $hoursA )
                    {

                       if($this->isNonWorkingDay($dateStart,'morning')) $hoursM = 0;
                       if($this->isNonWorkingDay($dateStart,'afternoon')) $hoursA = 0;

                       $nbreOfHours[$timecodeId]['nbre'] += ($hoursM + $hoursA);
                    }

                    $dateStart = date("Y-m-d",strtotime(date("Y-m-d", strtotime($dateStart)) . " +1 day"));

                }

                $date = explode("-",$dateTo);
                $hoursM = $this->isNeedWorking($date[0], $date[1],  date("N",mktime(0,0,0,$date[1],$date[2],$date[0])),'m', $date[2]);
                $hoursA = $this->isNeedWorking($date[0], $date[1],  date("N",mktime(0,0,0,$date[1],$date[2],$date[0])),'a', $date[2]);

                if($period == 'morning') $hoursA = 0.0;
                if($period == 'afternoon') $hoursM = 0.0;

                if($hoursM || $hoursA )
                {
                   if($this->isNonWorkingDay($dateTo,'morning')) $hoursM = 0;
                   if($this->isNonWorkingDay($dateTo,'afternoon')) $hoursA = 0;
                   $nbreOfHours[$timecodeId]['nbre'] += ($hoursM + $hoursA);
                }

            }

        }

        foreach($nbreOfHours as $k=>$v)
        {
            if($nbreOfHours[$k]['disp'] == 'day')
            {
               $nbreOfHours[$k]['nbre'] = bcdiv($nbreOfHours[$k]['nbre'], $hoursByDay, 4);
            }
        }
        
        return $nbreOfHours;
    }

    public function updateCounter($timeCodeId,$nbreOfHours, $year, $month, $func)
    {
        $cmd=$this->db->createCommand("SELECT * FROM hr_timux_activity_counter  WHERE  user_id=".$this->employeeId." AND timecode_id=".$timeCodeId." AND month=0 AND year=0");

        $data = $cmd->query();
        $data = $data->read();

        $cmd=$this->db->createCommand("INSERT hr_timux_activity_counter SET nbre=".$data['nbre'].$func."$nbreOfHours, year=$year, month=$month, user_id=".$this->employeeId.", timecode_id=".$timeCodeId);
        $cmd->execute();

        $cmd=$this->db->createCommand("UPDATE hr_timux_activity_counter SET nbre=nbre".$func."$nbreOfHours WHERE year=0 AND month=0 AND user_id=".$this->employeeId." AND timecode_id=".$timeCodeId);
        $cmd->execute();

    }

    public function getDefaultOvertimeCounter()
    {
        $cmd = $this->db->createCommand( "SELECT id FROM hr_timux_timecode WHERE defaultOvertime=1" );
        $query = $cmd->query();
        $data = $query->read();
        return $data['id'];
    
    }

    public function getDefaultHolidaysCounter()
    {
        $cmd = $this->db->createCommand( "SELECT id FROM hr_timux_timecode WHERE defaultHoliday=1" );
        $query = $cmd->query();
        $data = $query->read();
        return $data['id'];

    }

    public function getMonthOvertime($year, $month)
    {
        $res = 0.0;
        $nbreOfDay = date("t",mktime(0,0,0,$month,1,$year));

        if($year == date('Y') && $month==date('n'))
            $nbreOfDay = date('j')-1;

        for($i=1; $i<=$nbreOfDay;$i++)
        {
            $time = $this->getDayOvertime($year, $month, $i);

            $res = bcadd($time['overtime'], $res, 4);
        }
        return $res;
    }

    public function getTimeWorkedMonth($year, $month)
    {
        $res = 0.0;
        $nbreOfDay = date("t",mktime(0,0,0,$month,1,$year));

        if($year == date('Y') && $month==date('n'))
            $nbreOfDay = date('j')-1;

        for($i=1; $i<=$nbreOfDay;$i++)
        {
            $time = $this->getDayOvertime($year, $month, $i);

            $res = bcadd($time['inHours'], $res, 4);
        }
        return $res;
    }

    public function geHolidaystLastMonth($year, $month)
    {
        if($month == 1)
        {
            $month = 12;
            $year -=1;
        }
        else
            $month -= 1;

        $timeCode = $this->getDefaultHolidaysCounter();

        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE year=$year AND month=$month AND timecode_id=$timeCode AND user_id=".$this->employeeId );
        $query = $cmd->query();
        $data = $query->read();

        return $data['nbre'];
    }

    public function geHolidaystMonth($year, $month)
    {
        $timeCode = $this->getDefaultHolidaysCounter();

        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE year=$year AND month=$month AND timecode_id=$timeCode AND user_id=".$this->employeeId );
        $query = $cmd->query();
        $data = $query->read();

        if($data)
        {
            return $data['nbre'];
        }
        else
        {
            $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE year=0 AND month=0 AND timecode_id=$timeCode AND user_id=".$this->employeeId );
            $query = $cmd->query();
            $data = $query->read();
            return $data['nbre'];
        }
    }

    public function getOvertimeLastMonth($year, $month)
    {
        if($month == 1)
        {
            $month = 12;
            $year -=1;
        }
        else
            $month -= 1;

        $timeCode = $this->getDefaultOvertimeCounter();

        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE year=$year AND month=$month AND timecode_id=$timeCode AND user_id=".$this->employeeId );
        $query = $cmd->query();
        $data = $query->read();

        return $data['nbre'];
    }

    public function getOvertimeMonth($year, $month)
    {
        $timeCode = $this->getDefaultOvertimeCounter();

        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE year=$year AND month=$month AND timecode_id=$timeCode AND user_id=".$this->employeeId );
        $query = $cmd->query();
        $data = $query->read();

        return $data['nbre'];
    }

    public function getDayOvertime($year, $month, $day)
    {
        $hoursWorking = $this->isNeedWorking($year, $month,  date("N",mktime(0,0,0,$month,$day,$year)),'allday', $day);
        $hoursWorkingM = $this->isNeedWorking($year, $month,  date("N",mktime(0,0,0,$month,$day,$year)), 'm', $day);
        $hoursWorkingA = $this->isNeedWorking($year, $month,  date("N",mktime(0,0,0,$month,$day,$year)), 'a', $day);

        $date = $year."-".$month."-".$day;

        if($this->isVacation($date, "morning"))
        {
            $hoursWorking = bcsub($hoursWorking,$hoursWorkingM,2);
        }

        if($this->isVacation($date, "afternoon"))
        {
            $hoursWorking = bcsub($hoursWorking,$hoursWorkingA,2);
        }

        if($this->isNonWorkingDay($date, "morning"))
        {
            $hoursWorking = bcsub($hoursWorking,$hoursWorkingM,2);
        }

        if($this->isNonWorkingDay($date, "afternoon"))
        {
            $hoursWorking = bcsub($hoursWorking,$hoursWorkingA,2);
        }

        if($hoursWorking>0)
        {
            $cmd = $this->db->createCommand( "SELECT b.roundBooking FROM hr_timux_booking AS b LEFT JOIN hr_tracking AS t ON t.id=b.tracking_id WHERE id_user=".$this->employeeId." AND t.date='".$date."' ORDER BY b.roundBooking" );
            $query = $cmd->query();
            $data = $query->readAll();

            $diff = 0;

            for($i=0; $i<count($data);$i+=2)
            {
                $in = strtotime ($data[$i]['roundBooking']);
                $out = strtotime( $data[$i+1]['roundBooking'] );

                $diff += $out - $in;


            }

            $diffT = $diff;

            if( $days=intval((floor($diff/86400))) )
                $diff = $diff % 86400;
                
            if( $hours=intval((floor($diff/3600))) )
                $diff = $diff % 3600;

            if( $minutes=intval((floor($diff/60))) )
                $diff = $diff % 60;

            $diff    =    intval( $diff );

            $overtime = bcdiv($diffT - bcmul($hoursWorking , 3600, 4),3600,4);
            $inHours = bcdiv($diffT,3600,4);

            return array('inHours'=>$inHours ,'days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes, 'seconds'=>$diff, 'overtime'=>$overtime);

        }
        else
        {
            $cmd = $this->db->createCommand( "SELECT b.roundBooking FROM hr_timux_booking AS b LEFT JOIN hr_tracking AS t ON t.id=b.tracking_id WHERE id_user=".$this->employeeId." AND t.date='".$date."' ORDER BY b.roundBooking" );
            $query = $cmd->query();
            $data = $query->readAll();

            $diff = 0;

            for($i=0; $i<count($data);$i+=2)
            {
                $in = strtotime ($data[$i]['roundBooking']);
                $out = strtotime( $data[$i+1]['roundBooking'] );

                $diff += $out - $in;


            }

            $diffT = $diff;

            if( $days=intval((floor($diff/86400))) )
                $diff = $diff % 86400;

            if( $hours=intval((floor($diff/3600))) )
                $diff = $diff % 3600;

            if( $minutes=intval((floor($diff/60))) )
                $diff = $diff % 60;

            $diff    =    intval( $diff );

            $overtime = bcdiv($diffT,3600,4);
            $inHours = bcdiv($diffT,3600,4);

            return array('inHours'=>$inHours ,'days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes, 'seconds'=>$diff, 'overtime'=>$overtime);
        }
    }*/



    /*
     * Return the number of hours that musst be done in a month
     */
    /*public function getHoursMonth($year, $month)
    {
        $config = $this->getConfig();
        $hoursByDay = $this->getHoursByDay();

        $nbreOfDay =  date("t",mktime(0,0,0,$month,1,$year));

        $res = 0.0;
        for($i=1;$i<=$nbreOfDay; $i++)
        {
            $day =  date("w",mktime(0,0,0,$month,$i,$year));
            $sqlDate = "$year-$month-$i";

            if($day >= 1 && $day <= 5)
            {
                if(!$this->isNonWorkingDay($sqlDate, 'morning') && !$this->isNonWorkingDay($sqlDate, 'afternoon'))
                {
                    $res = bcadd($hoursByDay,$res,2);
                }
                else
                {
                    if(!$this->isNonWorkingDay($sqlDate, 'morning'))
                    {
                        $res = bcadd(bcdiv($hoursByDay,2,2),$res,2);
                    }
                    
                    if(!$this->isNonWorkingDay($sqlDate, 'afternoon'))
                    {
                        $res = bcadd(bcdiv($hoursByDay,2,2),$res,2);
                    }
                }
            }
        }

        return $res;
    }


    function getTodoByDay($year, $month)
    {
        $nbreOfDay = date("t",mktime(0,0,0,$month,1,$year));
        $percentage = $this->getPercentage($year,$month);
        $hoursForTheMonth = $this->getHoursMonth($year, $month);
        $hoursForTheMonthAtX = bcdiv(bcmul($hoursForTheMonth,$percentage,2),100.00,4);

        $daysForTheMonth = 0;

        for($i=1; $i<=$nbreOfDay;$i++)
        {
           if($this->isNeedWorking($year, $month, date("N",mktime(0,0,0,$month,$i,$year)),'allday', $i) > 0.0)
           {
                $daysForTheMonth++;
           }
        }

        return bcdiv($hoursForTheMonthAtX,$daysForTheMonth,4);

    }*/
}

?>
