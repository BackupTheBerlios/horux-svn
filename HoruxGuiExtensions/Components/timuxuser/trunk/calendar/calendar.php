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

Prado::using('horux.pages.components.timuxuser.employee');

class calendar extends Page
{
    public $defaultEventMinutes = 240;

    public function onLoad($param)
    {
         parent::onLoad($param);

         if(!$this->IsPostBack)
         {
            $employee = new employee(0);
            $this->defaultEventMinutes = $employee->getHoursByDay() / 2 * 60;

         }


         if(isset($this->Request['ajax']))
         {
            $func =  $this->Request['ajax'];

            $this->$func();
            exit;
         }
    }

    protected function getStyleSheet()
    {
        header("Content-type: text/css");
        
        $cmd = $this->db->createCommand( "SELECT color FROM hr_non_working_day GROUP BY color" );
        $data =  $cmd->query();
        $data =  $data->readAll();

        $style = "";
        $color = array();
        foreach($data as $row)
        {
            if(!isset($color[substr($row['color'],1,6)]))
            {

                $style .= ".nonworking_".substr($row['color'],1,6).",\n";
                $style .= ".fc-agenda .nonworking_".substr($row['color'],1,6)." .fc-event-time,\n";
                $style .= ".nonworking_".substr($row['color'],1,6)." a {\n";
                $style .= "   background-color: ".$row['color'].";\n";
                $style .= "   border-color: ".$row['color'].";\n";
                $style .= "   color: #fff;\n";
                $style .= "   font-size: 12px;\n";
                $style .= " }\n\n";
                $color[substr($row['color'],1,6)] = 1;
            }
        }

        $cmd = $this->db->createCommand( "SELECT color FROM hr_timux_request AS tr LEFT JOIN hr_timux_request_leave AS trl ON trl.request_id=tr.id LEFT JOIN hr_timux_timecode AS tt ON tr.timecodeId=tt.id GROUP BY color" );
        $data =  $cmd->query();
        $data =  $data->readAll();

        foreach($data as $row)
        {
            if(!isset($color[substr($row['color'],1,6)]))
            {
                $style .= ".leave_".substr($row['color'],1,6).",\n";
                $style .= ".fc-agenda .leave_".substr($row['color'],1,6)." .fc-event-time,\n";
                $style .= ".leave_".substr($row['color'],1,6)." a {\n";
                $style .= "   background-color: ".$row['color'].";\n";
                $style .= "   border-color: ".$row['color'].";\n";
                $style .= "   color: #fff;\n";
                $style .= "   font-size: 12px;\n";
                $style .= " }\n\n";
                $color[substr($row['color'],1,6)] = 1;
            }
        }

        echo $style;
    }

    protected function getDate()
    {

        $from = date("Y-n-j", $this->Request['start']);
        $to = date("Y-n-j", $this->Request['end']);

        $cmd = $this->db->createCommand( "SELECT * FROM hr_non_working_day WHERE `from`>='$from' AND `until`<='$to' " );
        $data =  $cmd->query();
        $data =  $data->readAll();

        $event = array();

        foreach($data as $row)
        {

            $time = '';
            if($row['period'] == 'morning')
                $time = 'T08:00:00';
            if($row['period'] == 'afternoon')
                $time = 'T13:00:00';

            $event[] = array(
                            'id'=>$row['id'],
                            'title' => $row['name'],
                            'start' => $row['from'].$time,
                            'end' => $row['until'],
                            'url' => "index.php?page=nonWorkingDay.mod&id=".$row['id']."&back=components.timuxuser.calendar.calendar",
                            'className'=>'nonworking_'.substr($row['color'],1,6),
                            'description'=>$row['comment'],
                            'allDay'=> $row['period'] == 'allday' ? true : false
                            );
        }

        while(strtotime($from) <= strtotime($to))
        {
            $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_request AS tr LEFT JOIN hr_timux_request_leave AS trl ON trl.request_id=tr.id LEFT JOIN hr_timux_timecode AS tt ON tr.timecodeId=tt.id WHERE trl.datefrom<='$from' AND trl.dateto>='$from'  AND ( tr.state='validate' OR  tr.state='closed')" );
            $data =  $cmd->query();
            $data =  $data->readAll();

            foreach($data as $row)
            {
                $employee = new employee($row['userId']);

                $time = '';
                if($row['period'] == 'morning')
                    $time = 'T08:00:00';
                if($row['period'] == 'afternoon')
                    $time = 'T13:00:00';


                $event[$row['request_id']] = array(
                                'id'=>$row['request_id'],
                                'title' => $row['name']." (".$employee->getFullName().")",
                                'start' => $row['datefrom'].$time,
                                'end' => $row['dateto'],
                                'url' => 'index.php?page=components.timuxuser.leave.mod&id='.$row['request_id'].'&back=components.timuxuser.calendar.calendar',
                                'description'=>$row['remark'],
                                'className'=>'leave_'.substr($row['color'],1,6),
                                'allDay'=> $row['period'] == 'allday' ? true : false
                                );
            }

            $from = date("Y-m-d",strtotime(date("Y-m-d", strtotime($from)) . " +1 day"));

        }

        echo json_encode(array_values($event));

    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.panel'));
    }
}

?>
