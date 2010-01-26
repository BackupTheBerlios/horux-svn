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

Prado::using('System.I18N.core.DateFormat');

class employee
{
    protected $employeeId = NULL;
    protected $db = NULL;

    private $hoursByDay = NULL;
    private $daysByWeek = NULL;

    private $signedValue = 0.0;
    private $timcode = array();

    function __construct($userId) {
        $this->db = Prado::getApplication()->getModule('horuxDb')->DbConnection;
        $this->db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);

        $this->db->Active=true;

        $this->employeeId = $userId;
    }

    public function getUserId()
    {
        return $this->employeeId;
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

        $cmd=$this->db->createCommand("SELECT  t.id, t.date, tb.roundBooking AS time, tb.action, tb.actionReason, tb.internet FROM hr_tracking AS t LEFT JOIN hr_timux_booking AS tb ON tb.tracking_id=t.id WHERE $date $status $time t.id_user=".$this->employeeId." AND tb.action!='NULL' ORDER BY t.date $order, t.time $order  LIMIT 0,500");
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
                    $cmd=$this->db->createCommand("SELECT timeworked,name,formatDisplay,type FROM hr_timux_timecode WHERE id=".$ar[0]);

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
                            $d['timecodetype'] = $data['type'];
                            $d['formatDisplay'] = $data['formatDisplay'];
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
                                $d['timecodetype'] = $data['type'];
                                $d['formatDisplay'] = $data['formatDisplay'];
                                $dataTmp[] = $d;
                            }
                        }
                    }
                }
                else
                {
                    $cmd=$this->db->createCommand("SELECT timeworked,name,formatDisplay,type FROM hr_timux_timecode WHERE id=".$ar[0]);

                    $data = $cmd->query();
                    $data = $data->read();

                    if($data['signtype'] == 'in' && $statusTmp == 1)
                    {
                        $d['inout'] = 'in';
                        $d['timeworked'] = $data['timeworked'];
                        $d['timecodeid'] = $ar[0];
                        $d['timecode'] = $data['name'];
                        $d['timecodetype'] = $data['type'];
                        $d['formatDisplay'] = $data['formatDisplay'];
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
                           $d['timecodetype'] = $data['type'];
                           $d['formatDisplay'] = $data['formatDisplay'];
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
                                $d['timecodetype'] = $data['type'];
                                $d['formatDisplay'] = $data['formatDisplay'];
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

    public function getDepartment()
    {
        $cmd = $this->db->createCommand( "SELECT d.name FROM hr_user AS u LEFT JOIN hr_department AS d ON d.id=u.department WHERE u.id=:id" );
        $cmd->bindParameter(":id",$this->employeeId,PDO::PARAM_STR);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            return $data['name'];
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
                  $remark .= '<a href="index.php?page=components.timuxuser.booking.mod&back=components.timuxuser.error.error&id='.$b['id'].'" >';
                  $remark .= substr($b['time'],0,5).'/';
                  $remark .= $b['inout'] == 'in' ? Prado::localize('In') : Prado::localize('Out');
                  $remark .= $b['internet'] ? "*" :"";
                  $remark .= '</a>';
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
        if($this->daysByWeek == NULL)
        {
            $config = $this->getConfig();
            $this->daysByWeek = $config['daysByWeek'];
        }

        return $this->daysByWeek;
    }

    /*
     * Get the number of hours by day
     */
    function getHoursByDay()
    {
        if($this->hoursByDay == NULL)
        {
            $config = $this->getConfig();

            if($config['daysByWeek'] > 0)
                $this->hoursByDay = bcdiv($config['hoursByWeek'], $config['daysByWeek'], 4);
            else
                $this->hoursByDay = 0;
        }

        return $this->hoursByDay;
    }

    
    /*
     * Return the number of hours that musst be done in a month
     */
    public function getHoursMonth($year, $month, $atxpercent=true)
    {
        if($atxpercent)
        {
            $cmd = $this->db->createCommand( "SELECT hoursByMonth FROM hr_timux_closed_month  WHERE year=$year AND month=$month AND user_id=".$this->employeeId );
            $query = $cmd->query();
            $data = $query->read();

            if($data)
            {
                return $data['hoursByMonth'];
            }
        }

        $nbreOfDay =  date("t",mktime(0,0,0,$month,1,$year));

        $hoursByDay = $this->getHoursByDay();

        $res = 0.0;
        
        for($i=1;$i<=$nbreOfDay; $i++)
        {
            $day =  date("w",mktime(0,0,0,$month,$i,$year));

            if($day >= 1 && $day <= $this->getDaysByWeek())
            {
               $res = bcadd($hoursByDay,$res,2);
            }
        }

        //substract the non working day
        $res = bcsub($res, bcmul($this->getAllNonWorkingDay($year,$month),$hoursByDay,4),4);

        if($atxpercent)
        {
            // return the result according to the occupancy
            return bcdiv(bcmul($res, $this->getPercentage($year,$month),2),100.00,4);
        }
        else
        {
            return $res;
        }
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
                    $day = explode("-",$d['from']);
                    $nday = date("N",mktime(0,0,0,$month,$day[2],$year));

                    if($nday>=1 && $nday<=5)
                    {
                        $nbre += 1;
                    }
                }
                else
                {
                    $day = explode("-",$d['from']);
                    $nday = date("N",mktime(0,0,0,$month,$day[2],$year));
                    if($nday>=1 && $nday<=5)
                    {
                        $nbre += 1;
                    }

                    while($d['from'] != $d['until'])
                    {
                        $d['from'] = date("Y-m-d",strtotime(date("Y-m-d", strtotime($d['from'])) . " +1 day"));
                        
                        $day = explode("-",$d['from']);
                        $nday = date("N",mktime(0,0,0,$month,$day[2],$year));
                        if($nday>=1 && $nday<=5)
                        {
                            $nbre += 1;
                        }
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


    public function getNonWorkingDayEndOfYear($year,$month)
    {
        $nbre = 0.0;
        for($i=$month+1; $i<=12; $i++)
        {
            $nbre += $this->getAllNonWorkingDay($year, $i);
        }

        return $nbre;
    }

    /*
     * Return the number of hours worked in a day
     */
    public function getTimeWorked($year, $month, $day, $withCompensation = true)
    {
        $date = $year."-".$month."-".$day;

        $config = $this->getConfig();
        $hoursBlockMorning4 = strtotime ($config['hoursBlockMorning4'] );
        $hoursBlockAfternoon1= strtotime ($config['hoursBlockAfternoon1']);

        $data = $this->getBookings('all',$date,$date,'ASC');

        if(count($data) == 0) return  array('time'=>0, 'timecode'=>array());

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

        $timecode = array();
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

            if(!$signFirstA && $data[$i]['inout'] == 'in' && $time >= $hoursBlockAfternoon1)
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
                $tc = array();
                $tc['id'] =  $data[$i]['timecodeid'];
                $tc['name'] =  $data[$i]['timecode'];
                $tc['formatDisplay'] = $data[$i]['formatDisplay'] ;
                $timecode['fm'] = $tc;
            }

            // the first sign in at the afternonn is a time worked time code
            if($signFirstA == $in && $data[$i]['timeworked'] == '1')
            {
                $timeworkedInA = true;
                $tc = array();
                $tc['id'] =  $data[$i]['timecodeid'];
                $tc['name'] =  $data[$i]['timecode'];
                $tc['formatDisplay'] = $data[$i]['formatDisplay'] ;
                $timecode['fa'] = $tc;
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
                    $tc = array();
                    $tc['id'] =  $data[$i+1]['timecodeid'];
                    $tc['name'] =  $data[$i+1]['timecode'];
                    $tc['formatDisplay'] = $data[$i+1]['formatDisplay'] ;
                    $timecode['la'] = $tc;
                }


                // the last sign out at the morning is a time worked time code
                if($signLastM == $out && $data[$i+1]['timeworked'] == '1')
                {
                    $timeworkedOutM = true;
                    $tc = array();
                    $tc['id'] =  $data[$i+1]['timecodeid'];
                    $tc['name'] =  $data[$i+1]['timecode'];
                    $tc['formatDisplay'] = $data[$i+1]['formatDisplay'] ;
                    $timecode['lm'] = $tc;
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
           if($signLastM && $signFirstM)
           {
                $diffC = $signLastM - $signFirstM;
           }
           else
           {
               if(!$signLastM)
               {
                    $diffC =  $hoursBlockMorning4 - $signFirstM;
               }
           }

           if(bcdiv($diffC,3600,4) < bcdiv($hoursByDayTodo,2,4) )
           {
               if($withCompensation)
                    $diff = bcadd(bcsub(bcdiv($hoursByDayTodo,2,4), bcdiv($diffC,3600,4),4), $diff, 4);

               if($timeworkedInM)
               {
                    if($timecode['fm']['time'] == 'day')
                    {
                        $timecode['fm']['time'] = bcdiv(bcsub(bcdiv($hoursByDayTodo,2,4), bcdiv($diffC,3600,4),4),$this->getTimeHoursDayTodo($year, $month),4);

                    }
                    else
                    {
                        $timecode['fm']['time'] = bcsub(bcdiv($hoursByDayTodo,2,4), bcdiv($diffC,3600,4),4);
                    }
               }

               if($timeworkedOutM)
               {
                    if($timecode['lm']['time'] == 'day')
                    {
                        $timecode['lm']['time'] = bcdiv(bcsub(bcdiv($hoursByDayTodo,2,4), bcdiv($diffC,3600,4),4),$this->getTimeHoursDayTodo($year, $month),4);

                    }
                    else
                    {
                        $timecode['lm']['time'] = bcsub(bcdiv($hoursByDayTodo,2,4), bcdiv($diffC,3600,4),4);
                    }
               }
           }
           
        }

        if($timeworkedOutA || $timeworkedInA)
        {
           $diffC = $signLastA - $signFirstA;

           if($signLastA && $signFirstA)
           {
                $diffC = $signLastM - $signFirstM;
           }
           else
           {
               if(!$signFirstA)
               {
                    $diffC =  $signLastA - $hoursBlockAfternoon1;
               }
           }

           if(bcdiv($diffC,3600,4) < bcdiv($hoursByDayTodo,2,4) )
           {
               if($withCompensation)
                   $diff = bcadd(bcsub(bcdiv($hoursByDayTodo,2,4), bcdiv($diffC,3600,4),4),$diff,4);
               
               if($timeworkedOutA)
               {
                    if($timecode['la']['formatDisplay'] == 'day')
                    {
                        $timecode['la']['time'] = bcdiv(bcsub(bcdiv($hoursByDayTodo,2,4), bcdiv($diffC,3600,4),4),$this->getTimeHoursDayTodo($year, $month),4);

                    }
                    else
                    {
                        $timecode['la']['time'] = bcsub(bcdiv($hoursByDayTodo,2,4), bcdiv($diffC,3600,4),4);
                    }
               }

               if($timeworkedInA)
               {
                    if($timecode['fa']['formatDisplay'] == 'day')
                    {
                        $timecode['fa']['time'] = bcdiv(bcsub(bcdiv($hoursByDayTodo,2,4), bcdiv($diffC,3600,4),4),$this->getTimeHoursDayTodo($year, $month),4);

                    }
                    else
                    {
                        $timecode['fa']['time'] = bcsub(bcdiv($hoursByDayTodo,2,4), bcdiv($diffC,3600,4),4);
                    }
               }
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
        
        return  array('time'=>$diff, 'timecode'=>$timecode);
    }


    public function isTimeBetweenTwoBookingsOk($year, $month, $day)
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

            if(!$signFirstA && $data[$i]['inout'] == 'in' && $time >= $hoursBlockAfternoon1)
            {
                $signFirstA = $time;
            }
        }


        $passBack = bcdiv(5, 60, 4);
        if( bcdiv($signLastM,3600,4)-bcdiv($signFirstM,3600,4) > $passBack  || (!$signLastM || !$signFirstM) )
            if( bcdiv($signFirstA,3600,4)-bcdiv($signLastM,3600,4) > $passBack  || (!$signFirstA || !$signLastM)  )
                if( bcdiv($signLastA,3600,4)-bcdiv($signFirstA,3600,4) > $passBack || (!$signLastA || !$signFirstA) )
                    return true;

        return false;
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

            if(!$signFirstA && $data[$i]['inout'] == 'in' && $time >= $hoursBlockAfternoon1)
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
            $n = $this->isWorking($year, $month, $i);
            $nPeriod = $this->isWorkingPeriod($year, $month, $i);
            if($n)
            {
                $nwd = $this->getNonWorkingDay($year, $month, $i);
                $nwdPeriod = $this->getNonWorkingDayPeriod($year, $month, $i);
                if($nwd>0 && $nwd<1)
                {
                    if($nPeriod != $nwdPeriod)
                        $nDay += $nwd;
                }
                elseif($nwd==0)
                {
                    $nDay+=$n;
                }
            }
        }

        if($nDay>0)
        {
            return bcdiv($hoursByMonth, $nDay,4);
        }
        else
        {
            return 0;
        }
    }



    public function isWorking($year, $month, $day)
    {
        $wt = $this->getWorkingTime($year, $month);

        $dayName = strtolower(date("l",mktime(0,0,0,$month,$day,$year)));

        if($wt[$dayName.'Time_m'] > 0 || $wt[$dayName.'Time_a'] > 0)
        {
            if($wt[$dayName.'Time_m'] > 0 && $wt[$dayName.'Time_a'] > 0)
                return 1;
            return 0.5;

        }
        else
        {
            return false;
        }
       
    }

    public function isWorkingPeriod($year, $month, $day)
    {
        $wt = $this->getWorkingTime($year, $month);

        $dayName = strtolower(date("l",mktime(0,0,0,$month,$day,$year)));

        if($wt[$dayName.'Time_m'] > 0 || $wt[$dayName.'Time_a'] > 0)
        {
            if($wt[$dayName.'Time_m'] > 0 && $wt[$dayName.'Time_a'] > 0)
            {
                return 'allday';
            }
            elseif($wt[$dayName.'Time_m'] > 0)
            {
                return 'morning';
            }
            else
            {
                return 'afternoon';
            }

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
        $data = $query->readAll();

        $res = 0;
        foreach($data as $d)
        {
            if($d['period'] == 'allday')
                $res += 1;
            else
                $res += 0.5;
        }

        return $res;
    }

    public function getAbsenceMonth($year,$month)
    {
        $dateFrom = $year."-".$month."-1";
        $dateTo = $year."-".$month."-".date("t",mktime(0,0,0,$month,1,$year));

        $wt = $this->getWorkingTime($year, $month);

        $cmd=$this->db->createCommand("SELECT * FROM hr_timux_request AS r LEFT JOIN hr_timux_request_leave AS rl ON rl.request_id=r.id LEFT JOIN hr_timux_timecode AS t ON t.id=r.timecodeId WHERE rl.datefrom>='".$dateFrom."' AND rl.dateto<='".$dateTo."' AND ( r.state='validate' OR  r.state='closed') AND r.userId=".$this->employeeId." AND  t.type='absence'");

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

                if($this->isWorking($date[0], $date[1], $date[2]))
                {
                    $nwd = $this->getNonWorkingDay($date[0], $date[1], $date[2]);
                    $nwdPeriod = $this->getNonWorkingDayPeriod($date[0], $date[1], $date[2]);
                    $nPeriod = $this->isWorkingPeriod($date[0], $date[1], $date[2]);

                    if($nwdPeriod == 'morning' && $period == 'afternoon')
                    {
                        if($nPeriod != $nwdPeriod)
                            $nbreOfHours['nbre'] += 0.5;
                    }

                    if($nwdPeriod == 'afternoon' && $period == 'morning')
                    {
                        if($nPeriod != $nwdPeriod)
                            $nbreOfHours['nbre'] += 0.5;
                    }

                    if($nwdPeriod == 'afternoon' && $period == 'allday')
                    {
                        if($nPeriod != $nwdPeriod)
                            $nbreOfHours['nbre'] += 0.5;
                    }

                    if($nwdPeriod == 'morning' && $period == 'allday')
                    {
                        if($nPeriod != $nwdPeriod)
                            $nbreOfHours['nbre'] += 0.5;
                    }

                    if($nwd==0 && $period == 'morning')
                    {
                        if($nPeriod =='allday')
                            $nbreOfHours['nbre'] += 0.5;
                    }

                    if($nwd==0 && $period == 'afternoon')
                    {
                        if($nPeriod =='allday')
                            $nbreOfHours['nbre'] += 0.5;
                    }

                    if($nwd==0 && $period == 'allday')
                    {
                        if($nPeriod == $period)
                            $nbreOfHours['nbre'] += 1;
                        else
                            $nbreOfHours['nbre'] += 0.5;
                    }
                }

            }
            else
            {

                while($dateStart != $dateTo)
                {
                    $date = explode("-",$dateStart);

                    if($this->isWorking($date[0], $date[1], $date[2]))
                    {
                        $nwd = $this->getNonWorkingDay($date[0], $date[1], $date[2]);
                        $nwdPeriod = $this->getNonWorkingDayPeriod($date[0], $date[1], $date[2]);
                        $nPeriod = $this->isWorkingPeriod($date[0], $date[1], $date[2]);

                       if($nwdPeriod == 'morning' && $period == 'afternoon')
                       {
                            if($nPeriod != $nwdPeriod)
                                $nbreOfHours['nbre'] += 0.5;
                       }

                        if($nwdPeriod == 'afternoon' && $period == 'morning')
                        {
                            if($nPeriod != $nwdPeriod)
                                $nbreOfHours['nbre'] += 0.5;
                        }

                        if($nwdPeriod == 'afternoon' && $period == 'allday')
                        {
                            if($nPeriod != $nwdPeriod)
                                $nbreOfHours['nbre'] += 0.5;
                        }

                        if($nwdPeriod == 'morning' && $period == 'allday')
                        {
                            if($nPeriod != $nwdPeriod)
                                $nbreOfHours['nbre'] += 0.5;
                        }

                        if($nwd==0 && $period == 'morning')
                        {
                             if($nPeriod =='allday')
                               $nbreOfHours['nbre'] += 0.5;
                        }

                        if($nwd==0 && $period == 'afternoon')
                        {
                            if($nPeriod =='allday')
                                $nbreOfHours['nbre'] += 0.5;
                        }

                        if($nwd==0 && $period == 'allday')
                        {
                            if($nPeriod == $period)
                                $nbreOfHours['nbre'] += 1;
                            else
                                $nbreOfHours['nbre'] += 0.5;

                        }

                    }

                    $dateStart = date("Y-m-d",strtotime(date("Y-m-d", strtotime($dateStart)) . " +1 day"));

                }

                $date = explode("-",$dateTo);

                if($this->isWorking($date[0], $date[1], $date[2]))
                {
                   $nwd = $this->getNonWorkingDay($date[0], $date[1], $date[2]);
                   $nwdPeriod = $this->getNonWorkingDayPeriod($date[0], $date[1], $date[2]);
                   $nPeriod = $this->isWorkingPeriod($date[0], $date[1], $date[2]);

                   if($nwdPeriod == 'morning' && $period == 'afternoon')
                   {
                        if($nPeriod != $nwdPeriod)
                            $nbreOfHours['nbre'] += 0.5;
                   }

                   if($nwdPeriod == 'afternoon' && $period == 'morning')
                   {
                        if($nPeriod != $nwdPeriod)
                            $nbreOfHours['nbre'] += 0.5;
                   }

                   if($nwdPeriod == 'afternoon' && $period == 'allday')
                   {
                        if($nPeriod != $nwdPeriod)
                            $nbreOfHours['nbre'] += 0.5;
                   }

                   if($nwdPeriod == 'morning' && $period == 'allday')
                   {
                        if($nPeriod != $nwdPeriod)
                            $nbreOfHours['nbre'] += 0.5;
                   }

                   if($nwd==0 && $period == 'morning')
                   {
                        if($nPeriod =='allday')
                            $nbreOfHours['nbre'] += 0.5;
                   }

                   if($nwd==0 && $period == 'afternoon')
                   {
                        if($nPeriod =='allday')
                           $nbreOfHours['nbre'] += 0.5;
                   }

                   if($nwd==0 && $period == 'allday')
                   {
                        if($nPeriod == $period)
                            $nbreOfHours['nbre'] += 1;
                        else
                            $nbreOfHours['nbre'] += 0.5;
                   }
                }

            }

        }
        return $nbreOfHours['nbre'];


       /* $dateFrom = $year."-".$month."-1";
        $dateTo= $year."-".$month."-".date("t",mktime(0,0,0,$month,1,$year));

        $cmd = $this->db->createCommand( "SELECT l.period, l.datefrom, l.dateto FROM hr_timux_request AS r LEFT JOIN hr_timux_request_leave AS l ON l.request_id=r.id LEFT JOIN hr_timux_timecode  AS t ON t.id=r.timecodeId  WHERE t.type='absence' AND r.userId=".$this->employeeId." AND l.datefrom>='$dateFrom' AND l.dateto<='$dateTo' AND ( r.state='validate' OR r.state='closed' )" );
        $query = $cmd->query();
        $data = $query->readAll();

        $res = 0;
        foreach($data as $d)
        {
            if($d['period'] == 'allday')
                $res += 1;
            else
                $res += 0.5;
        }

        return $res;*/
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
        return $query->readAll();

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

        if($timeCode != "")
        {

            $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE year=$year AND month=$month AND timecode_id=$timeCode AND user_id=".$this->employeeId );
            $query = $cmd->query();
            $data = $query->read();

            return $data['nbre'];
        }
        else
            return 0;
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


    public function getLeaveRequest($year,$month)
    {
        $dateFrom = $year."-".$month."-1";
        $dateTo = $year."-".$month."-".date("t",mktime(0,0,0,$month,1,$year));

        $wt = $this->getWorkingTime($year, $month);

        $timecode = $this->getDefaultHolidaysCounter();

        if($timecode == "") return array();

        $cmd=$this->db->createCommand("SELECT * FROM hr_timux_request AS r LEFT JOIN hr_timux_request_leave AS rl ON rl.request_id=r.id LEFT JOIN hr_timux_timecode AS t ON t.id=r.timecodeId WHERE (t.type='leave' OR t.type='overtime' )  AND rl.datefrom>='".$dateFrom."' AND rl.dateto<='".$dateTo."' AND ( r.state='validate' OR  r.state='closed') AND r.userId=".$this->employeeId." AND t.id!=".$timecode);

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

                if($this->isWorking($date[0], $date[1], $date[2]))
                {
                   $nwdPeriod = $this->getNonWorkingDay($date[0], $date[1], $date[2]);

                   $nPeriod = $this->isWorkingPeriod($date[0], $date[1], $date[2]);

                   $m = 0.0;
                   $a = 0.0;

                   if($nPeriod == 'allday' && $period == 'allday' )
                   {
                       $m = 0.5;
                       $a = 0.5;
                   }

                   if($nPeriod == 'allday' && $period == 'morning' )
                   {
                       $m = 0.5;
                   }

                   if($nPeriod == 'allday' && $period == 'afternoon' )
                   {
                       $a = 0.5;
                   }

                   if($nPeriod == 'morning' && ($period == 'allday' || $period == 'morning'))
                   {
                       $m = 0.5;
                   }

                   if($nPeriod == 'afternoon' && ($period == 'allday' || $period == 'afternoon'))
                   {
                       $a = 0.5;
                   }

                   if( ($nwdPeriod == 'morning' || $nwdPeriod == 'allday' ) && $m>0)
                   {
                       $m = 0.0;
                   }

                   if( ($nwdPeriod == 'afternoon' || $nwdPeriod == 'allday' ) && $a>0)
                   {
                       $a = 0.0;
                   }

                   $nbreOfHours['nbre'] = bcadd($m,$a, 4);
                }

            }
            else
            {

                while($dateStart != $dateTo)
                {
                    $date = explode("-",$dateStart);
                    if($this->isWorking($date[0], $date[1], $date[2]))
                    {
                       $nwdPeriod = $this->getNonWorkingDay($date[0], $date[1], $date[2]);

                       $nPeriod = $this->isWorkingPeriod($date[0], $date[1], $date[2]);

                       $m = 0.0;
                       $a = 0.0;

                       if($nPeriod == 'allday' && $period == 'allday' )
                       {
                           $m = 0.5;
                           $a = 0.5;
                       }

                       if($nPeriod == 'allday' && $period == 'morning' )
                       {
                           $m = 0.5;
                       }

                       if($nPeriod == 'allday' && $period == 'afternoon' )
                       {
                           $a = 0.5;
                       }

                       if($nPeriod == 'morning' && ($period == 'allday' || $period == 'morning'))
                       {
                           $m = 0.5;
                       }

                       if($nPeriod == 'afternoon' && ($period == 'allday' || $period == 'afternoon'))
                       {
                           $a = 0.5;
                       }

                       if( ($nwdPeriod == 'morning' || $nwdPeriod == 'allday' ) && $m>0)
                       {
                           $m = 0.0;
                       }

                       if( ($nwdPeriod == 'afternoon' || $nwdPeriod == 'allday' ) && $a>0)
                       {
                           $a = 0.0;
                       }

                       $nbreOfHours['nbre'] += bcadd($m,$a, 4);
                    }

                    $dateStart = date("Y-m-d",strtotime(date("Y-m-d", strtotime($dateStart)) . " +1 day"));

                }

                $date = explode("-",$dateTo);

                if($this->isWorking($date[0], $date[1], $date[2]))
                {
                   $nwdPeriod = $this->getNonWorkingDay($date[0], $date[1], $date[2]);

                   $nPeriod = $this->isWorkingPeriod($date[0], $date[1], $date[2]);

                   $m = 0.0;
                   $a = 0.0;

                   if($nPeriod == 'allday' && $period == 'allday' )
                   {
                       $m = 0.5;
                       $a = 0.5;
                   }

                   if($nPeriod == 'allday' && $period == 'morning' )
                   {
                       $m = 0.5;
                   }

                   if($nPeriod == 'allday' && $period == 'afternoon' )
                   {
                       $a = 0.5;
                   }

                   if($nPeriod == 'morning' && ($period == 'allday' || $period == 'morning'))
                   {
                       $m = 0.5;
                   }

                   if($nPeriod == 'afternoon' && ($period == 'allday' || $period == 'afternoon'))
                   {
                       $a = 0.5;
                   }

                   if( ($nwdPeriod == 'morning' || $nwdPeriod == 'allday' ) && $m>0)
                   {
                       $m = 0.0;
                   }

                   if( ($nwdPeriod == 'afternoon' || $nwdPeriod == 'allday' ) && $a>0)
                   {
                       $a = 0.0;
                   }

                   $nbreOfHours['nbre'] += bcadd($m,$a, 4);

                }

            }

        }

        if($nbreOfHours['disp'] == 'hour')
        {
            $nbreOfHours['nbre'] = bcdiv($nbreOfHours['nbre'], $this->getTimeHoursDayTodo($year, $month), 4);
        }

        return $nbreOfHours;
    }

    public function getRequest($year,$month, $timecode)
    {
        if($timecode == NULL) return 0;

        $dateFrom = $year."-".$month."-1";
        $dateTo = $year."-".$month."-".date("t",mktime(0,0,0,$month,1,$year));

        $wt = $this->getWorkingTime($year, $month);

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

                if($this->isWorking($date[0], $date[1], $date[2]))
                {
                    $nwd = $this->getNonWorkingDay($date[0], $date[1], $date[2]);
                    $nwdPeriod = $this->getNonWorkingDayPeriod($date[0], $date[1], $date[2]);
                    $nPeriod = $this->isWorkingPeriod($date[0], $date[1], $date[2]);
                    
                    if($nwdPeriod == 'morning' && $period == 'afternoon')
                    {
                        if($nPeriod != $nwdPeriod)
                            $nbreOfHours['nbre'] += 0.5;
                    }

                    if($nwdPeriod == 'afternoon' && $period == 'morning')
                    {
                        if($nPeriod != $nwdPeriod)
                            $nbreOfHours['nbre'] += 0.5;
                    }

                    if($nwdPeriod == 'afternoon' && $period == 'allday')
                    {
                        if($nPeriod != $nwdPeriod)
                            $nbreOfHours['nbre'] += 0.5;
                    }

                    if($nwdPeriod == 'morning' && $period == 'allday')
                    {
                        if($nPeriod != $nwdPeriod)
                            $nbreOfHours['nbre'] += 0.5;
                    }

                    if($nwd==0 && $period == 'morning')
                    {
                        if($nPeriod =='allday')
                            $nbreOfHours['nbre'] += 0.5;
                    }

                    if($nwd==0 && $period == 'afternoon')
                    {
                        if($nPeriod =='allday')
                            $nbreOfHours['nbre'] += 0.5;
                    }

                    if($nwd==0 && $period == 'allday')
                    {
                        if($nPeriod == $period)
                            $nbreOfHours['nbre'] += 1;
                        else
                            $nbreOfHours['nbre'] += 0.5;
                    }
                }

            }
            else
            {

                while($dateStart != $dateTo)
                {
                    $date = explode("-",$dateStart);

                    if($this->isWorking($date[0], $date[1], $date[2]))
                    {
                        $nwd = $this->getNonWorkingDay($date[0], $date[1], $date[2]);
                        $nwdPeriod = $this->getNonWorkingDayPeriod($date[0], $date[1], $date[2]);
                        $nPeriod = $this->isWorkingPeriod($date[0], $date[1], $date[2]);

                       if($nwdPeriod == 'morning' && $period == 'afternoon')
                       {
                            if($nPeriod != $nwdPeriod)
                                $nbreOfHours['nbre'] += 0.5;
                       }

                        if($nwdPeriod == 'afternoon' && $period == 'morning')
                        {
                            if($nPeriod != $nwdPeriod)
                                $nbreOfHours['nbre'] += 0.5;
                        }

                        if($nwdPeriod == 'afternoon' && $period == 'allday')
                        {
                            if($nPeriod != $nwdPeriod)
                                $nbreOfHours['nbre'] += 0.5;
                        }

                        if($nwdPeriod == 'morning' && $period == 'allday')
                        {
                            if($nPeriod != $nwdPeriod)
                                $nbreOfHours['nbre'] += 0.5;
                        }

                        if($nwd==0 && $period == 'morning')
                        {
                             if($nPeriod =='allday')
                               $nbreOfHours['nbre'] += 0.5;
                        }

                        if($nwd==0 && $period == 'afternoon')
                        {
                            if($nPeriod =='allday')
                                $nbreOfHours['nbre'] += 0.5;
                        }

                        if($nwd==0 && $period == 'allday')
                        {
                            if($nPeriod == $period)
                                $nbreOfHours['nbre'] += 1;
                            else
                                $nbreOfHours['nbre'] += 0.5;

                        }

                    }

                    $dateStart = date("Y-m-d",strtotime(date("Y-m-d", strtotime($dateStart)) . " +1 day"));

                }

                $date = explode("-",$dateTo);

                if($this->isWorking($date[0], $date[1], $date[2]))
                {
                   $nwd = $this->getNonWorkingDay($date[0], $date[1], $date[2]);
                   $nwdPeriod = $this->getNonWorkingDayPeriod($date[0], $date[1], $date[2]);
                   $nPeriod = $this->isWorkingPeriod($date[0], $date[1], $date[2]);

                   if($nwdPeriod == 'morning' && $period == 'afternoon')
                   {
                        if($nPeriod != $nwdPeriod)
                            $nbreOfHours['nbre'] += 0.5;
                   }

                   if($nwdPeriod == 'afternoon' && $period == 'morning')
                   {
                        if($nPeriod != $nwdPeriod)
                            $nbreOfHours['nbre'] += 0.5;
                   }

                   if($nwdPeriod == 'afternoon' && $period == 'allday')
                   {
                        if($nPeriod != $nwdPeriod)
                            $nbreOfHours['nbre'] += 0.5;
                   }

                   if($nwdPeriod == 'morning' && $period == 'allday')
                   {
                        if($nPeriod != $nwdPeriod)
                            $nbreOfHours['nbre'] += 0.5;
                   }

                   if($nwd==0 && $period == 'morning')
                   {
                        if($nPeriod =='allday')
                            $nbreOfHours['nbre'] += 0.5;
                   }

                   if($nwd==0 && $period == 'afternoon')
                   {
                        if($nPeriod =='allday')
                           $nbreOfHours['nbre'] += 0.5;
                   }

                   if($nwd==0 && $period == 'allday')
                   {
                        if($nPeriod == $period)
                            $nbreOfHours['nbre'] += 1;
                        else
                            $nbreOfHours['nbre'] += 0.5;
                   }
                }

            }

        }

        if($nbreOfHours['disp'] == 'hour')
        {
            $nbreOfHours['nbre'] = bcdiv($nbreOfHours['nbre'], $this->getTimeHoursDayTodo($year, $month), 4);
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

        if(!$data)
        {
            $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE year=0 AND month=0 AND timecode_id=$timeCode AND user_id=".$this->employeeId );
            $query = $cmd->query();
            $data = $query->read();

        }

        return $data['nbre'];
    }

    public function geHolidaystForTheYear($year, $month)
    {
        $wt = $this->getWorkingTime($year, $month);

        if($wt)
            return $wt['holidaysByYear'];

        return 0;

    }

    public function geHolidaystMonth($year, $month)
    {
        $timeCode = $this->getDefaultHolidaysCounter();

        if($timeCode == "") return 0;

        if($year>=date('Y') && $month>=date('n') )
        {
            $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE year=0 AND month=0 AND timecode_id=$timeCode AND user_id=".$this->employeeId );
        }
        else
        {
            $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE year=$year AND month=$month AND timecode_id=$timeCode AND user_id=".$this->employeeId );

        }
        
        $query = $cmd->query();
        $data = $query->read();


        return $data['nbre'];
    }

    public function getComputeBookings($year, $month, $nDay)
    {
        $nbreOfDay = $nDay;

        $signed = 0.0;
        $due = 0.0;

        $todo = $this->getTimeHoursDayTodo($year, $month);
        $config = $this->getConfig();
        $holidays = 0.0;
        $timecode = array();

        for($i=1; $i<$nbreOfDay;$i++)
        {
            $date = $year."-".$month."-".$i;

            $line = array();

            // date of th day
            $line['date'] = $date;

            // bookings done in the day
            $booking = $this->getBookings('all',$date,$date,'ASC');
            $bookingIn = $this->getBookings(1,$date,$date,'ASC');
            $bookingOut = $this->getBookings(0,$date,$date,'ASC');

            $line['sign'] = '';

            $line['remark'] = '';

            //display the booking
            foreach($booking as $b)
            {
                if($b['internet'] == 1) $line['sign'].= "*";
                $inout = $b['inout'] == 'out' ? Prado::localize("out") : Prado::localize("in");
                $line['sign'] .= substr($b['time'],0,5)."/".$inout."&nbsp;&nbsp;&nbsp;";

                if(isset($b['timeworked']))
                {
                    if($line['remark'] != '') $line['remark'].="<br>";
                    if($b['inout'] == 'out')
                    {
                        $line['remark'].= '&larr; ';
                        $line['remark'].= $b['time']." ".$b['timecode'];
                    }
                    else
                    {
                        $line['remark'].= '&rarr; ';
                        $line['remark'].= $b['time']." ".$b['timecode'];

                    }
                }
            }

            // check the missing booking
            if(count($booking) % 2 > 0)
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                 $line['remark'] .= "<span style=\"color:red\">".Prado::localize('Signing missing')."</span>";
            }

            // check the error booking
            if(count($bookingIn) != count($bookingOut))
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                 $line['remark'] .= "<span style=\"color:red\">".Prado::localize('Signing error')."</span>";
            }

            // check the break
            if(!$this->isBreakOk($year, $month, $i) && count($booking) % 2 == 0)
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                 $line['remark'] .= "<span style=\"color:red\">".$config['minimumBreaks']." ".Prado::localize('min. for the break are required')."</span>";
            }

            // check the time between two booking

            if(!$this->isTimeBetweenTwoBookingsOk($year, $month, $i) && count($booking) % 2 == 0)
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                 $line['remark'] .= "<span style=\"color:red\">".Prado::localize('A time between two bookings is too small')."</span>";

            }

            // get the time signed by the employee
            $timeWorked = $this->getTimeWorked($year, $month, $i);
            $line['done'] = $timeWorked['time'] > 0 ? sprintf("%.02f",$timeWorked['time']) : '';


            // get the non working day
            $nwd = $this->getNonWorkingDay($year, $month, $i);
            $nwdPeriod = $this->getNonWorkingDayPeriod($year, $month, $i);

            // get the absences
            $a = $this->getAbsence($year, $month, $i);
            $aPeriod = $this->getAbsencePeriod($year, $month, $i);

            // check if the employee is working this day
            $isWorking = $this->isWorking($year, $month, $i);

            // compute the hours that the employee must work according to the n.w.d. and the absence
            if($isWorking)
            {
                // what is the time period for the day
                $todoPeriod = $this->isWorkingPeriod($year, $month, $i);

                // do we have absence or n.w.d.
                if($nwd == 0 && $a == 0)
                {
                    // if the time period is all the day, the employ should work all the day
                    if($todoPeriod == 'allday')
                    {
                        $line['todo'] = $todo > 0 ? sprintf("%.02f",$todo) : '';
                        $signed = bcadd($timeWorked['time'],$signed,4);
                    }
                    else // the employee must work a half a day
                    {
                        $line['todo'] = bcmul($todo,0.5,4) > 0 ? sprintf("%.02f",bcmul($todo,0.5,4)) : '';
                        $signed = bcadd($timeWorked['time'],$signed,4);
                    }
                }
                else // we have n.w.d. or absence
                {

                    $todoMorning = 0.0;
                    $todoAfternoon = 0.0;

                    // compute the nbre of hours todo for the morning
                    if($todoPeriod == 'morning' || $todoPeriod == 'allday')
                    {
                       $todoMorning = bcmul($todo,0.5,4);
                    }

                    // compute the nbre of hours todo for the morning
                    if($todoPeriod == 'afternoon' || $todoPeriod == 'allday')
                    {
                       $todoAfternoon = bcmul($todo,0.5,4);
                    }

                    // recompute according to the non working day
                    if($nwdPeriod == 'morning' || $nwdPeriod == 'allday' )
                    {
                        $todoMorning = 0.0;
                    }

                    // recompute according to the non working day
                    if($nwdPeriod == 'afternoon' || $nwdPeriod == 'allday' )
                    {
                        $todoAfternoon = 0.0;
                    }

                    $line['todo'] = bcadd($todoMorning,$todoAfternoon,4) > 0 ? sprintf("%.02f",bcadd($todoMorning,$todoAfternoon,4)) : '';


                    // compute the time done according to the absence
                    $doneMorning = 0.0;
                    $doneAfternoon = 0.0;

                    foreach($aPeriod as $a)
                    {
                        // if the time day worked should be greater than 0 hours
                        if(bcadd($todoMorning,$todoAfternoon,4) > 0)
                        {
                            if(($a['period'] == 'morning' || $a['period'] == 'allday') && ($todoPeriod == 'morning' || $todoPeriod == 'allday') )
                            {
                                $doneMorning = bcmul($todo,0.5,4);
                            }

                            if(($a['period'] == 'afternoon' || $a['period'] == 'allday')  && ($todoPeriod == 'afternoon' || $todoPeriod == 'allday') )
                            {
                                $doneAfternoon = bcmul($todo,0.5,4);
                            }

                            if($nwdPeriod == 'morning' || $nwdPeriod == 'allday')
                            {
                                $doneMorning = 0.0;
                            }

                            if($nwdPeriod == 'afternoon' || $nwdPeriod == 'allday' )
                            {
                                $doneAfternoon = 0.0;
                            }

                        }
                    }

                    $tD = $doneMorning;
                    $tD = bcadd($tD,$doneAfternoon,4);
                    $tD = bcadd($tD,$timeWorked['time'],4);
                    if($timeWorked['time']>$todo)
                    {
                        if($line['remark'] != '') $line['remark'].="<br>";
                         $line['remark'] .= "<span style=\"color:red\">".Prado::localize('Time code error')."</span>";
                    }


                    $line['done'] = $tD > 0 ? sprintf("%.02f",$tD) : '';

                    $signed = bcadd($tD,$signed,4);
                }
            }
            else
            {
                $line['todo'] = '';

                if($line['done'] != '')
                    $signed = bcadd($line['done'],$signed,4);
            }

            // compute the overtime
            $overtime = bcsub($line['done'],$line['todo'], 4);
            $line['overtime'] = $overtime > 0 || $overtime < 0 ? sprintf("%.02f",$overtime) : '';


            // add remarks for the n.w.d.
            if($nwdPeriod === 'allday')
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                    $line['remark'] .= Prado::localize('Non working day');
            }
            elseif($nwdPeriod === 'morning')
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                $line['remark'] .= Prado::localize('Non working day at the morning');
            }
            elseif($nwdPeriod === 'afternoon')
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                $line['remark'] .= Prado::localize('Non working day at the afternoon');
            }


            // add remarks for the absence
            foreach($aPeriod as $a)
            {
                // if the absence is an allday absence and if the employee should works
                if($a['period'] === 'allday' && $isWorking && $line['todo'] != '')
                {
                    // add in the remark the time code
                    if($line['remark'] != '') $line['remark'].="<br>";
                    $tc = $this->getTimeCode($date);
                    $line['remark'] .= $tc['name'];

                    // add the time code in list used to diplay the time code list, the default
                    // holidays time code is omitted
                    if($tc['timecodeId'] != $this->getDefaultHolidaysCounter())
                    {
                        if($tc['formatDisplay'] == 'day')
                        {
                            // check according to the n.w.d.
                            if($nwdPeriod !== false && ( $nwdPeriod === 'morning' || $nwdPeriod === 'afternoon'))
                            {
                               $timecode[$tc['name']]['value'] += 0.5;
                               $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                            }
                            elseif($nwdPeriod === false )
                            {
                               $timecode[$tc['name']]['value'] += 1;
                               $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                            }

                        }
                        else
                        {
                            // check according to the n.w.d.
                            if($nwdPeriod !== false && ( $nwdPeriod === 'morning' || $nwdPeriod === 'afternoon'))
                            {
                                $timecode[$tc['name']]['value'] += bcmul($todo,0.5,4);
                                $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                            }
                            elseif($nwdPeriod === false )
                            {
                                $timecode[$tc['name']]['value'] += $todo;
                                $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                            }
                        }
                    }
                }
                elseif($a['period'] === 'morning' && $isWorking && $line['todo'] != '')
                {
                    if($line['remark'] != '') $line['remark'].="<br>";
                    $tc = $this->getTimeCode($date,'morning');
                    $line['remark'] .= $tc['name'].' / '.Prado::localize('morning');

                    if($tc['timecodeId'] != $this->getDefaultHolidaysCounter())
                    {
                        if($tc['formatDisplay'] == 'day')
                        {
                            // check according to the n.w.d.
                            if($nwdPeriod === false )
                            {
                                $timecode[$tc['name']]['value'] += 0.5;
                                $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                            }
                        }
                        else
                        {
                            // check according to the n.w.d.
                            if($nwdPeriod === false )
                            {
                                $timecode[$tc['name']]['value'] += bcmul($todo,0.5,4);;
                                $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                            }
                        }
                    }
                }
                elseif($a['period'] === 'afternoon' && $isWorking && $line['todo'] != '')
                {
                    if($line['remark'] != '') $line['remark'].="<br>";
                    $tc = $this->getTimeCode($date,'afternoon');
                    $line['remark'] .= $tc['name'].' / '.Prado::localize('afternoon');

                    if($tc['timecodeId'] != $this->getDefaultHolidaysCounter())
                    {
                        if($tc['formatDisplay'] == 'day')
                        {
                            // check according to the n.w.d.
                            if($nwdPeriod === false )
                            {
                                $timecode[$tc['name']]['value'] += 0.5;
                                $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                            }
                        }
                        else
                        {
                            // check according to the n.w.d.
                            if($nwdPeriod === false )
                            {
                                $timecode[$tc['name']]['value'] += bcmul($todo,0.5,4);
                                $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                            }
                        }
                    }
                }
            }

            // add the compensation of the abcences in the time code list
            foreach($timeWorked['timecode'] as $tw)
            {
                $timecode[$tw['name']]['value'] += $tw['time'];
                $timecode[$tw['name']]['formatDisplay'] = $tw['formatDisplay'];
            }

            $res[] = $line;
        }

        return $res;
    }

    public function getOvertimeMonth($year, $month)
    {
        $timeCode = $this->getDefaultOvertimeCounter();

        if($timeCode == "") return 0;

        if($year>=date('Y') && $month>=date('n') )
        {
            $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE year=0 AND month=0 AND timecode_id=$timeCode AND user_id=".$this->employeeId );
            $query = $cmd->query();
            $data = $query->read();
        }
        else
        {
            $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE year=$year AND month=$month AND timecode_id=$timeCode AND user_id=".$this->employeeId );
            $query = $cmd->query();
            $data = $query->read();
            if($data['nbre']==0)
            {
                $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE year=0 AND month=0 AND timecode_id=$timeCode AND user_id=".$this->employeeId );
                $query = $cmd->query();
                $data = $query->read();
            }
        }



        return $data['nbre'];
    }

    public function getMonthTimeWorked($year, $month)
    {

        $cmd = $this->db->createCommand( "SELECT hours, absences FROM hr_timux_closed_month  WHERE year=$year AND month=$month AND user_id=".$this->employeeId );
        $query = $cmd->query();
        $data = $query->read();

        if($data)
        {
            return array('done'=>$data['hours'], 'absence'=>$data['absences']);
        }


        $nbreOfDay = date("t",mktime(0,0,0,$month,1,$year));

        $todoMonth = $this->getHoursMonth($year, $month);

        $timeWorked = 0.0;
        $absence = 0.0;

        for($i=1; $i<=$nbreOfDay;$i++)
        {
            $done = $this->getTimeWorked($year, $month, $i, false);

            foreach($done['timecode'] as $tc)
            {
                if($tc['formatDisplay'] == 'day')
                {
                    $absence = bcadd($absence,bcmul($tc['time'],$this->getTimeHoursDayTodo($year, $month),4),4);
                }
                else
                {
                    $absence = bcadd($absence,$tc['time'],4);
                }
            }

            if($this->isWorking($year, $month, $i))
            {
                $nwd = $this->getNonWorkingDay($year, $month, $i);
                $h = $this->getAbsence($year, $month, $i);

                if($nwd == 0 && $h == 0)
                {
                    $timeWorked = bcadd($done['time'],$timeWorked,4);
                }
                else
                {
                    $tNwd = bcmul($todo,$nwd,4);
                    if(round($tNwd,2)==0)
                        $tNwd = 0.0;

                    $todoMinNwd = bcsub($todo,$tNwd,4);

                    $line['todo'] = $todoMinNwd > 0 ? sprintf("%.02f",$todoMinNwd) : '';

                    $tH = 0;
                    $tH = bcmul($todo,$h,4);
                    if(round($tH,2)==0)
                        $tH = 0.0;

                    if($tNwd<$tH)
                        $tH = bcsub($tH,$tNwd,4);
                    $tH = bcadd($tH,$done['time'],4);

                    $timeWorked = bcadd($tH,$timeWorked,4);
                }
            }
            else
            {
                $timeWorked = bcadd($done['time'],$timeWorked,4);
            }
            
        }
        return array('done'=>$timeWorked, 'absence'=>$absence);
    }


    public function getEmployeeLeaveRequest($state)
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


    public function closeMonth($year, $month)
    {
        // check if the month is already closed
        $cmd=$this->db->createCommand("SELECT * FROM hr_timux_closed_month WHERE user_id=".$this->employeeId." AND year=$year AND month=$month");
        $data = $cmd->query();
        $data = $data->readAll();

        $error = $this->getError($year, $month);

        $timeCodeOvId = $this->getDefaultOvertimeCounter();

        if(count($data)==0 && count($error) == 0)
        {
            $cmd=$this->db->createCommand("SELECT * FROM hr_timux_timecode WHERE (type='overtime' OR type='leave') AND id!=$timeCodeOvId");
            $timecodes = $cmd->query();
            $timecodes = $timecodes->readAll();

            foreach($timecodes as $tc)
            {
                $request = $this->getRequest($year, $month, $tc['id']);
                if($request === 0)
                {
                    $request = array();
                    $request['nbre'] = 0;
                }

                if($tc['formatDisplay'] == 'hour')
                {
                    $request['nbre'] = bcmul($request['nbre'], $this->getTimeHoursDayTodo($year,$month),4);
                }

                if($tc['type'] == 'leave')
                {
                    $this->updateCounter($tc['id'],$request['nbre'],$year, $month,"-");
                }
                
                if($tc['type'] == 'overtime')
                {                    
                    $this->updateCounter($tc['id'],$request['nbre'],$year, $month,"-");
                }
            }
            
            //update the counter according to overtime
            $nbre = 0.0;

            // time worked + absence
            
            $nbreOfHours = $this->getMonthTimeWorked($year, $month);
            $nbre = bcadd($nbre, $nbreOfHours['done'] ,4);
            $nbre = bcadd($nbre, $nbreOfHours['absence'] ,4);

            //holidays
            $defaultHolidayTimeCode = $this->getDefaultHolidaysCounter();
            $holidays = $this->getRequest($year,$month,$defaultHolidayTimeCode);
            $nbre = bcadd($nbre, bcmul($holidays['nbre'], $this->getTimeHoursDayTodo($year,$month),4) ,4);

            // leave
            $leaveRequest = $this->getLeaveRequest($year, $month);
            $nbre = bcadd($nbre, bcmul($leaveRequest['nbre'], $this->getTimeHoursDayTodo($year,$month),4) ,4);

            $absence = $this->getAbsenceMonth($year, $month);
            $absence = bcmul($absence, $this->getTimeHoursDayTodo($year,$month),4);
            $nbre = bcadd($nbre, $absence ,4);

            $hoursMonth = $this->getHoursMonth($year, $month);
            $diff = bcsub($nbre,$hoursMonth, 4);

            $this->updateCounter($timeCodeOvId,$diff,$year, $month,"+");

            // add the vacation day to the counter
            if($month == 12)
            {
                $wt = $this->getWorkingTime($year, $month);
                $n = $wt['holidaysByYear'];
                $timeCodeId = $this->getDefaultHolidaysCounter();
                $cmd=$this->db->createCommand("UPDATE hr_timux_activity_counter SET nbre=nbre+$n WHERE year=0 AND month=0 AND user_id=".$this->employeeId." AND timecode_id=".$timeCodeId);
                $cmd->execute();

            }

            // set the month as closed
            $cmd = $this->db->createCommand( "INSERT INTO hr_timux_closed_month SET user_id=".$this->employeeId." , year=$year, month=$month, hours={$nbreOfHours['done']}, absences={$nbreOfHours['absence']}, hoursByMonth=$hoursMonth" );
            $query = $cmd->execute();

            // set the validate request to closed
            $this->closeRequest($year,$month);

            // close the booking
            $this->closeBooking($year, $month);

            // send email to the employee
            $this->sendEmailMonthReport($year, $month);

            $sa = new TStandAlone();
            $sa->addStandalone("add", $this->employeeId, 'timuxAddBalances');
        }
    }


    public function getMonthReportData($year, $month)
    {

        $nbreOfDay = date("t",mktime(0,0,0,$month,1,$year));

        $signed = 0.0;
        $due = 0.0;

        $todo = $this->getTimeHoursDayTodo($year, $month);
        $config = $this->getConfig();
        $holidays = 0.0;
        $timecode = array();

        for($i=1; $i<=$nbreOfDay;$i++)
        {
            $date = $year."-".$month."-".$i;

            $line = array();

            // date of th day
            $line['date'] = $date;

            // booking done in the day
            $booking = $this->getBookings('all',$date,$date,'ASC');
            $bookingIn = $this->getBookings(1,$date,$date,'ASC');
            $bookingOut = $this->getBookings(0,$date,$date,'ASC');

            $line['sign'] = '';

            $line['remark'] = '';

            foreach($booking as $b)
            {
                if($b['internet'] == 1) $line['sign'].= "*";
                $inout = $b['inout'] == 'out' ? Prado::localize("out") : Prado::localize("in");
                $line['sign'] .= substr($b['time'],0,5)."/".$inout."&nbsp;&nbsp;&nbsp;";

                if(isset($b['timeworked']))
                {
                    if($line['remark'] != '') $line['remark'].="<br>";
                    if($b['inout'] == 'out')
                    {
                        $line['remark'].= '&larr; ';
                        $line['remark'].= $b['time']." ".$b['timecode'];
                    }
                    else
                    {
                        $line['remark'].= '&rarr; ';
                        $line['remark'].= $b['time']." ".$b['timecode'];

                    }
                }
            }

            // check the missing booking
            if(count($booking) % 2 > 0)
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                 $line['remark'] .= "<span style=\"color:red\">".Prado::localize('Signing missing')."</span>";
            }

            // check the error booking
            if(count($bookingIn) != count($bookingOut))
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                 $line['remark'] .= "<span style=\"color:red\">".Prado::localize('Signing error')."</span>";
            }

            // check the break
            if(!$this->isBreakOk($year, $month, $i) && count($booking) % 2 == 0)
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                 $line['remark'] .= "<span style=\"color:red\">".$config['minimumBreaks']." ".Prado::localize('min. for the break are required')."</span>";
            }

            // get the time signed by the employee
            $timeWorked = $this->getTimeWorked($year, $month, $i);
            $line['done'] = $timeWorked['time'] > 0 ? sprintf("%.02f",$timeWorked['time']) : '';


            // get the non working day
            $nwd = $this->getNonWorkingDay($year, $month, $i);
            $nwdPeriod = $this->getNonWorkingDayPeriod($year, $month, $i);

            // get the absences
            $a = $this->getAbsence($year, $month, $i);
            $aPeriod = $this->getAbsencePeriod($year, $month, $i);

            // check if the employee is working this day
            $isWorking = $this->isWorking($year, $month, $i);
            
            // compute the hours that the employee must work according to the n.w.d. and the absence
            if($isWorking)
            {
                // what is the time period for the day
                $todoPeriod = $this->isWorkingPeriod($year, $month, $i);

                // do we have absence or n.w.d.
                if($nwd == 0 && $a == 0)
                {
                    // if the time period is all the day, the employ should work all the day
                    if($todoPeriod == 'allday')
                    {
                        $line['todo'] = $todo > 0 ? sprintf("%.02f",$todo) : '';
                        $signed = bcadd($timeWorked['time'],$signed,4);
                    }
                    else // the employee must work a half a day
                    {
                        $line['todo'] = bcmul($todo,0.5,4) > 0 ? sprintf("%.02f",bcmul($todo,0.5,4)) : '';
                        $signed = bcadd($timeWorked['time'],$signed,4);
                    }
                }
                else // we have n.w.d. or absence
                {

                    $todoMorning = 0.0;
                    $todoAfternoon = 0.0;

                    // compute the nbre of hours todo for the morning
                    if($todoPeriod == 'morning' || $todoPeriod == 'allday')
                    {
                       $todoMorning = bcmul($todo,0.5,4);
                    }

                    // compute the nbre of hours todo for the morning
                    if($todoPeriod == 'afternoon' || $todoPeriod == 'allday')
                    {
                       $todoAfternoon = bcmul($todo,0.5,4);
                    }

                    // recompute according to the non working day
                    if($nwdPeriod == 'morning' || $nwdPeriod == 'allday' )
                    {
                        $todoMorning = 0.0;
                    }

                    // recompute according to the non working day
                    if($nwdPeriod == 'afternoon' || $nwdPeriod == 'allday' )
                    {
                        $todoAfternoon = 0.0;
                    }

                    $line['todo'] = bcadd($todoMorning,$todoAfternoon,4) > 0 ? sprintf("%.02f",bcadd($todoMorning,$todoAfternoon,4)) : '';


                    // compute the time done according to the absence
                    $doneMorning = 0.0;
                    $doneAfternoon = 0.0;

                    foreach($aPeriod as $a)
                    {
                        // if the time day worked should be greater than 0 hours
                        if(bcadd($todoMorning,$todoAfternoon,4) > 0)
                        {
                            if(($a['period'] == 'morning' || $a['period'] == 'allday') && ($todoPeriod == 'morning' || $todoPeriod == 'allday') )
                            {
                                $doneMorning = bcmul($todo,0.5,4);
                            }

                            if(($a['period'] == 'afternoon' || $a['period'] == 'allday')  && ($todoPeriod == 'afternoon' || $todoPeriod == 'allday') )
                            {
                                $doneAfternoon = bcmul($todo,0.5,4);
                            }

                            if($nwdPeriod == 'morning' || $nwdPeriod == 'allday')
                            {
                                $doneMorning = 0.0;
                            }

                            if($nwdPeriod == 'afternoon' || $nwdPeriod == 'allday' )
                            {
                                $doneAfternoon = 0.0;
                            }

                        }
                    }

                    $tD = $doneMorning;
                    $tD = bcadd($tD,$doneAfternoon,4);
                    $tD = bcadd($tD,$timeWorked['time'],4);
                    if($timeWorked['time']>$todo)
                    {
                        if($line['remark'] != '') $line['remark'].="<br>";
                         $line['remark'] .= "<span style=\"color:red\">".Prado::localize('Time code error')."</span>";
                    }


                    $line['done'] = $tD > 0 ? sprintf("%.02f",$tD) : '';

                    $signed = bcadd($tD,$signed,4);
                }
            }
            else
            {
                $line['todo'] = '';

                if($line['done'] != '')
                    $signed = bcadd($line['done'],$signed,4);
            }

            // compute the overtime
            $overtime = bcsub($line['done'],$line['todo'], 4);
            $line['overtime'] = $overtime > 0 || $overtime < 0 ? sprintf("%.02f",$overtime) : '';


            // add remarks for the n.w.d.
            if($nwdPeriod === 'allday')
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                    $line['remark'] .= Prado::localize('Non working day');
            }
            elseif($nwdPeriod === 'morning')
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                $line['remark'] .= Prado::localize('Non working day at the morning');
            }
            elseif($nwdPeriod === 'afternoon')
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                $line['remark'] .= Prado::localize('Non working day at the afternoon');
            }


            // add remarks for the absence
            foreach($aPeriod as $a)
            {
                // if the absence is an allday absence and if the employee should works
                if($a['period'] === 'allday' && $isWorking && $line['todo'] != '')
                {
                    // add in the remark the time code
                    if($line['remark'] != '') $line['remark'].="<br>";
                    $tc = $this->getTimeCode($date);
                    $line['remark'] .= $tc['name'];

                    // add the time code in list used to diplay the time code list, the default
                    // holidays time code is omitted
                    if($tc['timecodeId'] != $this->getDefaultHolidaysCounter())
                    {
                        if($tc['formatDisplay'] == 'day')
                        {
                            // check according to the n.w.d.
                            if($nwdPeriod !== false && ( $nwdPeriod === 'morning' || $nwdPeriod === 'afternoon'))
                            {
                               $timecode[$tc['name']]['value'] += 0.5;
                               $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                            }
                            elseif($nwdPeriod === false )
                            {
                               $timecode[$tc['name']]['value'] += 1;
                               $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                            }

                        }
                        else
                        {
                            // check according to the n.w.d.
                            if($nwdPeriod !== false && ( $nwdPeriod === 'morning' || $nwdPeriod === 'afternoon'))
                            {
                                $timecode[$tc['name']]['value'] += bcmul($todo,0.5,4);
                                $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                            }
                            elseif($nwdPeriod === false )
                            {
                                $timecode[$tc['name']]['value'] += $todo;
                                $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                            }
                        }
                    }
                }
                elseif($a['period'] === 'morning' && $isWorking && $line['todo'] != '')
                {
                    if($line['remark'] != '') $line['remark'].="<br>";
                    $tc = $this->getTimeCode($date,'morning');
                    $line['remark'] .= $tc['name'].' / '.Prado::localize('morning');

                    if($tc['timecodeId'] != $this->getDefaultHolidaysCounter())
                    {
                        if($tc['formatDisplay'] == 'day')
                        {
                            // check according to the n.w.d.
                            if($nwdPeriod === false )
                            {
                                $timecode[$tc['name']]['value'] += 0.5;
                                $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                            }
                        }
                        else
                        {
                            // check according to the n.w.d.
                            if($nwdPeriod === false )
                            {
                                $timecode[$tc['name']]['value'] += bcmul($todo,0.5,4);;
                                $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                            }
                        }
                    }
                }
                elseif($a['period'] === 'afternoon' && $isWorking && $line['todo'] != '')
                {
                    if($line['remark'] != '') $line['remark'].="<br>";
                    $tc = $this->getTimeCode($date,'afternoon');
                    $line['remark'] .= $tc['name'].' / '.Prado::localize('afternoon');

                    if($tc['timecodeId'] != $this->getDefaultHolidaysCounter())
                    {
                        if($tc['formatDisplay'] == 'day')
                        {
                            // check according to the n.w.d.
                            if($nwdPeriod === false )
                            {
                                $timecode[$tc['name']]['value'] += 0.5;
                                $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                            }
                        }
                        else
                        {
                            // check according to the n.w.d.
                            if($nwdPeriod === false )
                            {
                                $timecode[$tc['name']]['value'] += bcmul($todo,0.5,4);
                                $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                            }
                        }
                    }
                }
            }

            // add the compensation of the abcences in the time code list
            foreach($timeWorked['timecode'] as $tw)
            {
                $timecode[$tw['name']]['value'] += $tw['time'];
                $timecode[$tw['name']]['formatDisplay'] = $tw['formatDisplay'];
            }

            $res[] = $line;
        }
        
        $this->signedValue = $signed;


        $this->timcode = $timecode;

        return $res;
    }

    public function sendEmailMonthReport($year, $month)
    {
        include("PrintList.php");
        $app = Prado::getApplication()->getGlobalization();

        $cmd = $this->db->createCommand( "SELECT * FROM hr_site WHERE id=1" );
        $query = $cmd->query();
        $data = $query->read();

        $this->pdf = new PrintListPDF();
        $this->pdf->userName = Prado::getApplication()->getUser()->getName();
        $this->pdf->siteName = utf8_decode($data['name']);
        $this->pdf->SetFont('Arial','',10);

        $this->pdf->AddPage();


        $data = $this->getMonthReportData($year, $month);

        $this->pdf->SetFont('Arial','',9);
        $this->pdf->Cell(0,10,utf8_decode(Prado::localize('Sign in/out')),0,0,'L');
        $this->pdf->Ln(10);
        //$this->pdf->setDefaultFont();

        $this->pdf->Cell(30,5,utf8_decode(Prado::localize('Employee'))." :",0,0,'L');
        $this->pdf->Cell(0,5,utf8_decode($this->getFullName()),0,1,'L');

        $this->pdf->Cell(30,5,utf8_decode(Prado::localize('Department'))." :",0,0,'L');
        $this->pdf->Cell(0,5,utf8_decode($this->getDepartment()),0,1,'L');

        $date = new DateFormat($app->getCulture());
        $date = $date->format('1-'.$month."-".$year, "P");
        $date = explode(" ", $date);
        $date = $date[2]." ".$date[3];

        $this->pdf->Cell(30, 5,utf8_decode(Prado::localize('Month'))." :",0,0,'L');
        $this->pdf->Cell(0,5,utf8_decode($date),0,1,'L');

        $this->pdf->Ln(10);


        $header = array(utf8_decode(Prado::localize("Date")),
            utf8_decode(Prado::localize("Signing")),
            utf8_decode(Prado::localize("To do")),
            utf8_decode(Prado::localize("Done")),
            utf8_decode(substr(Prado::localize("Overtime"),0,8)),
            utf8_decode(Prado::localize("Remark")),
        );

        $this->pdf->SetFillColor(124,124,124);
        $this->pdf->SetTextColor(255);
        $this->pdf->SetDrawColor(255);
        $this->pdf->SetLineWidth(.3);
        $this->pdf->SetFont('','B');
        $w=array(20,65,15,15,15,65);
        for($i=0;$i<count($header);$i++)
        $this->pdf->Cell($w[$i],7,$header[$i],1,0,'C',1);
        $this->pdf->Ln();
        $this->pdf->SetFillColor(215,215,215);
        $this->pdf->SetTextColor(0);
        $this->pdf->SetFont('');

        $fill=false;

        $this->pdf->SetFont('courier','',8);

        foreach($data as $d)
        {
            $date = new DateFormat($app->getCulture());
            $date = $date->format($d['date'], "P");

            $date = explode(" ", $date);
            $date[0] = strtoupper(substr($date[0], 0,2));
            $date = implode(" ", $date);

            $date= utf8_decode($date);
            $sign = str_replace("&nbsp;"," ",utf8_decode($d['sign']));
            $todo = utf8_decode($d['todo']);
            $done= utf8_decode($d['done']);
            $overtime = utf8_decode($d['overtime']);
            $remark = utf8_decode($d['remark']);
            $remark = str_replace("&rarr;","->",$remark);
            $remark = str_replace("&larr;","<-",$remark);

            $nBr = $this->findall("<br>", $remark);

            $remark = str_replace("<br>","\n",$remark);
            $remark = str_replace("<span style=\"color:red\">","",$remark);
            $remark = str_replace("</span>","",$remark);

            $height = 5;
            if($nBr !== false)
            {
                $height = 7 * count($nBr);
            }

            $this->pdf->CellExt($w[0],$height,$date,'LR',0,'L',$fill);
            $this->pdf->CellExt($w[1],$height,$sign,'LR',0,'L',$fill);
            $this->pdf->CellExt($w[2],$height,$todo,'LR',0,'L',$fill);
            $this->pdf->CellExt($w[3],$height,$done,'LR',0,'L',$fill);
            $this->pdf->CellExt($w[4],$height,$overtime,'LR',0,'L',$fill);
            $this->pdf->CellExt($w[5],$height,$remark,'LR',0,'L',$fill);
            $this->pdf->Ln();
            $fill=!$fill;
        }

        $this->pdf->SetFont('Arial','',9);
        $this->pdf->SetDrawColor(0);
        $this->pdf->SetLineWidth(.1);

        $this->pdf->Ln(7);

// ligne 1

        if($this->signedValue>0)
        {
            $this->pdf->Cell(30,3,utf8_decode(Prado::localize('Signed'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("+ %.02f",$this->signedValue),0,0,'R');
        }
        elseif($this->signedValue<0 || $this->signedValue==0)
        {
            $this->pdf->Cell(30,3,utf8_decode(Prado::localize('Signed'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf(" %.02f",$this->signedValue),0,0,'R');
        }

        $this->pdf->Cell(20,3,"",0,0,'R');

        $y = $this->Request['f3'];
        $m = $this->Request['f4'];
        if($m == 1)
        {
            $y--;
            $m = 12;
        }
        else
        {
            $m--;
        }

        $wt = $this->getWorkingTime($y, $m);

        if(!$wt)
        {
            $totalYearHours100 = 0.0;
            $totalYearHoursX = $totalYearHoursX2 = 0.0;
            $vacations = 0.0;
            for($i=1; $i<=12; $i++)
            {
                $p = $this->getPercentage($year,$i);
                $h = $this->getHoursMonth($year, $i);
                $totalYearHours100 = bcadd($h, $totalYearHours100,2);
                $h = bcdiv(bcmul($h,$p,2),100.00,2);
                $totalYearHoursX = bcadd($h, $totalYearHoursX,2);
                $totalYearHoursX2 = $totalYearHoursX;
                $wt = $this->getWorkingTime($year, $i);

                $vByMonth = bcdiv($wt['holidaysByYear'],12,4);
                $vByMonth = bcdiv(bcmul($vByMonth,$p,4),100.00,4);

                $vacations += $vByMonth;


            }

            $holidaysLastMonth = $vacations;
        }
        else
        {
            $holidaysLastMonth = $this->geHolidaystLastMonth( $year,  $month);
        }

        if($holidaysLastMonth>0)
        {
            $this->pdf->Cell(40,3,utf8_decode(Prado::localize('Holidays last month'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("+ %.02f",$holidaysLastMonth),0,1,'R');
        }
        elseif($holidaysLastMonth<0 || $holidaysLastMonth==0)
        {
            $this->pdf->Cell(40,3,utf8_decode(Prado::localize('Holidays last month'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf(" %.02f",$holidaysLastMonth),0,1,'R');
        }

// ligne 2

        $hoursForTheMonthAtX = $this->getHoursMonth($year,  $month);
        if($hoursForTheMonthAtX>0)
        {
            $this->pdf->Cell(30,3,utf8_decode(Prado::localize('Hours due'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("+ %.02f",$hoursForTheMonthAtX),0,0,'R');
        }
        elseif($hoursForTheMonthAtX<0 || $hoursForTheMonthAtX==0)
        {
            $this->pdf->Cell(30,3,utf8_decode(Prado::localize('Hours due'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf(" %.02f",$hoursForTheMonthAtX),0,0,'R');
        }

        $this->pdf->Cell(20,3,"",0,0,'R');

        $defaultHolidayTimeCode = $this->getDefaultHolidaysCounter();

        $holidays = $this->getRequest($year,$month,$defaultHolidayTimeCode);

        if($holidays['nbre']>0)
        {
            $this->pdf->Cell(40,3,utf8_decode(Prado::localize('Holidays for this month'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("- %.02f",$holidays['nbre']),0,1,'R');
        }
        elseif($holidays['nbre']==0)
        {
            $this->pdf->Cell(40,3,utf8_decode(Prado::localize('Holidays for this month'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("%.02f",$holidays['nbre']),0,1,'R');
        }


// ligne 3

        $balanceForTheMonth = bcsub($this->signedValue,$hoursForTheMonthAtX,4);

        if($balanceForTheMonth>0)
        {
            $this->pdf->Cell(30,3,utf8_decode(str_replace("<br/>"," ",Prado::localize('Balance for the month')))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("+ %.02f",$balanceForTheMonth),'T',0,'R');
        }
        elseif($balanceForTheMonth<0 || $balanceForTheMonth==0)
        {
            $this->pdf->Cell(30,3,utf8_decode(str_replace("<br/>"," ",Prado::localize('Balance for the month')))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf(" %.02f",$balanceForTheMonth),'T',0,'R');
        }

        $this->pdf->Cell(20,3,"",0,0,'R');

        $holidaysTotal = bcsub($holidaysLastMonth, $holidays['nbre'],4);
        if($holidaysTotal>0)
        {
            $this->pdf->Cell(40,3,utf8_decode(Prado::localize('Total'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("+ %.02f",$holidaysTotal),'T',1,'R');
        }
        elseif($holidaysTotal<0 || $holidaysTotal==0)
        {
            $this->pdf->Cell(40,3,utf8_decode(Prado::localize('Total'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("%.02f",$holidaysTotal),'T',1,'R');
        }


// ligne 4
        $lastOvertime = $this->getOvertimeLastMonth($year,  $month);

        if($lastOvertime>0)
        {
            $this->pdf->Cell(30,3,utf8_decode(Prado::localize('Last month'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("+%.02f",$lastOvertime),0,0,'R');
        }
        elseif($lastOvertime<0 || $lastOvertime==0)
        {
            $this->pdf->Cell(30,3,utf8_decode(Prado::localize('Last month'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf(" %.02f",$lastOvertime),0,1,'R');
        }

        $this->pdf->Cell(20,3,"",0,0,'R');

        $this->pdf->ln(3);

// ligne 5
        $balances = bcadd($balanceForTheMonth , $lastOvertime, 4);

        if($balances>0)
        {
            $this->pdf->Cell(30,3,utf8_decode(Prado::localize('Balances'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("+%.02f",$balances),'T',0,'R');
        }
        elseif($balances<0 || $balances==0)
        {
            $this->pdf->Cell(30,3,utf8_decode(Prado::localize('Balances'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf(" %.02f",$balances),'T',0,'R');
        }

        $this->pdf->Cell(20,3,"",0,0,'R');

        $nonWorkingDay = $this->getAllNonWorkingDay($year, $month);
        if($nonWorkingDay>0)
        {
            $this->pdf->Cell(40,3,utf8_decode(Prado::localize('Non working days'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf(" %.02f",$nonWorkingDay),0,1,'R');
        }
        elseif($nonWorkingDay==0)
        {
            $this->pdf->Cell(40,3,utf8_decode(Prado::localize('Non working days'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf(" %.02f",$nonWorkingDay),0,1,'R');
        }

        $this->pdf->ln(7);


        foreach($this->timcode as $k=>$v)
        {
            $disp = $v['formatDisplay'] == 'day' ? Prado::localize('days') : Prado::localize('hours');
            $this->pdf->Cell(30,3,utf8_decode($k)." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("%.02f $disp",$v['value']),0,1,'R');

        }

        $sql = "SELECT picturepath FROM hr_config WHERE id=1";
        $cmd=$this->db->createCommand($sql);
        $data = $cmd->query();
        $data = $data->read();

        $timuxreportpath = "";
        if($data['picturepath'] != "")
        {
            $timuxreportpath = '.'.DIRECTORY_SEPARATOR.'timuxreport'.DIRECTORY_SEPARATOR.$data['picturepath'].DIRECTORY_SEPARATOR;
        }
        else
        {
            $timuxreportpath = '.'.DIRECTORY_SEPARATOR.'timuxreport'.DIRECTORY_SEPARATOR;
        }

        $emp = md5($this->getFullName());


        $this->pdf->Output($timuxreportpath.$emp.'_timuxreport'.$month.$year.'.pdf','F');

        chmod($timuxreportpath.$emp.'_timuxreport'.$month.$year.'.pdf', 755);

        $mailer = new TMailer();

        $mailer->addAttachment($timuxreportpath.$emp.'_timuxreport'.$month.$year.'.pdf', 'timuxreport'.$month.$year.'.pdf', 'application/pdf');

        $cmd2=$this->db->createCommand("SELECT u.email1, u.email2, su.email AS email3 FROM hr_user AS u LEFT JOIN hr_superusers AS su ON su.user_id=u.id WHERE u.id=:id");
        $cmd2->bindParameter(":id",$this->employeeId);
        $query = $cmd2->query();
        $data2 = $query->read();

        if($data2['email1'] != '' || $data2['email2'] != '' || $data2['email3'] != '')
        {
            if($data2['email2'] != '')
            {
                $mailer->addRecipient($data2['email2']);
            }
            elseif($data2['email3'] != '')
            {
                $mailer->addRecipient($data2['email3']);
            }
            elseif($data2['email1'] != '')
            {
                $mailer->addRecipient($data2['email1']);
            }

        }


        $mailer->setObject(Prado::localize("Timux month report"));

        $body = Prado::localize("{name}<br/><br>Please find your month report<br/><br/>Timux",array('name'=>$this->getFullName()));
        $mailer->setBody($body);
        $mailer->sendHtmlMail();
        
    }

    protected function findall($needle, $haystack)
    {
        //Setting up
        $buffer=''; //We will use a 'frameshift' buffer for this search
        $pos=0; //Pointer
        $end = strlen($haystack); //The end of the string
        $getchar=''; //The next character in the string
        $needlelen=strlen($needle); //The length of the needle to find (speeds up searching)
        $found = array(); //The array we will store results in

        while($pos<$end)//Scan file
        {
            $getchar = substr($haystack,$pos,1); //Grab next character from pointer
            if($getchar!="\n" || buffer<$needlelen) //If we fetched a line break, or the buffer is still smaller than the needle, ignore and grab next character
            {
                $buffer = $buffer . $getchar; //Build frameshift buffer
                if(strlen($buffer)>$needlelen) //If the buffer is longer than the needle
                {
                    $buffer = substr($buffer,-$needlelen);//Truncunate backwards to needle length (backwards so that the frame 'moves')
                }
                if($buffer==$needle) //If the buffer matches the needle
                {
                    $found[]=$pos-$needlelen+1; //Add the location of the needle to the array. Adding one fixes the offset.
                }
            }
            $pos++; //Increment the pointer
        }
        if(array_key_exists(0,$found)) //Check for an empty array
        {
            return $found; //Return the array of located positions
        }
        else
        {
            return false; //Or if no instances were found return false
        }
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

    public function closeBooking($year, $month)
    {
        $dateFrom = $year."-".$month."-1";
        $dateTo = $year."-".$month."-".date("t",mktime(0,0,0,$month,1,$year));

        $cmd=$this->db->createCommand("UPDATE hr_timux_booking SET closed='1' WHERE tracking_id IN (SELECT id FROM hr_tracking AS t WHERE t.date>='$dateFrom' AND t.date<='$dateTo' AND id_user=".$this->employeeId." )");
        $cmd->execute();
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
