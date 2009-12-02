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

class balances extends PageList
{
    protected $userId = 0;
    protected $employee = null;

    public function onLoad($param)
    {
        parent::onLoad($param);
        $app = $this->getApplication();
        $usedId = $app->getUser()->getUserID() == null ? 0 : $app->getUser()->getUserID();

        $cmd=$this->db->createCommand("SELECT user_id FROM hr_superusers WHERE id=$usedId");
        $data = $cmd->query();
        $dataUser = $data->read();
        $this->userId = $dataUser['user_id'];


        if(!$this->IsPostBack)
        {
            $this->employee = new employee($this->userId );

            $cmd=$this->db->createCommand("SELECT t.date FROM hr_tracking AS t ORDER BY t.date LIMIT 0,1");
            $data = $cmd->query();
            $data = $data->readAll();

            $year = date("Y");
            if(count($data)>0)
            {
                $year = explode("-",$data[0]['date']);
                $year = $year[0];
            }
            $currentYear = date("Y");

            $yearList = array();
            for($i=$year; $i<= $currentYear;$i++ )
            {
                $yearList[] = array('Value'=>$i, 'Text'=>$i);
            }


            $this->FilterYear->DataSource=$yearList;
            $this->FilterYear->dataBind();


            $FilterYear= $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterYear', date('Y'));
            $FilterMonth = $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterMonth', date('n'));
            $FilterEmployee = $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterEmployee', false);
            $FilterDepartment = $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterDepartment', false);


            $this->FilterDepartment->DataSource=$this->DepartmentList;
            $this->FilterDepartment->dataBind();

            if($FilterDepartment !== false)
                $this->FilterDepartment->setSelectedValue($FilterDepartment);
            else
                $this->FilterDepartment->setSelectedIndex(0);

            $this->FilterEmployee->DataSource=$this->EmployeeList;
            $this->FilterEmployee->dataBind();


            if($FilterEmployee)
                $this->FilterEmployee->setSelectedValue($FilterEmployee);
            else
                $this->FilterEmployee->setSelectedValue($this->userId);


            if($FilterYear)
                $this->FilterYear->setSelectedValue($FilterYear);
                
            if($FilterMonth)
                $this->FilterMonth->setSelectedValue($FilterMonth);


            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();
        }

        if(isset($this->Request['okMsg']))
        {
            $this->displayMessage($this->Request['okMsg'], true);
        }
        if(isset($this->Request['koMsg']))
        {
            $this->displayMessage($this->Request['koMsg'], false);
        }
    }

    public function getEmployeeList()
    {
        $employee = new employee($this->userId);

        $role = $employee->getRole();
        if($role == 'employee')
        {
            $id = 'id='.$this->userId.' AND ';
        }
        
        $department = $this->FilterDepartment->getSelectedValue();
        $cmd = $this->db->createCommand( "SELECT CONCAT(name, ' ', firstname) AS Text, id AS Value FROM hr_user WHERE $id department=$department");

        $data = $cmd->query();
        $data = $data->readAll();
        return $data;

    }

    public function getDepartmentList()
    {
        $employee = new employee($this->userId);

        $role = $employee->getRole();
        $department = $employee->getDepartmentId();
        $cmd = NULL;
        if($role == 'manager' || $role == 'employee')
            $cmd = $this->db->createCommand( "SELECT name AS Text, id AS Value FROM hr_department WHERE id=$department");
        else
            $cmd = $this->db->createCommand( "SELECT name AS Text, id AS Value FROM hr_department ORDER BY name");
        $data = $cmd->query();
        $data = $data->readAll();

        if($role == 'rh')
        {
            $dataAll[] = array("Value"=>0, "Text"=>Prado::localize("--- All ---"));

            $data = array_merge($dataAll, $data);
        }

        return $data;

    }

    public function selectionChangedEmployee($sender, $param)
    {
            $this->employee = new employee($this->FilterEmployee->getSelectedValue() );

            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();
            $this->Page->CallbackClient->update('list', $this->DataGrid);
            $this->Page->CallbackClient->update('timecode', $this->TimeCode);
        
    }

