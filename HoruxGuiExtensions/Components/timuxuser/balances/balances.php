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

        $this->employee = new employee($this->userId );

        if(!$this->IsPostBack)
        {
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
                $line['sign'] .= $b['time']."&nbsp;&nbsp;&nbsp;";

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

            if(!$this->employee->isBreakOk($year, $month, $i))
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                 $line['remark'] .= "<span style=\"color:red\">".$config['minimumBreaks'].Prado::localize(' min. for the break are required')."</span>";
            }

            $timeWorked = $this->employee->getTimeWorked($year, $month, $i);
            $line['done'] = $timeWorked > 0 ? sprintf("%.02f",$timeWorked) : '';
            

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
                    $t = $todo;
                    $tNwd = bcmul($todo,$nwd,4);
                    if(round($tNwd,2)==0)
                        $tNwd = 0.0;
                    $tH = 0;
                    if($h>0)
                        $tH = bcmul($todo,$h-$nwd,4);
                    if(round($tH,2)==0)
                        $tH = 0.0;

                    $t = bcsub($t,$tNwd,4);            


                    $line['todo'] = $t > 0 ? sprintf("%.02f",$t) : '';
                    $done = bcadd($tH,$timeWorked,4);
                    $line['done'] = $done > 0 ? sprintf("%.02f",$done) : '';
                    $signed = bcadd($done,$signed,4);
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
            $h = $this->employee->getAbsencePeriod($year,$month,$i);

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

            if($h === 'allday' && $this->employee->isWorking($year, $month, $i))
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                $tc = $this->employee->getTimeCode($date);
                $line['remark'] .= $tc['name'];
            }
            elseif($h === 'morning' && $this->employee->isWorking($year, $month, $i))
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                $tc = $this->employee->getTimeCode($date,'morning');
                $line['remark'] .= $tc['name'].' / '.Prado::localize('morning');
            }
            elseif($h === 'afternoon' && $this->employee->isWorking($year, $month, $i))
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                $tc = $this->employee->getTimeCode($date,'afternoon');
                $line['remark'] .= $tc['name'].' / '.Prado::localize('afternoon');
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


        $holidaysLastMonth = $this->employee->geHolidaystLastMonth($year, $month);

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


        /*$percentage = $this->employee->getPercentage($year,$month);
        $hoursForTheMonth = $this->employee->getHoursMonth($year, $month);
        $hoursForTheMonthAtX = bcdiv(bcmul($hoursForTheMonth,$percentage,2),100.00,4);


        if($hoursForTheMonthAtX>0)
            $this->hoursDue->Text = sprintf("+%.02f",$hoursForTheMonthAtX);
        elseif($hoursForTheMonthAtX<0 || $hoursForTheMonthAtX==0)
            $this->hoursDue->Text = sprintf("%.02f",$hoursForTheMonthAtX);

        $balanceForTheMonth = bcsub($signed,$due,4);

        if($balanceForTheMonth>0)
            $this->balanceForTheMonth->Text = sprintf("+%.02f",$balanceForTheMonth);
        elseif($balanceForTheMonth<0 || $balanceForTheMonth==0)
            $this->balanceForTheMonth->Text = sprintf("%.02f",$balanceForTheMonth);


        

        $balances = bcadd($balanceForTheMonth , $lastOvertime, 4);

        if($balances>0)
            $this->balances->Text = sprintf("+%.02f",$balances);
        elseif($balances<0 || $balances==0)
            $this->balances->Text = sprintf("%.02f",$balances);

        $holidaysLastMonth = $this->employee->geHolidaystLastMonth($year, $month);

        if($holidaysLastMonth>0)
            $this->holidaysLastMonth->Text = sprintf("+%.02f",$holidaysLastMonth);
        elseif($holidaysLastMonth<0 || $holidaysLastMonth==0)
            $this->holidaysLastMonth->Text = sprintf("%.02f",$holidaysLastMonth);

        $allRequest = $this->employee->getRequest($year, $month);

        $defaultHolidayTimeCode = $this->employee->getDefaultHolidaysCounter();
        if($allRequest[$defaultHolidayTimeCode]['nbre']>0)
            $this->holidaysThisMonth->Text = sprintf("-%.02f",$allRequest[$defaultHolidayTimeCode]['nbre']);
        elseif($allRequest[$defaultHolidayTimeCode]['nbre']==0)
            $this->holidaysThisMonth->Text = sprintf("%.02f",$allRequest[$defaultHolidayTimeCode]['nbre']);

        $holidaysTotal = bcsub($holidaysLastMonth, $allRequest[$defaultHolidayTimeCode]['nbre'],4);
        if($holidaysTotal>0)
            $this->holidaysTotal->Text = sprintf("+%.02f",$holidaysTotal);
        elseif($holidaysTotal<0 || $holidaysTotal==0)
            $this->holidaysTotal->Text = sprintf("%.02f",$holidaysTotal);


        $nonWorkingDay = $this->employee->getNonWorkingDay($year, $month);
        if($nonWorkingDay>0)
            $this->nonworkingday->Text = sprintf("+%.02f",$nonWorkingDay);
        elseif($nonWorkingDay==0)
            $this->nonworkingday->Text = sprintf("%.02f",$nonWorkingDay);*/


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
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterYear', $this->FilterYear->getSelectedValue());
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterMonth', $this->FilterMonth->getSelectedValue());

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
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
