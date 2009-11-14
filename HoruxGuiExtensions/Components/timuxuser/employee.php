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
     */
    public function getBookings($status, $from, $until)
    {

        switch($status)
        {
            case '1':
                $status = ' (tb.action=255 OR tb.action=500 OR tb.action=502 ) AND ';
                break;
            case '0':
                $status = ' (tb.action=254 OR tb.action=501 OR tb.action=503  ) AND ';
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

        $cmd=$this->db->createCommand("SELECT t.id, t.date, tb.roundBooking AS time, tb.action, tb.actionReason FROM hr_tracking AS t LEFT JOIN hr_timux_booking AS tb ON tb.tracking_id=t.id LEFT JOIN hr_user AS u ON u.id=t.id_user WHERE $date $status t.id_user={$this->employeeId} AND tb.action!='NULL' ORDER BY t.date DESC, t.time DESC  LIMIT 0,1000");

        $data = $cmd->query();
        $data = $data->readAll();

        return $data;
    }

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

    public function getLeaveRequest()
    {
        $cmd = $this->db->createCommand( "SELECT tr.*, CONCAT(u.name, ' ', u.firstname ) AS modUser, tt.name AS timcodeName, rl.* FROM hr_timux_request AS tr LEFT JOIN hr_user AS u ON u.id=tr.modifyUserId LEFT JOIN hr_timux_timecode AS tt ON tt.id=tr.timecodeId LEFT JOIN hr_timux_request_leave AS rl ON rl.request_id=tr.id  WHERE tr.userId=:id AND tr.state<>'closed' AND tr.state<>'canceled' ORDER BY tr.createDate DESC" );
        $cmd->bindParameter(":id",$this->employeeId,PDO::PARAM_STR);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->readAll();
            return $data;
        }

        return array();        
    }
}

?>
