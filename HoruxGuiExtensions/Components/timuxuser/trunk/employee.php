<?php


Prado::using('System.I18N.core.DateFormat');

define("MON", 1);
define("TUE", 2);
define("WED", 3);
define("THU", 4);
define("FRI", 5);
define("SAT", 6);
define("SUN", 7);

class employee
{
    // for the db connexion
    protected $db = NULL;

    // id of the employee
    protected $employeeId = NULL;

    protected $cache = array();
    protected $pradoCache;

    function __construct($userId) {

        $this->db = Prado::getApplication()->getModule('horuxDb')->DbConnection;
        $this->db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);

        $this->db->Active=true;

        $this->employeeId = $userId;

        $this->pradoCache = Prado::getApplication()->getCache();
    }

    protected function getCache($key) {

        if($this->pradoCache)
            return $this->pradoCache->get($this->employeeId."_".$key);
        else
            return $this->cache[$this->employeeId."_".$key];
    }

    protected function setCache($key, $value) {

        if($this->pradoCache) {
            $param = Prado::getApplication()->getParameters();

            $cacheTimeout = $param['cacheTimeout'];

            $this->pradoCache->set($this->employeeId."_".$key, $value, $cacheTimeout);
        } else {
            $this->cache[$this->employeeId."_".$key] = $value;
        }
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
        $cmd->bindValue(":id",$this->employeeId,PDO::PARAM_STR);
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
        $cmd->bindValue(":id",$this->employeeId,PDO::PARAM_STR);
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
        $cmd->bindValue(":id",$this->employeeId,PDO::PARAM_STR);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            return $data['fullname'];
        }

        return "";
    }

    /*
     * Get the role of the employee. Return employee|manager|rh
     */
    public function getRole()
    {
        $cmd = $this->db->createCommand( "SELECT role FROM hr_timux_workingtime WHERE user_id=:id ORDER BY id DESC LIMIT 0,1" );
        $cmd->bindValue(":id",$this->employeeId,PDO::PARAM_STR);
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
        $cmd->bindValue(":id",$this->employeeId,PDO::PARAM_STR);
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
        $cmd->bindValue(":id",$this->employeeId,PDO::PARAM_STR);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            return $data['name'];
        }

        return "";
    }


    /*
     * Get the timux config
     */
    public function getConfig()
    {
        $cache = $this->getCache('getConfig');
        if($cache) {
            return $cache;
        }

        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_config" );
        $query = $cmd->query();

        $this->setCache('getConfig',$query->read());

        return $this->getCache('getConfig');
    }


    /**
     * Return the working time according to the day, month and year
     * 
     * @param int $day
     * @param int $month
     * @param int $year
     * @return mixed
     */
    public function getWorkingTime($day, $month, $year)
    { 
        $date = $year."-".sprintf("%02d",$month)."-".$day;
        $cache = $this->getCache('getWorkingTime_'.$date);
        if($cache) {
            return $cache;
        }

        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_workingtime WHERE user_id=".$this->employeeId." AND startDate<='".$date."' AND ( endDate>='".$date."' || endDate IS NULL ) ORDER BY startDate DESC" );
        $query = $cmd->query();


        if($query) {
             $this->setCache('getWorkingTime_'.$date, $query->read());
             return $this->getCache('getWorkingTime_'.$date);
        }
        else {
            $this->setCache('getWorkingTime_'.$date, false);
            return false;
        }

    }


    /**
     *  Return the bookings done for a specific day
     *
     * @param int $day
     * @param int $month
     * @param int $year
     */
    public function getBookingsDay($day, $month, $year) {

        $dateCache = $year."-".sprintf("%02d",$month)."-".$day;
        $cache = $this->getCache('getBookingsDay_'.$dateCache);
        if($cache) {
            return $cache;
        }

        $sql = "SELECT t.id, tb.roundBooking, tb.internet, tb.closed, tb.action, tb.actionReason FROM
                    hr_tracking AS t
                LEFT JOIN
                    hr_timux_booking AS tb
                ON
                    tb.tracking_id = t.id
                WHERE
                    `date`=:date AND id_user=:id_user ORDER by tb.roundBooking ASC, tb.action ASC ";

        $cmd = $this->db->createCommand( $sql );
        $cmd->bindValue(":id_user",$this->employeeId,PDO::PARAM_STR);
        $cmd->bindValue(":date",$year."-".$month."-".$day,PDO::PARAM_STR);

        $query = $cmd->query();
        if($query)
        {
            $this->setCache('getBookingsDay_'.$dateCache,$query->readAll());
            return $this->getCache('getBookingsDay_'.$dateCache);
        }

        $this->setCache('getBookingsDay_'.$dateCache,array());
        return array();
    }

    /**
     *
     * @param <type> $status
     * @param <type> $status
     * @param <type> $from
     * @param <type> $until 
     */
    public function getBookings($status, $from, $until, $order='DESC') {

        $dateCache = $status.$from.$until.$order;
        $cache = $this->getCache('getBookings_'.$dateCache);
        if($cache) {
            return $cache;
        }

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

        $cmd=$this->db->createCommand("SELECT  t.id, t.date, tb.roundBooking AS time, tb.action, tb.actionReason, tb.internet FROM hr_tracking AS t LEFT JOIN hr_timux_booking AS tb ON tb.tracking_id=t.id WHERE $date $status t.id_user=".$this->employeeId." AND tb.action!='NULL' ORDER BY t.date $order, t.time $order, tb.action $order  LIMIT 0,500");
        $data = $cmd->query();

        $this->setCache('getBookings_'.$dateCache,$data->readAll());
        return $this->getCache('getBookings_'.$dateCache);

    }

    /**
     * Determin if the booking is a in or out
     * @param array $booking
     */
    public function isBookingIn($booking) {
        //255 => in
        //254 => out

        if($booking['action'] == 255)
            return true;
        if($booking['action'] == 254)
            return false;

        if($booking['action'] == 100 && substr($booking['actionReason'],-2,2) == 'IN')
            return true;

        // the last is in every case an out booking
        return false;
    }

    /**
     *  get if the booking is a special time code
     * @param array $booking
     */
    public function isSpecialTimeCode($booking) {
        return $booking['action'] == 100;
    }

    /**
     *  get if the booking is a special time code with compensation
     * @param array $booking
     */
    public function isSpecialTimeCodeWithCompensation($booking) {
        if($this->isSpecialTimeCode($booking)) {
            $timecodeId = explode("_", $booking['actionReason']);
            $timecodeId = $timecodeId[0];
            $cmd = $this->db->createCommand( "SELECT timeworked FROM hr_timux_timecode WHERE id=:id" );
            $cmd->bindValue(":id", $timecodeId);
            $query = $cmd->query();
            if($query) {
                $row = $query->read();
                return $row['timeworked'];
            }
            return false;
        }

        return false;

    }

    public function getBookingTimeCode($booking) {

        if($this->isSpecialTimeCode($booking)) {
            $timecodeId = explode("_", $booking['actionReason']);
            $timecodeId = $timecodeId[0];
            $cmd = $this->db->createCommand( "SELECT name FROM hr_timux_timecode WHERE id=:id" );
            $cmd->bindValue(":id", $timecodeId);
            $query = $cmd->query();
            if($query) {
                $row = $query->read();
                return $row['name'];
            }
            return "";
        }

        return "";
    }

    /*
     * Get the occupancy of the employee
     */
    public function getPercentage($day, $month, $year)
    {
        $wt = $this->getWorkingTime($day, $month, $year);

        if($wt)
            return $wt['workingPercent'];
        else
            return 0;
    }


    /**
     *  Return the time that the employee should done for a specific day
     *
     * @param int $day
     * @param int $month
     * @param int $year
     */
    public function getDayTodo($day, $month, $year) {

        $dateCache = $year."-".sprintf("%02d",$month)."-".$day;
        $cache = $this->getCache('getDayTodo_'.$dateCache);
        if($cache) {
            return $cache;
        }

        if($this->isNonWorkingDay($day,$month, $year)) {
            $this->setCache('getDayTodo_'.$dateCache, 0);
            return 0;
        }

        //check if the day is in its contract
        $wt = $this->getWorkingTime($day, $month, $year);

        $config = $this->getConfig();

        $workingDay = explode(",", $config['workingDays']);

        if($wt) {


            if($wt['calendarType'] == 1) {
                $nbreOfDayInMonth = date("t",mktime(0,0,0,$month,1, $year));
                $nbreOfDayInMonthToWorks = 0;
                $nbreRealWorks = 0;
                for($d=1; $d<=$nbreOfDayInMonth; $d++) {

                    $nday = date("N",mktime(0,0,0,$month,$d, $year));

                    if( $workingDay[$nday-1])
                        $nbreOfDayInMonthToWorks++;

                    
                    switch($nday) {
                        case MON:
                            $dayName = 'mondayTime_';
                            break;
                        case TUE:
                            $dayName = 'tuesdayTime_';
                            break;
                        case WED:
                            $dayName = 'wednesdayTime_';
                            break;
                        case THU:
                            $dayName = 'thursdayTime_';
                            break;
                        case FRI:
                            $dayName = 'fridayTime_';
                            break;
                        case SAT:
                            $dayName = 'saturdayTime_';
                            break;
                        case SUN:
                            $dayName = 'sundayTime_';
                            break;
                    }

                    if($wt[$dayName.'m']>0)
                        $nbreRealWorks += 0.5;
                    if($wt[$dayName.'a']>0)
                        $nbreRealWorks += 0.5;
                }

                //by 1/2 day
                $nbreRealWorks *=2;

                $nbreOfDayInMonthToWorks = bcmul(bcdiv($wt['hoursByWeek'], 5, 4), $nbreOfDayInMonthToWorks, 4);

                if($wt['workingPercent'] != 100) {
                     $nbreOfDayInMonthToWorks = bcmul($nbreOfDayInMonthToWorks, bcdiv($wt['workingPercent'],100,4),4);
                }

                if($nbreRealWorks > 0)
                    $timeByHalfDay = bcdiv($nbreOfDayInMonthToWorks,$nbreRealWorks,4 );
                else
                    $timeByHalfDay = 0;


                $nday = date("N",mktime(0,0,0,$month,$day, $year));
                $dayName = '';
                switch($nday) {
                    case MON:
                        $dayName = 'mondayTime_';
                        break;
                    case TUE:
                        $dayName = 'tuesdayTime_';
                        break;
                    case WED:
                        $dayName = 'wednesdayTime_';
                        break;
                    case THU:
                        $dayName = 'thursdayTime_';
                        break;
                    case FRI:
                        $dayName = 'fridayTime_';
                        break;
                    case SAT:
                        $dayName = 'saturdayTime_';
                        break;
                    case SUN:
                        $dayName = 'sundayTime_';
                        break;
                }

                if($wt[$dayName.'m']>0)
                    $timeByDay = bcadd($timeByDay,$timeByHalfDay,4);
                if($wt[$dayName.'a']>0)
                    $timeByDay = bcadd($timeByDay,$timeByHalfDay,4);

                

                $this->setCache('getDayTodo_'.$dateCache, $timeByDay);
                return $timeByDay;
            } 
            
            if($wt['calendarType'] == 2) {
                $nday = date("N",mktime(0,0,0,$month,$day, $year));

                $nbreRealWorks = 0;


                $dayName = '';
                switch($nday) {
                    case MON:
                        $dayName = 'mondayTime_';
                        break;
                    case TUE:
                        $dayName = 'tuesdayTime_';
                        break;
                    case WED:
                        $dayName = 'wednesdayTime_';
                        break;
                    case THU:
                        $dayName = 'thursdayTime_';
                        break;
                    case FRI:
                        $dayName = 'fridayTime_';
                        break;
                    case SAT:
                        $dayName = 'saturdayTime_';
                        break;
                    case SUN:
                        $dayName = 'sundayTime_';
                        break;
                }

                if($wt[$dayName.'m']>0)
                    $nbreRealWorks += $wt[$dayName.'m'];
                if($wt[$dayName.'a']>0)
                    $nbreRealWorks += $wt[$dayName.'a'];

               
                $this->setCache('getDayTodo_'.$dateCache, $nbreRealWorks);
                return $nbreRealWorks;
            }
        } else {
            $this->setCache('getDayTodo_'.$dateCache, 0);
            return 0;
        }
    }

    /**
     * Return the request for a day
     * @param int $day
     * @param int $month
     * @param int $year 
     */
    public function getDayRequest($day, $month, $year) {

        $dateCache = $year."-".sprintf("%02d",$month)."-".$day;
        $cache = $this->getCache('getDayRequest_'.$dateCache);
        if($cache) {
            return $cache;
        }  

        $date = $year."-".$month."-".$day;
        $cmd = $this->db->createCommand( "SELECT * FROM
                                            hr_timux_request AS tr
                                          LEFT JOIN
                                            hr_timux_request_leave AS trl
                                          ON
                                            trl.request_id = tr.id
                                          LEFT JOIN
                                            hr_timux_timecode AS tt
                                          ON
                                            tt.id = tr.timecodeid
                                          WHERE
                                            (tr.state='validate' OR tr.state='closed')
                                          AND
                                            tr.userId=:id
                                          AND
                                            trl.datefrom<=:date
                                          AND
                                            trl.dateto>=:date
                                            "
                                       );
        $cmd->bindValue(":id", $this->employeeId );
        $cmd->bindValue(":date", $date );
        $query = $cmd->query();
        if($query) {
            $row = $query->readAll();
            $this->setCache('getDayRequest_'.$dateCache,$row);
            return $row;
        }

        $this->setCache('getDayRequest_'.$dateCache,false);
        return false;

    }

    /*
     * Return the time done in a day
     * @param int $day
     * @param int month
     * @param int year
     * @return return the time
     */
    public function getDayDone($day, $month, $year) {

        $dateCache = $year."-".sprintf("%02d",$month)."-".$day;
        $cache = $this->getCache('getDayDone_'.$dateCache);
        if($cache) {
            return $cache;
        }

        $res = array();
        $res['done'] = 0;
        $res['compensation'] = 0;
        $res['timecodeId'] = 0;
        $res['timecodeName'] = '';
        $SpecialTimeCodeWithCompensation = false;

        $bookings  = $this->getBookingsDay($day, $month, $year);

        $break = bcmul($this->isBreakOk($bookings), 60, 4);

        $nBooking = count($bookings);
        if($nBooking>=2  ) {

            $nextBookingType = 'IN';
            $bookinIN = 0;

            for($i=0; $i<$nBooking; $i++) {
                $type = $this->isBookingIn($bookings[$i]) ? 'IN' : 'OUT';

                // do we need to do a compensation
                if( $this->isSpecialTimeCodeWithCompensation($bookings[$i]) ) {
                        $SpecialTimeCodeWithCompensation = true;
                        $res['timecodeId'] = $bookings[$i]['actionReason'];
                        $res['timecodeName'] = $this->getTimeCodeName($bookings[$i]['actionReason']);
                }

                // if the type is equal, use it for the computation
                if($type == $nextBookingType) {
                    if($type == 'IN') {

                        $bookinIN = $bookings[$i]['roundBooking'];

                    } else {

                        $res['done'] += (strtotime($bookings[$i]['roundBooking']) - strtotime($bookinIN));
                        $bookinIN = 0;
                        
                    }

                }

                if(strtotime($bookinIN) > 0 || $nextBookingType == 'OUT') {
                    $nextBookingType = $nextBookingType == 'IN' ? 'OUT' : 'IN';
                }
            }
        }


        //check if the day is a absent request
        $requests = $this->getDayRequest($day, $month, $year);

        $addRequest = 0;
        if($requests ) {
            foreach($requests as $request) {
                if(($request['type'] == 'leave') || $request['timeworked']) {
                    if($request['period'] == 'allday') {
                        if(bcdiv($res['done'], 3600, 4) > $this->getDayTodo($day, $month, $year)) {
                            $res['done'] = bcdiv($res['done'], 3600, 4);
                            $this->setCache('getDayDone_'.$dateCache, $res);
                            return $res;
                        } else {
                            $res['done'] = bcadd($this->getDayTodo($day, $month, $year),bcdiv($res['done'], 3600, 4),4) ;
                            $this->setCache('getDayDone_'.$dateCache, $res);
                            return $res;
                        }
                    } else {

                        $addRequest = bcadd($addRequest, bcmul($this->getDayTodo($day, $month, $year),0.5,4),4);

                    }
                }
            }

            $res['done'] = bcadd ( $res['done'], bcmul($addRequest, 3600, 4), 4);

            /*if( bcdiv($res['done'],3600,4) > $this->getDayTodo($day, $month, $year)) {
                $res['done'] = $this->getDayTodo($day, $month, $year);
                return $res;
            }*/
        }


        if($SpecialTimeCodeWithCompensation && bcdiv($res['done'], 3600, 4) < $this->getDayTodo($day, $month, $year)) {
            $res['compensation'] = $this->getDayTodo($day, $month, $year) - bcdiv($res['done'], 3600, 4);
            $res['done'] = $this->getDayTodo($day, $month, $year);
            $this->setCache('getDayDone_'.$dateCache, $res);
            return $res;
        }


        $res['done'] = bcsub($res['done'], $break, 4);

        $res['done'] = bcdiv($res['done'], 3600, 4);

        $this->setCache('getDayDone_'.$dateCache, $res);
        return $res;
    }

    /**
     *  Return the number of working day in a specific month
     * @param int $month
     * @param int $year
     */
    public function getNumberOfWorkingDayInMonth($month, $year) {

        $dateCache = $year."-".sprintf("%02d",$month);
        $cache = $this->getCache('getNumberOfWorkingDayInMonth_'.$dateCache);
        if($cache) {
            return $cache;
        }

        $res = 0;

        $nbreOfDay = date("t",mktime(0,0,0,$month,1,$year));

        for($day=1; $day<=$nbreOfDay; $day++) {
            $nday = date("N",mktime(0,0,0,$month,$day, $year));

            if($nday >= MON && $nday <= FRI) {
                $res++;
            }            
        }

        $this->setCache('getNumberOfWorkingDayInMonth_'.$dateCache,$res);
        return $res;

    }

    /**
     * Check if the day is a non working day
     * @param int $day
     * @param int $month
     * @param int $year
     * @return boolean
     */
    public function isNonWorkingDay($day, $month, $year)
    {
        $dateCache = $year."-".sprintf("%02d",$month)."-".$day;
        $cache = $this->getCache('isNonWorkingDay_'.$dateCache);
        if($cache) {
            return $cache;
        }

        $date = $year."-".$month."-".$day;

        $cmd = $this->db->createCommand( "SELECT period FROM hr_non_working_day AS n WHERE n.from<='$date' AND n.until>='$date'" );
        $query = $cmd->query();

        if($query->read())
        {
            $this->setCache('isNonWorkingDay_'.$dateCache, true);
            return true;
        }

        $this->setCache('isNonWorkingDay_'.$dateCache, false);
        return false;
    }

    /**
     * Check is the minumum break is ok
     * @param array $bookings 
     */
    public function isBreakOk($bookings) {


        $nbreOfBooking = count($bookings);

        if($nbreOfBooking == 0)
            return false;

        $config = $this->getConfig();

        if($config['minimumBreaks']== 0)
            return false;


        if($nbreOfBooking % 2 != 0)
            return false;

        $bIn = 0;
        $bOut = 0;
        $nextB = 'IN';
        foreach($bookings as $b) {
            if($this->isBookingIn($b)) {
                $bIn++;
                $type = 'IN';
            }
            else {
                $bOut++;
                $type = 'OUT';
            }


            if($type != $nextB) {
                return false;
            }

            $nextB = $nextB == 'IN' ? 'OUT' : 'IN';

        }

        if($bIn != $bOut)
           return false;

        $nextB = 'IN';
        $timeIn = 0;
        foreach($bookings as $b) {

            if($this->isBookingIn($b)) {
              $timeIn = $b['roundBooking'];
            } else {
                $diff = strtotime($b['roundBooking'])-strtotime($timeIn);

                // swiss laws, 5.5 hours need 15 minutes break
                if( bcdiv($diff,3600,4) > 5.5 && bcdiv($diff,3600,4) < 7 ) {
                    if($nbreOfBooking == 2) return 15;
                }

                // swiss laws, 7 hours need 30 minutes break
                if( bcdiv($diff,3600,4) > 7  && bcdiv($diff,3600,4) < 9 ) {
                    if($nbreOfBooking == 2) return 30;
                }

                // swiss laws, 9 hours need 60 minutes break
                if( bcdiv($diff,3600,4) > 9 ) {
                    if($nbreOfBooking == 2) return 60;
                }

            }


            $nextB = $nextB == 'IN' ? 'OUT' : 'IN';
        }

        return false;
    }

    /**
     *  Return the overtime of the last year
     *  @param int $LastYear
     */
    public function getOvertimeLastYear($lastYear) {

        $dateCache = $lastYear;
        $cache = $this->getCache('getOvertimeLastYear_'.$dateCache);
        if($cache) {
            return $cache;
        }   

        $timeCode = $this->getDefaultOvertimeCounter();
        
        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE isClosedMonth=1 AND year=$lastYear AND month=12 AND timecode_id=$timeCode AND user_id=".$this->employeeId );
        $query = $cmd->query();
        $data = $query->read();

        $this->setCache('getOvertimeLastYear_'.$dateCache,$data['nbre']);
        return $data['nbre'];
    }

    /**
     *  Return the overtime of the last month
     *  @param int $month
     *  @param int $year
     */
    public function getOvertimeLastMonth($currentMonth, $currentYear, $lastOvertime = 0) {

        $date = $currentYear."-".sprintf("%02d",$currentMonth).$lastOvertime;

        $cache = $this->getCache('getOvertimeLastMonth_'.$date);
        if($cache) {
            return $cache;
        }

        if($currentMonth == 1) {
            $lastMonth = 12;
            $lastYear = $currentYear - 1;
        }
        else {
            $lastMonth = $currentMonth - 1;
            $lastYear = $currentYear;
        }

        $wt = $this->getWorkingTime(1,$currentMonth, $currentYear);

        if(!$wt) {
            $this->setCache('getOvertimeLastMonth_'.$date,$lastOvertime);
            return $lastOvertime;
        }

        $timeCode = $this->getDefaultOvertimeCounter();

        // check on the activity counter if we find the overtime
        if($timeCode)
        {

            $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE isClosedMonth=1 AND year=$lastYear AND month=$lastMonth AND timecode_id=$timeCode AND user_id=".$this->employeeId );
            $query = $cmd->query();
            $data = $query->read();

            if($data) {
                $this->setCache('getOvertimeLastMonth_'.$date,bcadd($lastOvertime, $data['nbre'], 4));

                return $this->getCache('getOvertimeLastMonth_'.$date);
            }
            else {

                $nbreOfDay = date("t",mktime(0,0,0,$lastMonth,1,$lastYear));
                $overtime = 0;
                for($day=1; $day<=$nbreOfDay;$day++) {
                    $todo = $this->getDayTodo($day, $lastMonth, $lastYear);
                    $done = $this->getDayDone($day, $lastMonth, $lastYear);
                    $overtime += bcsub($done['done'],$todo,4);
                }

                $lastOvertime = bcadd($lastOvertime, $overtime, 4);

                $lastOvertime = bcadd($lastOvertime, $this->getActivityCounter($lastYear, $lastMonth, $timeCode), 4);

                $this->setCache('getOvertimeLastMonth_'.$date,$this->getOvertimeLastMonth($lastMonth, $lastYear, $lastOvertime));
                return $this->getCache('getOvertimeLastMonth_'.$date);
            }
        }
        $this->setCache('getOvertimeLastMonth_'.$date,0);
        return 0;
    }

    public function getActivityCounter($year, $month, $timecode) {

        $date = $year."-".sprintf("%02d",$month)."-".$timecode;

        $cache = $this->getCache('getActivityCounter_'.$date);
        if($cache) {
            return $cache;
        }

        $cmd = $this->db->createCommand( "SELECT ROUND(SUM(nbre), 4) AS n FROM hr_timux_activity_counter WHERE isClosedMonth=0 AND 	timecode_id=$timecode AND year=$year AND month=$month AND user_id={$this->employeeId}") ;
        $query = $cmd->query();
        $data = $query->read();

        $this->setCache('getActivityCounter_'.$date,$data['n']);

        return $data['n'];
    }


    public function getAllActivityCounter($year, $month) {

        $date = $year."-".sprintf("%02d",$month);

        $cache = $this->getCache('getAllActivityCounter_'.$date);
        if($cache) {
            return $cache;
        }

        $cmd = $this->db->createCommand( "SELECT ac.*, tc.formatDisplay, tc.name FROM hr_timux_activity_counter AS ac LEFT JOIN hr_timux_timecode AS tc ON tc.id=ac.timecode_id WHERE ac.isClosedMonth=0 AND year=$year AND month=$month AND user_id={$this->employeeId}") ;
        $query = $cmd->query();

        $this->setCache('getAllActivityCounter_'.$date,$query->readAll());

        return  $this->getCache('getAllActivityCounter_'.$date);
    }

    /**
     *  return the default counter for the overtime
     * @return int
     */
    public function getDefaultOvertimeCounter()
    {
        $cache = $this->getCache('getDefaultOvertimeCounter_');
        if($cache) {
            return $cache;
        }

        $cmd = $this->db->createCommand( "SELECT id FROM hr_timux_timecode WHERE defaultOvertime=1" );
        $query = $cmd->query();
        $data = $query->read();
        if($data) {
            $this->setCache('getDefaultOvertimeCounter_', $data['id']);
            return $data['id'];
        }

        $this->setCache('getDefaultOvertimeCounter_', false);
        return false;

    }

    /**
     *  return the default counter for the holidays
     * @return int
     */
    public function getDefaultHolidaysCounter()
    {
         $cache = $this->getCache('getDefaultHolidaysCounter_');
        if($cache) {
            return $cache;
        }

        $cmd = $this->db->createCommand( "SELECT id FROM hr_timux_timecode WHERE defaultHoliday=1" );
        $query = $cmd->query();
        $data = $query->read();
        if($data) {
            $this->setCache('getDefaultHolidaysCounter_', $data['id']);
            return $data['id'];
        }

        $this->setCache('getDefaultHolidaysCounter_', false);
        return false;

    }