    public function selectionChangedDepartment($sender, $param)
    {
            $this->FilterEmployee->DataSource=$this->EmployeeList;
            $this->FilterEmployee->dataBind();

            if(count($this->EmployeeList)>0)
                $this->FilterEmployee->setSelectedIndex(0);

            $this->employee = new employee($this->FilterEmployee->getSelectedValue() );

            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();
            $this->Page->CallbackClient->update('list', $this->DataGrid);
            $this->Page->CallbackClient->update('timecode', $this->TimeCode);
    }


    public function getData()
    {
        $year = $this->FilterYear->getSelectedValue();
        $month = $this->FilterMonth->getSelectedValue();

        $nbreOfDay = date("t",mktime(0,0,0,$month,1,$year));

        $signed = 0.0;
        $due = 0.0;

        $todo = $this->employee->getTimeHoursDayTodo($year, $month);
        $hoursByDay = $this->employee->getHoursByDay();
        $config = $this->employee->getConfig();
        $holidays = 0.0;
        $timecode = array();

        for($i=1; $i<=$nbreOfDay;$i++)
        {
            $date = $year."-".$month."-".$i;

            $line = array();

            // date of th day
            $line['date'] = $date;

            // booking done in the day
            $booking = $this->employee->getBookings('all',$date,$date,'ASC');

            $line['sign'] = '';

            $line['remark'] = '';

            foreach($booking as $b)
            {
                if($b['internet'] == 1) $line['sign'].= "*";
                $inout = $b['inout'] == 'out' ? Prado::localize("out") : Prado::localize("in");
                $line['sign'] .= $b['time']."/".$inout."&nbsp;&nbsp;&nbsp;";

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

            if(count($booking) % 2 > 0)
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                 $line['remark'] .= "<span style=\"color:red\">".Prado::localize('Signing missing')."</span>";
            }
           

            if(!$this->employee->isBreakOk($year, $month, $i) && count($booking) % 2 == 0)
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                 $line['remark'] .= "<span style=\"color:red\">".$config['minimumBreaks']." ".Prado::localize('min. for the break are required')."</span>";
            }

            $timeWorked = $this->employee->getTimeWorked($year, $month, $i);



            $line['done'] = $timeWorked['time'] > 0 ? sprintf("%.02f",$timeWorked['time']) : '';
            

            $nwd = $this->employee->getNonWorkingDay($year, $month, $i);
            $h = $this->employee->getAbsence($year, $month, $i);

            // hours that the employee musst do
            if($this->employee->isWorking($year, $month, $i))
            {
                if($nwd == 0 && $h == 0)
                {
                    $line['todo'] = $todo > 0 ? sprintf("%.02f",$todo) : '';
                    $signed = bcadd($line['done'],$signed,4);
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
                    $tH = bcadd($tH,$timeWorked['time'],4);
                    
                    $line['done'] = $tH > 0 ? sprintf("%.02f",$tH) : '';
                    $signed = bcadd($tH,$signed,4);

                }
            }
            else
            {
                $line['todo'] = '';
                $signed = bcadd($line['done'],$signed,4);
            }


            $overtime = bcsub($line['done'],$line['todo'], 4);
            
            $line['overtime'] = $overtime > 0 || $overtime < 0 ? sprintf("%.02f",$overtime) : '';

            $nwd = $this->employee->getNonWorkingDayPeriod($year,$month,$i);
            $periods = $this->employee->getAbsencePeriod($year,$month,$i);

            if($nwd === 'allday')
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                    $line['remark'] .= Prado::localize('Non working day');
            }
            elseif($nwd === 'morning')
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                $line['remark'] .= Prado::localize('Non working day at the morning');
            }
            elseif($nwd === 'afternoon')
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                $line['remark'] .= Prado::localize('Non working day at the afternoon');
            }

            foreach($periods as $h)
            {
                if($h['period'] === 'allday' && $this->employee->isWorking($year, $month, $i))
                {
                    if($line['remark'] != '') $line['remark'].="<br>";
                    $tc = $this->employee->getTimeCode($date);
                    $line['remark'] .= $tc['name'];

                    if($tc['timecodeId'] != $this->employee->getDefaultHolidaysCounter())
                    {
                        if($tc['formatDisplay'] == 'day')
                        {
                            $timecode[$tc['name']]['value'] += 1;
                            $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                        }
                        else
                        {
                            $timecode[$tc['name']]['value'] += $todo;
                            $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                        }
                    }
                }
                elseif($h['period'] === 'morning' && $this->employee->isWorking($year, $month, $i))
                {
                    if($line['remark'] != '') $line['remark'].="<br>";
                    $tc = $this->employee->getTimeCode($date,'morning');
                    $line['remark'] .= $tc['name'].' / '.Prado::localize('morning');
                    
                    if($tc['timecodeId'] != $this->employee->getDefaultHolidaysCounter())
                    {
                        if($tc['formatDisplay'] == 'day')
                        {
                            $timecode[$tc['name']]['value'] += 1;
                            $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                        }
                        else
                        {
                            $timecode[$tc['name']]['value'] += $todo;
                            $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                        }
                    }
                }
                elseif($h['period'] === 'afternoon' && $this->employee->isWorking($year, $month, $i))
                {
                    if($line['remark'] != '') $line['remark'].="<br>";
                    $tc = $this->employee->getTimeCode($date,'afternoon');
                    $line['remark'] .= $tc['name'].' / '.Prado::localize('afternoon');

                    if($tc['timecodeId'] != $this->employee->getDefaultHolidaysCounter())
                    {
                        if($tc['formatDisplay'] == 'day')
                        {
                            $timecode[$tc['name']]['value'] += 1;
                            $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                        }
                        else
                        {
                            $timecode[$tc['name']]['value'] += $todo;
                            $timecode[$tc['name']]['formatDisplay'] = $tc['formatDisplay'] ;
                        }
                    }
                }
            }
            

            foreach($timeWorked['timecode'] as $tw)
            {
                $timecode[$tw['name']]['value'] += $tw['time'];
                $timecode[$tw['name']]['formatDisplay'] = $tw['formatDisplay'];
            }

            $res[] = $line;
        }

        $hoursForTheMonthAtX = $this->employee->getHoursMonth($year, $month);
        if($hoursForTheMonthAtX>0)
            $this->hoursDue->Text = sprintf("+%.02f",$hoursForTheMonthAtX);
        elseif($hoursForTheMonthAtX<0 || $hoursForTheMonthAtX==0)
            $this->hoursDue->Text = sprintf("%.02f",$hoursForTheMonthAtX);


        if($signed>0)
            $this->signed->Text = sprintf("+%.02f",$signed);
        elseif($signed<0 || $signed==0)
            $this->signed->Text = sprintf("%.02f",$signed);


        $balanceForTheMonth = bcsub($signed,$hoursForTheMonthAtX,4);

        if($balanceForTheMonth>0)
            $this->balanceForTheMonth->Text = sprintf("+%.02f",$balanceForTheMonth);
        elseif($balanceForTheMonth<0 || $balanceForTheMonth==0)
            $this->balanceForTheMonth->Text = sprintf("%.02f",$balanceForTheMonth);


        $lastOvertime = $this->employee->getOvertimeLastMonth($year, $month);

        if($lastOvertime>0)
            $this->lastMonth->Text = sprintf("+%.02f",$lastOvertime);
        elseif($lastOvertime<0 || $lastOvertime==0)
            $this->lastMonth->Text = sprintf("%.02f",$lastOvertime);

        $balances = bcadd($balanceForTheMonth , $lastOvertime, 4);

        if($balances>0)
            $this->balances->Text = sprintf("+%.02f",$balances);
        elseif($balances<0 || $balances==0)
            $this->balances->Text = sprintf("%.02f",$balances);


        $y = $year;
        $m = $month;
        if($m == 1)
        {
            $y--;
            $m = 12;
        }
        else
        {
            $m--;
        }

        $wt = $this->employee->getWorkingTime($y, $m);
        
        if(!$wt)
        {
            $totalYearHours100 = 0.0;
            $totalYearHoursX = $totalYearHoursX2 = 0.0;
            $vacations = 0.0;
            for($i=1; $i<=12; $i++)
            {
                $p = $this->employee->getPercentage($year,$i);
                $h = $this->employee->getHoursMonth($year, $i);
                $totalYearHours100 = bcadd($h, $totalYearHours100,2);
                $h = bcdiv(bcmul($h,$p,2),100.00,2);
                $totalYearHoursX = bcadd($h, $totalYearHoursX,2);
                $totalYearHoursX2 = $totalYearHoursX;
                $wt = $this->employee->getWorkingTime($year, $i);

                $vByMonth = bcdiv($wt['holidaysByYear'],12,4);
                $vByMonth = bcdiv(bcmul($vByMonth,$p,4),100.00,4);

                $vacations += $vByMonth;


            }

            $holidaysLastMonth = $vacations;
        }
        else
        {
            $holidaysLastMonth = $this->employee->geHolidaystLastMonth($year, $month);
        }

        if($holidaysLastMonth>0)
            $this->holidaysLastMonth->Text = sprintf("+%.02f",$holidaysLastMonth);
        elseif($holidaysLastMonth<0 || $holidaysLastMonth==0)
            $this->holidaysLastMonth->Text = sprintf("%.02f",$holidaysLastMonth);


        $defaultHolidayTimeCode = $this->employee->getDefaultHolidaysCounter();

        $holidays = $this->employee->getRequest($year,$month,$defaultHolidayTimeCode);

        if($holidays['nbre']>0)
            $this->holidaysThisMonth->Text = sprintf("-%.02f",$holidays['nbre']);
        elseif($holidays['nbre']==0)
            $this->holidaysThisMonth->Text = sprintf("%.02f",$holidays['nbre']);

        $holidaysTotal = bcsub($holidaysLastMonth, $holidays['nbre'],4);
        if($holidaysTotal>0)
            $this->holidaysTotal->Text = sprintf("+%.02f",$holidaysTotal);
        elseif($holidaysTotal<0 || $holidaysTotal==0)
            $this->holidaysTotal->Text = sprintf("%.02f",$holidaysTotal);


        $nonWorkingDay = $this->employee->getAllNonWorkingDay($year, $month);
        if($nonWorkingDay>0)
            $this->nonworkingday->Text = sprintf("+%.02f",$nonWorkingDay);
        elseif($nonWorkingDay==0)
            $this->nonworkingday->Text = sprintf("%.02f",$nonWorkingDay);

        $tc = array();

        foreach($timecode as $k=>$v)
        {
            $disp = $v['formatDisplay'] == 'day' ? Prado::localize('days') : Prado::localize('hours');
            $tc[] = array('name'=>$k, 'value'=>sprintf("%.02f $disp",$v['value']) );
        }

        $this->TimeCode->DataSource=$tc;
        $this->TimeCode->dataBind();

        return $res;
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.panel'));
    }


    public function selectionChangedYear($sender, $param)
    {
        $this->onRefresh($sender, $param);
    }

    public function selectionChangedMonth($sender, $param)
    {
        $this->onRefresh($sender, $param);
    }

    public function onRefresh($sender, $param)
    {
        $this->employee = new employee($this->FilterEmployee->getSelectedValue() );
        
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterYear', $this->FilterYear->getSelectedValue());
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterMonth', $this->FilterMonth->getSelectedValue());

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
        $this->Page->CallbackClient->update('timecode', $this->TimeCode);
    }

    public function itemCreated($sender, $param)
    {
        $item=$param->Item;

        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' )
        {

            $item->ddate->date->Value = $item->DataItem['date'];
            
        }
    }

}
?>