/*
     * Return the number of non working day for a day
     * @param year Year to get the non working day
     * @param month Month to get the non working day
     * @return Return the number of day
     */
    public function getNonWorkingDay($year,$month, $day)
    {
        $date = $year."-".sprintf("%02d",$month)."-".$day;

        $cache = $this->getCache('getNonWorkingDay_'.$date);
        if($cache) {
            return $cache;
        }

        $date = $year."-".$month."-".$day;

        $cmd = $this->db->createCommand( "SELECT period FROM hr_non_working_day AS n WHERE n.from<='$date' AND n.until>='$date'" );
        $query = $cmd->query();
        $data = $query->read();

        if($data)
        {
            if($data['period'] == 'allday') {
                $this->setCache('getNonWorkingDay_'.$date, 1);
                return 1;
            }
            else {
                $this->setCache('getNonWorkingDay_'.$date, 0.5);
                return 0.5;
            }
        }

        $this->setCache('getNonWorkingDay_'.$date, 0);
        return 0;
    }

    /*
     * Return the number of non working day for a month
     * @param year Year to get the non working day
     * @param month Month to get the non working day
     * @return Return the number of day
     */
    public function getAllNonWorkingDay($year,$month)
    {
        $date = $year."-".sprintf("%02d",$month);

        $cache = $this->getCache('getAllNonWorkingDay_'.$date);
        if($cache) {
            return $cache;
        }

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

        $this->setCache('getAllNonWorkingDay_'.$date,$nbre);
        return $nbre;
    }

    /**
     * Return the non wroking day until the end of the year
     * @param <type> $year
     * @param <type> $month
     * @return <type>
     */
    public function getNonWorkingDayEndOfYear($month,$year)
    {
        $date = $year."-".sprintf("%02d",$month);

        $cache = $this->getCache('getNonWorkingDayEndOfYear_'.$date);
        if($cache) {
            return $cache;
        }

        $nbre = 0.0;
        for($i=$month+1; $i<=12; $i++)
        {
            $nbre += $this->getAllNonWorkingDay($year, $i);
        }

        $this->setCache('getNonWorkingDayEndOfYear_'.$date, $nbre);
        return $nbre;
    }

    /**
     * Return the request according to a timecode id
     * @param <type> $year
     * @param <type> $month
     * @param <type> $timecode
     * @return <type>
     */
    public function getRequest($year,$month, $timecode)
    {
        $dateCache = $year."-".sprintf("%02d",$month)."-".$timecode;

        $cache = $this->getCache('getRequest_'.$dateCache);
        if($cache) {
            return $cache;
        }


        if($timecode == NULL) {
            $this->setCache('getRequest_'.$dateCache, 0);
            return 0;
        }

        $dateFrom = $year."-".$month."-1";
        $dateTo = $year."-".$month."-".date("t",mktime(0,0,0,$month,1,$year));

        $nbreOfHours['nbre'] = 0.0;

        $cmd=$this->db->createCommand("SELECT * FROM hr_timux_timecode WHERE id=".$timecode);

        $data = $cmd->query();
        $data = $data->read();

        $nbreOfHours['disp'] = $data['formatDisplay'];

        while(strtotime($dateFrom) <= strtotime($dateTo))
        {
            $cmd=$this->db->createCommand("SELECT * FROM hr_timux_request AS r LEFT JOIN hr_timux_request_leave AS rl ON rl.request_id=r.id LEFT JOIN hr_timux_timecode AS t ON t.id=r.timecodeId WHERE rl.datefrom<='".$dateFrom."' AND rl.dateto>='".$dateFrom."' AND ( r.state='validate' OR  r.state='closed') AND r.userId=".$this->employeeId." AND t.id=".$timecode);

            $data = $cmd->query();
            $datas = $data->readAll();

            if($datas)
            {
                foreach($datas as $data) {
                    $date = explode("-",$dateFrom);

                    $period = $data['period'];
                    $format = $data['formatDisplay'];                    

                    if($this->isWorking($date[0], $date[1], $date[2]))
                    {
                        $nwd = $this->getNonWorkingDay($date[0], $date[1], $date[2]);
                        $nwdPeriod = $this->getNonWorkingDayPeriod($date[0], $date[1], $date[2]);
                        $nPeriod = $this->isWorkingPeriod($date[0], $date[1], $date[2]);

                        if($nwdPeriod == 'morning' && $period == 'afternoon')
                        {
                            if($nPeriod != $nwdPeriod)
                                $nbreOfHours['nbre'] += $format == 'day' ? 0.5 : bcmul(0.5, $this->getDayTodo($date[2], $date[1], $date[0]), 4);
                        }

                        if($nwdPeriod == 'afternoon' && $period == 'morning')
                        {
                            if($nPeriod != $nwdPeriod)
                                $nbreOfHours['nbre'] += $format == 'day' ? 0.5 : bcmul(0.5, $this->getDayTodo($date[2], $date[1], $date[0]), 4);
                        }

                        if($nwdPeriod == 'afternoon' && $period == 'allday')
                        {
                            if($nPeriod != $nwdPeriod)
                                $nbreOfHours['nbre'] += $format == 'day' ? 0.5 : bcmul(0.5, $this->getDayTodo($date[2], $date[1], $date[0]), 4);
                        }

                        if($nwdPeriod == 'morning' && $period == 'allday')
                        {
                            if($nPeriod != $nwdPeriod)
                                $nbreOfHours['nbre'] += $format == 'day' ? 0.5 : bcmul(0.5, $this->getDayTodo($date[2], $date[1], $date[0]), 4);
                        }

                        if($nwd==false && $period == 'morning')
                        { 
                            if($nPeriod =='allday')
                                $nbreOfHours['nbre'] += $format == 'day' ? 0.5 : bcmul(0.5, $this->getDayTodo($date[2], $date[1], $date[0]), 4);
                        }

                        if($nwd==false && $period == 'afternoon')
                        {
                            if($nPeriod =='allday')
                                $nbreOfHours['nbre'] += $format == 'day' ? 0.5 : bcmul(0.5, $this->getDayTodo($date[2], $date[1], $date[0]), 4);
                        }

                        if($nwd==false && $period == 'allday')
                        {
                           if($nPeriod == $period)
                                $nbreOfHours['nbre'] += $format == 'day' ? 1 : bcmul(1, $this->getDayTodo($date[2], $date[1], $date[0]), 4);
                           else
                                $nbreOfHours['nbre'] += $format == 'day' ? 0.5 : bcmul(1, $this->getDayTodo($date[2], $date[1], $date[0]), 4);
                        }

                        if($timecode == $this->getDefaultHolidaysCounter() ) {
                            $requests = $this->getDayRequest($date[2], $date[1], $date[0]);
                            foreach($requests as $request) {
                                if($request['id'] != $this->getDefaultHolidaysCounter() && $request['id'] != $this->getDefaultOvertimeCounter() && $request['timeworked']) {

                                    $leaveRequestPeriod = $request['period'];


                                    if($leaveRequestPeriod == 'morning' && $period == 'morning')
                                    {
                                        $nbreOfHours['nbre'] -= $format == 'day' ? 0.5 : bcmul(0.5, $this->getDayTodo($date[2], $date[1], $date[0]), 4);
                                    }

                                    if($leaveRequestPeriod == 'morning' && $period == 'allday')
                                    {
                                        $nbreOfHours['nbre'] -= $format == 'day' ? 0.5 : bcmul(0.5, $this->getDayTodo($date[2], $date[1], $date[0]), 4);
                                    }

                                    if($leaveRequestPeriod == 'afternoon' && $period == 'afternoon')
                                    {
                                        $nbreOfHours['nbre'] -= $format == 'day' ? 0.5 : bcmul(0.5, $this->getDayTodo($date[2], $date[1], $date[0]), 4);
                                    }


                                    if($leaveRequestPeriod == 'afternoon' && $period == 'allday')
                                    {
                                        $nbreOfHours['nbre'] -= $format == 'day' ? 0.5 : bcmul(0.5, $this->getDayTodo($date[2], $date[1], $date[0]), 4);
                                    }


                                    if($leaveRequestPeriod == 'allday')
                                    {
                                        $nbreOfHours['nbre'] -= $format == 'day' ? 1 : bcmul(1, $this->getDayTodo($date[2], $date[1], $date[0]), 4);
                                    }

                                }
                            }
                        }
                    }
                }
            }

            $dateFrom = date("Y-m-d",strtotime(date("Y-m-d", strtotime($dateFrom)) . " +1 day"));
        }

        $this->setCache('getRequest_'.$dateCache, $nbreOfHours);
        return $nbreOfHours;
    }

    /**
     *  Return if the 
     * @param <type> $year
     * @param <type> $month
     * @param <type> $day
     * @return <type>
     */
    public function isWorking($year, $month, $day)
    {
        $date = $year."-".sprintf("%02d",$month)."-".$day;

        $cache = $this->getCache('isWorking_'.$date);
        if($cache) {
            return $cache;
        }    

        $wt = $this->getWorkingTime($day, $month, $year);

        $dayName = strtolower(date("l",mktime(0,0,0,$month,$day,$year)));

        if($wt[$dayName.'Time_m'] > 0 || $wt[$dayName.'Time_a'] > 0)
        {
            if($wt[$dayName.'Time_m'] > 0 && $wt[$dayName.'Time_a'] > 0) {
                $this->setCache('isWorking_'.$date,1);
                return 1;
            }

            $this->setCache('isWorking_'.$date,0.5);
            return 0.5;

        }
        else
        {
            $this->setCache('isWorking_'.$date,false);
            return false;
        }

    }

    public function isAWorkingDay($year, $month, $day) {

        $date = $year."-".sprintf("%02d",$month)."-".$day;

        $cache = $this->getCache('isAWorkingDay_'.$date);
        if($cache) {
            return $cache;
        }   

        $config = $this->getConfig();

        $nday = date("N",mktime(0,0,0,$month,$day, $year));

        $workingDay = explode(",", $config['workingDays']);

        if($workingDay[$nday-1]) {
            $this->setCache('isAWorkingDay_'.$date,true);
            return true;
        }
        else {
            $this->setCache('isAWorkingDay_'.$date,false);
            return false;
        }

    }

    public function isWorkingPeriod($year, $month, $day)
    {

        $date = $year."-".sprintf("%02d",$month)."-".$day;
        $cache = $this->getCache('isWorkingPeriod_'.$date);
        if($cache) {
            return $cache;
        }

        $wt = $this->getWorkingTime($day, $month, $year);

        $dayName = strtolower(date("l",mktime(0,0,0,$month,$day,$year)));

        if($wt[$dayName.'Time_m'] > 0 || $wt[$dayName.'Time_a'] > 0)
        {
            if($wt[$dayName.'Time_m'] > 0 && $wt[$dayName.'Time_a'] > 0)
            {
                $this->setCache('isWorkingPeriod_'.$date,'allday');
                return 'allday';
            }
            elseif($wt[$dayName.'Time_m'] > 0)
            {
                $this->setCache('isWorkingPeriod_'.$date,'morning');
                return 'morning';
            }
            else
            {
                $this->setCache('isWorkingPeriod_'.$date,'afternoon');
                return 'afternoon';
            }

        }
        else
        {
            $this->setCache('isWorkingPeriod_'.$date,false);
            return false;
        }

    }

    /*
     * Return the period of non working day for
     * @param year Year to get the non working day
     * @param month Month to get the non working day
     * @return Return the number of day
     */
    public function getNonWorkingDayPeriod($year,$month, $day)
    {
        $dateCache = $year."-".sprintf("%02d",$month)."-".$day;
        $cache = $this->getCache('getNonWorkingDayPeriod_'.$dateCache);
        if($cache) {
            return $cache;
        }

        $date = $year."-".$month."-".$day;

        $cmd = $this->db->createCommand( "SELECT period FROM hr_non_working_day AS n WHERE n.from<='$date' AND n.until>='$date'" );
        $query = $cmd->query();
        $data = $query->read();

        if($data)
        {
           $this->setCache('getNonWorkingDayPeriod_'.$dateCache,$data['period']);
           return $data['period'];
        }

        $this->setCache('getNonWorkingDayPeriod_'.$dateCache,false);
        return false;
    }

    public function geHolidaystMonth($year, $month)
    {
        $date = $year."-".sprintf("%02d",$month);

        $cache = $this->getCache('geHolidaystMonth_'.$date);
        if($cache) {
            return $cache;
        }

        if($oldM == 0 && $oldY == 0) {
            $oldM = $month;
            $oldY = $year;
        }

        $timeCode = $this->getDefaultHolidaysCounter();

        if(!$timeCode ) {
            $this->setCache('geHolidaystMonth_'.$date,0);
            return 0;
        }

        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE isClosedMonth=1 AND year=$year AND month=$month AND timecode_id=$timeCode AND user_id=".$this->employeeId );
        $query = $cmd->query();
        $data = $query->read();

        if($data) { // ok, the month is closed

            $this->setCache('geHolidaystMonth_'.$date,$data['nbre']);
            return $data['nbre'] ;
        } else {

            $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE year=0 AND month=0 AND timecode_id=$timeCode AND user_id=".$this->employeeId );
            $query = $cmd->query();
            $data = $query->read();

            $currentNbre = $data['nbre'];

            $cmd = $this->db->createCommand( "SELECT month, year FROM hr_timux_activity_counter WHERE isClosedMonth=1 AND  timecode_id=$timeCode AND user_id=".$this->employeeId." ORDER BY year DESC, month DESC LIMIT 0,1" );
            $query = $cmd->query();
            $data = $query->read();
            $lastCloseMonth = $data['month'];
            $lastCloseYear = $data['year'];

            if($year - $lastCloseYear > 1) {
                $currentNbre = 25 + $this->geHolidaystMonth($year-1,12);
            }

            if($lastCloseMonth == 12 && $year-1 == $lastCloseYear ) {

                $lastCloseMonth = 1;

                $nvt = 0;

                for($i=$lastCloseMonth; $i<=$month; $i++) {
                    $nv = $this->getRequest($year, $i, $timeCode);
                    $nvt += $nv['nbre'];

                    $holidayActivityCounter = $this->getActivityCounter($year, $i, $timeCode );

                    $nvt -= $holidayActivityCounter;

                }
                $this->setCache('geHolidaystMonth_'.$date,$currentNbre - $nvt);
                return $this->getCache('geHolidaystMonth_'.$date);


            } else {

                $lastCloseMonth++;

                $nvt = 0;

                for($i=$lastCloseMonth; $i<=$month; $i++) {
                    $nv = $this->getRequest($year, $i, $timeCode);
                    $nvt += $nv['nbre'];

                    $holidayActivityCounter = $this->getActivityCounter($year, $i, $timeCode );

                    $nvt -= $holidayActivityCounter;
                }
                $this->setCache('geHolidaystMonth_'.$date,$currentNbre - $nvt);
                return $this->getCache('geHolidaystMonth_'.$date);
            }
        }
    }

    public function geHolidaystForTheYear($year, $month)
    {
        $wt = $this->getWorkingTime(1, $month, $year);

        if($wt)
            return $wt['holidaysByYear'];

        return 0;

    }

    /*
     * Return the period of general absence for a day
     * @param int month Month to get the holiday
     * @param int year Year to get the holiday
     * @return Return the number of day
     */
    public function getMonthAbsence($month, $year)
    {
        $date = $year."-".sprintf("%02d",$month);
        $cache = $this->getCache('getMonthAbsence_'.$date);
        if($cache) {
            return $cache;
        }

        $idDO = $this->getDefaultOvertimeCounter();
        $idDH = $this->getDefaultHolidaysCounter();
        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_timecode WHERE id NOT IN ($idDO, $idDH) ORDER BY name");
        $query = $cmd->query();
        $data = $query->readAll();

        if($data) {
            foreach($data as $d) {
                $absence = $this->getRequest($year, $month, $d['id']);
                $absence['name'] = $d['name'];
                $absences[] = $absence;
                
            }
        }

        $this->setCache('getMonthAbsence_'.$date,$absences);
        return $absences;
    }


    public function getMonthLeaveRequest($month, $year) {

        $date = $year."-".sprintf("%02d",$month);

        $cache = $this->getCache('getMonthLeaveRequest_'.$date);
        if($cache) {
            return $cache;
        }

        $dateFrom = $year."-".$month."-1";
        $dateTo = $year."-".$month."-".date("t",mktime(0,0,0,$month,1,$year));

        $timecode = $this->getDefaultHolidaysCounter();

        if($timecode == "")  {
            $this->setCache('getMonthLeaveRequest_'.$date, array());
            return array();
        }

        $cmd=$this->db->createCommand("SELECT * FROM hr_timux_timecode WHERE (type='leave' OR type='overtime' ) AND id!=".$timecode);

        $data = $cmd->query();
        $datas = $data->readAll();

        $nbreOfHours = 0.0;
        $nbreOfDay = 0.0;

        foreach($datas as $d) {
            $n = $this->getRequest($year, $month, $d['id']);

            if($n['format'] == 'day') {
                $nbreOfDay = bcadd($nbreOfHours, $n['nbre'], 4);

            } else {
                $nbreOfHours = bcadd($nbreOfHours, $n['nbre'], 4);
            }
        }

        $wt = $this->getWorkingTime(1, $month, $year);

        $config = $this->getConfig();

        $hoursByDay = bcdiv($wt['hoursByWeek'], $config['daysByWeek'],4);

        if($wt['calendarType'] == 1) {
            $hoursByDay = bcdiv(bcmul($wt['workingPercent'],$hoursByDay,4),100,4);
        }

        $nbreOfDay = bcmul($nbreOfDay,$hoursByDay,4 );

        $this->setCache('getMonthLeaveRequest_'.$date, bcadd($nbreOfHours, $nbreOfDay, 4));

        return $this->getCache('getMonthLeaveRequest_'.$date);
    }

    public function getMonthAbsentRequest($month, $year) {

        $dateCache = $year."-".sprintf("%02d",$month);

        $cache = $this->getCache('getMonthAbsentRequest_'.$dateCache);
        if($cache) {
            return $cache;
        }

        $dateFrom = $year."-".$month."-1";
        $dateTo = $year."-".$month."-".date("t",mktime(0,0,0,$month,1,$year));

        $timecode = $this->getDefaultHolidaysCounter();

        if($timecode == "") {
            $this->setCache('getMonthAbsentRequest_'.$dateCache, array());
            return array();
        }

        $nbreOfDays = 0.0;

        while(strtotime($dateFrom) <= strtotime($dateTo))
        {
            $cmd=$this->db->createCommand("SELECT * FROM hr_timux_request AS r LEFT JOIN hr_timux_request_leave AS rl ON rl.request_id=r.id LEFT JOIN hr_timux_timecode AS t ON t.id=r.timecodeId WHERE rl.datefrom<='".$dateFrom."' AND rl.dateto>='".$dateFrom."' AND ( r.state='validate' OR  r.state='closed') AND r.userId=".$this->employeeId." AND t.type='absence'");
            $data = $cmd->query();
            $datas = $data->readAll();

            if($datas) {
                foreach($datas as $data) {
                    $date = explode("-",$dateFrom);

                    $period = $data['period'];
                    $format = $data['formatDisplay'];

                    if($this->isWorking($date[0], $date[1], $date[2]))
                    {
                        $nwd = $this->getNonWorkingDay($date[0], $date[1], $date[2]);
                        $nwdPeriod = $this->getNonWorkingDayPeriod($date[0], $date[1], $date[2]);
                        $nPeriod = $this->isWorkingPeriod($date[0], $date[1], $date[2]);

                        if($nwdPeriod == 'morning' && $period == 'afternoon')
                        {
                            if($nPeriod != $nwdPeriod)
                                $nbreOfDays += 0.5;
                        }

                        if($nwdPeriod == 'afternoon' && $period == 'morning')
                        {
                            if($nPeriod != $nwdPeriod)
                                $nbreOfDays += 0.5;
                        }

                        if($nwdPeriod == 'afternoon' && $period == 'allday')
                        {
                            if($nPeriod != $nwdPeriod)
                                $nbreOfDays += 0.5;
                        }

                        if($nwdPeriod == 'morning' && $period == 'allday')
                        {
                            if($nPeriod != $nwdPeriod)
                                $nbreOfDays += 0.5;
                        }

                        if($nwd==0 && $period == 'morning')
                        {
                            if($nPeriod =='allday')
                                $nbreOfDays += 0.5;
                        }

                        if($nwd==0 && $period == 'afternoon')
                        {
                            if($nPeriod =='allday')
                                $nbreOfDays += 0.5;
                        }

                        if($nwd==0 && $period == 'allday')
                        {
                            if($nPeriod == $period)
                                $nbreOfDays += 1;
                            else
                                $nbreOfDays += 0.5;
                        }
                    }
                }
            }

            $dateFrom = date("Y-m-d",strtotime(date("Y-m-d", strtotime($dateFrom)) . " +1 day"));

        }

        $this->setCache('getMonthAbsentRequest_'.$dateCache, $nbreOfDays);

        return $nbreOfDays;
    }

    /*
     * Return the error in a month
     */
    public function getError($year=0, $month=0) {

        $dateCache = $year."-".sprintf("%02d",$month);

        $cache = $this->getCache('getError_'.$dateCache);
        if($cache) {
            return $cache;
        }

        $result = array();


        if($this->employeeId == '' || $this->employeeId == '') {
            $this->setCache('getError_'.$dateCache,$result);
            return $result;
        }

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
            if($month > date("n") && $year >= date("Y")) {
                $this->setCache('getError_'.$dateCache,array());
                return array();
            }

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
               $typeText = Prado::localize("Signing error");
               foreach($bookings as $b)
               {
                  $remark .= '<a href="index.php?page=components.timuxuser.booking.mod&back=components.timuxuser.error.error&id='.$b['id'].'" >';
                  $remark .= substr($b['time'],0,5).'/';
                  $remark .= $this->isBookingIn($b) == 'in' ? Prado::localize('In') : Prado::localize('Out');
                  $remark .= $b['internet'] ? "*" :"";
                  $remark .= '</a>';
                  $remark .= "&nbsp;&nbsp;&nbsp;";
               }

               $result[] = array('user'=>$this->getFullName(), 'date'=>  $date, 'typeText'=>$typeText, 'remark'=>$remark, 'type'=>'sign');
            }

            
        }

        $this->setCache('getError_'.$dateCache,$result);
        return $result;
    }

    /*
     * 
     */
    public function getEmployeeLeaveRequest($state)
    {
        $cache = $this->getCache('getEmployeeLeaveRequest_'.$state);
        if($cache) {
            return $cache;
        }

        if($state != 'all')
            $state = ' tr.state=\''.$state.'\' AND ';
        else
            $state = '';


        $cmd = $this->db->createCommand( "SELECT tr.*, CONCAT(u.name, ' ', u.firstname ) AS modUser, tt.name AS timcodeName, rl.* FROM hr_timux_request AS tr LEFT JOIN hr_user AS u ON u.id=tr.modifyUserId LEFT JOIN hr_timux_timecode AS tt ON tt.id=tr.timecodeId LEFT JOIN hr_timux_request_leave AS rl ON rl.request_id=tr.id  WHERE $state tr.userId=:id AND tr.state<>'closed' AND tr.state<>'canceled' AND rl.datefrom > CURDATE() ORDER BY tr.createDate DESC" );
        $cmd->bindValue(":id",$this->employeeId,PDO::PARAM_STR);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->readAll();
            $this->setCache('getEmployeeLeaveRequest_'.$state,$data);
            return $data;
        }

        $this->setCache('getEmployeeLeaveRequest_'.$state,array());
        return array();
    }

    /*
     * Get the number of hours by day
     */
    function getHoursByDay($month=0, $year=0)
    {

        $date = $year."-".sprintf("%02d",$month);

        $cache = $this->getCache('getHoursByDay_'.$date);
        if($cache) {
            return $cache;
        }

        if($month == 0 && $year == 0) {
            $hoursByDay = 0;

            $config = $this->getConfig();

            if($config['daysByWeek'] > 0) {
                $hoursByDay = bcdiv($config['hoursByWeek'], $config['daysByWeek'], 4);
            }

            $this->setCache('getHoursByDay_'.$date,$hoursByDay);
            return $hoursByDay;
        }

        $wt = $this->getWorkingTime(1, $month, $year);
        $config = $this->getConfig();
        
        if($wt['calendarType'] == 1) {
            $hours100 = 0;
            $nWorkingDay = 0;

            $nbreOfDay = date("t",mktime(0,0,0,$month,1,$year));
            
            for($day=1; $day<=$nbreOfDay;$day++) {

                if($this->isAWorkingDay($year, $month, $day)>0 && !$this->isNonWorkingDay($day, $month, $year)) {

                    $hours100 = bcadd($hours100, bcdiv($wt['hoursByWeek'],$config['daysByWeek'], 4),4);
                    $nWorkingDay++;
                }

            }

           $hoursX = bcmul($hours100 ,bcdiv($wt['workingPercent'], 100, 4),4);


           $this->setCache('getHoursByDay_'.$date,bcdiv($hoursX, $nWorkingDay, 4));
           return $this->getCache('getHoursByDay_'.$date);

        }
        
        if($wt['calendarType'] == 2) {

            if($config['daysByWeek'] > 0) {
                $hoursByDay = bcdiv($wt['hoursByWeek'], $config['daysByWeek'], 4);
            }
            $this->setCache('getHoursByDay_'.$date,$hoursByDay);
            return $hoursByDay;
        }

    }

    /*
     * return the timecode name
     */
    public function getTimeCodeName($timecodeId) {

        $cache = $this->getCache('getTimeCodeName_'.$timecodeId);
        if($cache) {
            return $cache;
        }

        $cmd = $this->db->createCommand( "SELECT name FROM hr_timux_timecode WHERE id=:id" );
        $cmd->bindValue(":id",$timecodeId,PDO::PARAM_STR);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            $this->setCache('getTimeCodeName_'.$timecodeId,$data['name']);
            return $data['name'];
        }

        $this->setCache('getTimeCodeName_'.$timecodeId, '');
        return '';
    }

    /*
    * Restore a month
    */
    public function restoreMonth($month, $year) {

       $cmd=$this->db->createCommand("SELECT * FROM hr_timux_restore_month WHERE user_id=:user_id AND `month`=:month AND `year`=:year");
       $cmd->bindValue(":user_id",$this->employeeId);
       $cmd->bindValue(":month",$month);
       $cmd->bindValue(":year",$year);
       $cmd = $cmd->query();
       $rows = $cmd->readAll();

       if(count($rows) == 0)
           return;

       $cmd=$this->db->createCommand("DELETE FROM hr_timux_activity_counter WHERE user_id=:user_id");
       $cmd->bindValue(":user_id",$this->employeeId);
       $cmd->execute();

       foreach($rows as $row) {
           $cmd=$this->db->createCommand($row['insert']);
           $cmd->execute();

           $cmd=$this->db->createCommand("DELETE FROM hr_timux_restore_month WHERE user_id=:user_id AND `month`>=:month AND `year`>=:year");
           $cmd->bindValue(":user_id",$this->employeeId);
           $cmd->bindValue(":month",$month);
           $cmd->bindValue(":year",$year);
           $cmd->execute();
       }

       //unlock the booking
       $dateFrom = $year."-".$month."-01";
       $cmd=$this->db->createCommand("UPDATE hr_timux_booking SET closed='0' WHERE tracking_id IN (SELECT id FROM hr_tracking AS t WHERE t.date>='$dateFrom' AND id_user=".$this->employeeId." )");
       $cmd->execute();

       //unlock the request
        $dateFrom = $year."-".$month."-1";
        $dateTo = $year."-".$month."-".date("t",mktime(0,0,0,$month,1,$year));

        $cmd=$this->db->createCommand("SELECT r.id FROM hr_timux_request AS r LEFT JOIN hr_timux_request_leave AS rl ON rl.request_id=r.id LEFT JOIN hr_timux_timecode AS t ON t.id=r.timecodeId WHERE rl.datefrom>='".$dateFrom."' AND rl.dateto<='".$dateTo."' AND r.state='closed' AND r.userId=".$this->employeeId);

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
            $cmd=$this->db->createCommand("UPDATE hr_timux_request SET state='validate', modifyDate=CURDATE(),modifyUserId=".$userId." WHERE id=".$d['id']);
            $cmd->execute();
        }
    }


   /*
    * Close a month, do back possible after that
    */
   public function closeMonth($month, $year) {

       //backup the current month
       $cmd=$this->db->createCommand("SELECT * FROM hr_timux_activity_counter WHERE user_id=:user_id");
       $cmd->bindValue(":user_id",$this->employeeId);
       $rows = $cmd->query();
       $rows = $rows->readAll();
       
       foreach($rows as $ac) {
           $cmd=$this->db->createCommand("INSERT INTO hr_timux_restore_month (`user_id`, `year`, `month`, `insert`) VALUES (:user_id, :year, :month, :insert)");
           $cmd->bindValue(":user_id",$this->employeeId);
           $cmd->bindValue(":year",$year);
           $cmd->bindValue(":month",$month);

           $insert = "INSERT INTO hr_timux_activity_counter (`user_id`, `timecode_id`, `nbre`, `year`, `month`, `day`, `isClosedMonth`, `remark`) VALUES ({$ac['user_id']},{$ac['timecode_id']},{$ac['nbre']},{$ac['year']},{$ac['month']},{$ac['day']},{$ac['isClosedMonth']},'".addslashes($ac['remark'])."')";
           $cmd->bindValue(":insert",$insert);

           $cmd->execute();
       }


       // get all the time code
       $cmd=$this->db->createCommand("SELECT * FROM hr_timux_timecode");
       $timecodes = $cmd->query();
       $timecodes = $timecodes->readAll();

       // get the default time code for the overtime and the holidays
       $defaultTimeCodeOvertime = $this->getDefaultOvertimeCounter();
       $defaultTimeCodeHolidays = $this->getDefaultHolidaysCounter();

       foreach($timecodes as $tc) {

           // get the format of the time code (day or hour)
           $format = $tc['formatDisplay'];

           switch($tc['type']) {

               case 'leave':
                   //here, we will close the default holidays counter
                   if($defaultTimeCodeHolidays == $tc['id']) {

                        // get the current value
                        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE year=0 AND month=0 AND timecode_id=$defaultTimeCodeHolidays AND user_id=".$this->employeeId );
                        $query = $cmd->query();
                        $data = $query->read();
                        $nvy = $data['nbre'];

                        $holidays = $this->getRequest($year,$month,$defaultTimeCodeHolidays);

                        
                        $holidaysCurrentMonth = bcsub($nvy, $holidays['nbre'],2);

                        $holidaysCurrentMonth = bcadd($holidaysCurrentMonth, $this->getActivityCounter($year, $month, $defaultTimeCodeHolidays), 4);


                        $cmd=$this->db->createCommand("INSERT hr_timux_activity_counter SET  isClosedMonth=1, nbre=".$holidaysCurrentMonth.", year=$year, month=$month, user_id=".$this->employeeId.", timecode_id=".$tc['id']);
                        $cmd->execute();

                        // add the holidays for the new year
                        if($month == 12) {
                            $wt = $this->getWorkingTime(1, $month, $year);

                            $holidaysCurrentMonth += $wt['holidaysByYear'];
                        }

                        $cmd=$this->db->createCommand("UPDATE hr_timux_activity_counter SET nbre=".$holidaysCurrentMonth." WHERE year=0 AND month=0 AND user_id=".$this->employeeId." AND timecode_id=".$tc['id']);
                        $cmd->execute();

                   } else {

                   }
                   break;

               case 'overtime':
                   //here, we will close the default overtime counter
                   if($defaultTimeCodeOvertime == $tc['id']) {
                        $overTimeLastMonth = $this->getOvertimeLastMonth($month, $year);

                        $overTimeMonth = 0;

                        for($day=1; $day<=date('t', mktime(0,0,0,$month,1,$year));$day++) {
                            $todo = $this->getDayTodo($day,$month, $year);
                            $done = $this->getDayDone($day,$month, $year);
                            $overTimeMonth = bcadd($overTimeMonth, bcsub($done['done'], $todo ,4),4 );
                        }

                        $overtime = bcadd($overTimeLastMonth,$overTimeMonth,4);

                        $timeCode = $this->getDefaultOvertimeCounter();
                        $overtime = bcadd($overtime, $this->getActivityCounter($year, $month, $timeCode), 4);

                        $cmd=$this->db->createCommand("INSERT hr_timux_activity_counter SET isClosedMonth=1, nbre=".$overtime.", year=$year, month=$month, user_id=".$this->employeeId.", timecode_id=".$tc['id']);
                        $cmd->execute();

                        $cmd=$this->db->createCommand("UPDATE hr_timux_activity_counter SET nbre=".$overtime." WHERE year=0 AND month=0 AND user_id=".$this->employeeId." AND timecode_id=".$tc['id']);
                        $cmd->execute();


                   } else {

                   }
                   break;

               case 'absence':
                   break;

               case 'load':
                   break;
               
           }
       }

       $this->closeBooking($month, $year);
       
       $this->closeRequest($month, $year);

       $this->sendEmailMonthReport($year, $month);
   }

   /*
    * Send the month report to the user
    */
   public function sendEmailMonthReport($year, $month) {
       
   }

    /*
     *  close the booking for a month
     */
    public function closeBooking($month, $year)
    {
        $dateFrom = $year."-".$month."-1";
        $dateTo = $year."-".$month."-".date("t",mktime(0,0,0,$month,1,$year));

        $cmd=$this->db->createCommand("UPDATE hr_timux_booking SET closed='1' WHERE tracking_id IN (SELECT id FROM hr_tracking AS t WHERE t.date>='$dateFrom' AND t.date<='$dateTo' AND id_user=".$this->employeeId." )");
        $cmd->execute();
    }

    /*
     *  close the reuqest for a month
     */
    public function closeRequest($month,$year)
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

    public function getHourly($month, $year) {

        $date = $year."-".sprintf("%02d",$month);
        $cache = $this->getCache('getHourly_'.$date);
        if($cache) {
            return $cache;
        }

        $cmd=$this->db->createCommand("SELECT hourly FROM hr_timux_hourly WHERE month=:month AND year=:year AND user_id=".$this->employeeId);
        $cmd->bindValue(':month', $month);
        $cmd->bindValue(':year',$year);
        $data = $cmd->query();
        $data = $data->read();
        if($data) {
            $this->setCache('getHourly_'.$date,$data['hourly']);
            return $data['hourly'];
        }

        $this->setCache('getHourly_'.$date,0);
        return 0;
    }

    public function isLastMonthLastYeatClosed($year) {

        $cache = $this->getCache('isLastMonthLastYeatClosed_'.$year);
        if($cache) {
            return $cache;
        }

        $year--;

        $cmd=$this->db->createCommand("SELECT COUNT(*) AS n FROM hr_timux_activity_counter WHERE month=12 AND year=:year AND user_id=".$this->employeeId);
        $cmd->bindValue(':year',$year);
        $data = $cmd->query();
        $data = $data->read();

        if($data['n']>0) {
            $this->setCache('isLastMonthLastYeatClosed_'.$year, true);
            return true;
        }
        else {
            $this->setCache('isLastMonthLastYeatClosed_'.$year, false);
            return false;
        }
    }

    public function isClosedMonth($month, $year) {

        $date = $year."-".sprintf("%02d",$month);

        $cache = $this->getCache('isClosedMonth_'.$date);
        if($cache) {
            return $cache;
        }

        $cmd=$this->db->createCommand("SELECT* FROM hr_timux_activity_counter WHERE user_id=".$this->employeeId." AND year=$year AND month=$month AND isClosedMonth=1");
        $data = $cmd->query();
        $data = $data->readAll();

        if(count($data)>0) {
            $this->setCache('isClosedMonth_'.$date, true);
            return true;
        } else {
            $this->setCache('isClosedMonth_'.$date, false);
            return false;
        }
    }

    public function getHoursMonthTodo($month, $year) {

        $date = $year."-".sprintf("%02d",$month);

        $cache = $this->getCache('getHoursMonthTodo_'.$date);
        if($cache) {
            return $cache;
        }

        $HoursMonth = 0;

        $nbreOfDay = date("t",mktime(0,0,0,$month,1,$year));
        
        $wt = $this->getWorkingTime(1, $month, $year);

        for($day=1; $day<=$nbreOfDay;$day++) {

            $dayTodo = $this->getDayTodo($day, $month, $year);
            $HoursMonth = bcadd($HoursMonth,$dayTodo,4);
        }

        $this->setCache('getHoursMonthTodo_'.$date, $HoursMonth);

        return $HoursMonth;
    }

}

?>