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

$param = Prado::getApplication()->getParameters();
$computation = $param['computation'];

Prado::using('horux.pages.components.timuxuser.'.$computation);

Prado::using('System.I18N.core.DateFormat');


class year extends PageList
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
            $cmd=$this->db->createCommand("SELECT t.startDate FROM hr_timux_workingtime AS t ORDER BY t.startDate LIMIT 0,1");
            $data = $cmd->query();
            $data = $data->readAll();

            $year = date("Y");
            if(count($data)>0)
            {
                $year = explode("-",$data[0]['startDate']);
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

            if(Prado::getApplication()->getSession()->contains($this->getApplication()->getService()->getRequestedPagePath().'FilterYear'))
            {
                $FilterYear= $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterYear'];
            }
            else
            {
                $FilterYear= date('Y');
            }

            $FilterEmployee = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterEmployee'];
            $FilterDepartment = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterDepartment'];



            $this->FilterDepartment->DataSource=$this->DepartmentList;
            $this->FilterDepartment->dataBind();

            if($FilterDepartment)
                $this->FilterDepartment->setSelectedValue($FilterDepartment);
            else
                $this->FilterDepartment->setSelectedIndex(0);

            $this->FilterEmployee->DataSource=$this->EmployeeList;
            $this->FilterEmployee->dataBind();


            if($FilterEmployee)
            {
                $this->employee = new employee($FilterEmployee );
                $this->FilterEmployee->setSelectedValue($FilterEmployee);
            }
            else
            {
                $this->employee = new employee($this->userId );
                $this->FilterEmployee->setSelectedValue($this->userId);
            }



            if($FilterYear)
                $this->FilterYear->setSelectedValue($FilterYear);


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

        if($department>0)
            $cmd = $this->db->createCommand( "SELECT CONCAT(name, ' ', firstname) AS Text, id AS Value FROM hr_user WHERE $id department=$department AND name!='??' ORDER BY name, firstname");
        else
            $cmd = $this->db->createCommand( "SELECT CONCAT(name, ' ', firstname) AS Text, id AS Value FROM hr_user WHERE $id name!='??' ORDER BY name, firstname");

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

            $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterEmployee'] = $this->FilterEmployee->getSelectedValue();

            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();
            $this->Page->CallbackClient->update('list', $this->DataGrid);
    }

    public function selectionChangedDepartment($sender, $param)
    {
            $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterDepartment'] = $this->FilterDepartment->getSelectedValue();

            $this->FilterEmployee->DataSource=$this->EmployeeList;
            $this->FilterEmployee->dataBind();

            if(count($this->EmployeeList)>0)
                $this->FilterEmployee->setSelectedIndex(0);

            $this->employee = new employee($this->FilterEmployee->getSelectedValue() );
            $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterEmployee'] = $this->FilterEmployee->getSelectedValue();

            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();
            $this->Page->CallbackClient->update('list', $this->DataGrid);
    }

    protected function br2nl($string)
    {
        return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
    }

    protected function onPrint()
    {
        parent::onPrint();
        $app = $this->getApplication()->getGlobalization();

        $this->pdf->AddPage('L');

        $data = $this->getData();

        $this->pdf->SetFont('Arial','',9);

        $this->pdf->Ln(5);

        $this->pdf->Cell(80,5,utf8_decode(Prado::localize('Employee'))." :",0,0,'L');
        $this->pdf->Cell(0,5,utf8_decode($this->employee->getFullName()),0,1,'L');

        $this->pdf->Cell(80,5,utf8_decode(Prado::localize('Department'))." :",0,0,'L');
        $this->pdf->Cell(0,5,utf8_decode($this->employee->getDepartment()),0,1,'L');

        $this->pdf->Cell(80,5,utf8_decode(Prado::localize('Year'))." :",0,0,'L');
        $this->pdf->Cell(0,5,$this->Request['f3'],0,1,'L');

        $this->pdf->Cell(80,5,utf8_decode(Prado::localize('Number of holidays'))." :",0,0,'L');
        $this->pdf->Cell(0,5,$this->daysVacation->Text,0,1,'L');

        $this->pdf->Cell(80,5,utf8_decode(Prado::localize('Number of days of holidays for the last year'))." :",0,0,'L');
        $this->pdf->Cell(0,5,$this->daysVacationLastYear->Text,0,1,'L');

        $this->pdf->Cell(80,5,utf8_decode(str_replace("<br/>", " ",Prado::localize('Total of holidays (days)')))." :",0,0,'L');
        $this->pdf->Cell(0,5,$this->totalVacation->Text,0,1,'L');

        $this->pdf->Cell(80,5,utf8_decode(Prado::localize('Hours balance of the last year'))." :",0,0,'L');
        $this->pdf->Cell(0,5,$this->hoursBalance->Text,0,1,'L');

        $date = new DateFormat($app->getCulture());
        $date = $date->format('1-'.$this->Request['f4']."-".$this->Request['f3'], "P");
        $date = explode(" ", $date);
        $date = $date[2]." ".$date[3];

        $this->pdf->Ln(10);


        $header = array(utf8_decode(Prado::localize("Month")),
            utf8_decode($this->br2nl(Prado::localize("Occupancy rate in %"))),
            utf8_decode($this->br2nl(Prado::localize("Hours at 100%"))),
            utf8_decode($this->br2nl(Prado::localize("Hours at X%"))),
            utf8_decode($this->br2nl(Prado::localize("Hours worked"))),
            utf8_decode($this->br2nl(Prado::localize("Nbre of holidays (day)"))),
            utf8_decode($this->br2nl(Prado::localize("Nbre of holidays (hours)"))),
            utf8_decode($this->br2nl(Prado::localize("Nbre of leave (day)"))),
            utf8_decode($this->br2nl(Prado::localize("Nbre of leave (hours)"))),
            utf8_decode($this->br2nl(Prado::localize("Holidays balance"))),
            utf8_decode($this->br2nl(Prado::localize("Nbre of absence (day)"))),
            utf8_decode($this->br2nl(Prado::localize("Nbre of absence (hours)"))),
            utf8_decode($this->br2nl(Prado::localize("Total of hours"))),
            utf8_decode($this->br2nl(Prado::localize("Balance for the month"))),
            utf8_decode($this->br2nl(Prado::localize("Hours due for the year"))),
            utf8_decode($this->br2nl(Prado::localize("Hours due for the year / without holidays"))),
            utf8_decode($this->br2nl(Prado::localize("Average per month / end of year"))),
        );

        $this->pdf->SetFillColor(124,124,124);
        $this->pdf->SetTextColor(255);
        $this->pdf->SetDrawColor(255);
        $this->pdf->SetLineWidth(.3);
        $this->pdf->SetFont('','B');
        $this->pdf->SetFont('Arial','',6);
        $w=array(16.5,16.5,16.5,16.5,16.5,16.5,16.5,16.5,16.5,16.5,16.5,16.5,16.5,16.5,16.5,16.5,16.5);
        for($i=0;$i<count($header);$i++)
            $this->pdf->CellExt($w[$i],10,$header[$i],1,0,'C',1);
        $this->pdf->Ln();
        $this->pdf->SetFillColor(215,215,215);
        $this->pdf->SetTextColor(0);
        $this->pdf->SetFont('');

        $fill=false;

        $this->pdf->SetFont('courier','',8);

        foreach($data as $d)
        {

            $height = 5;

            $this->pdf->CellExt($w[0],$height,utf8_decode($d['month']),'LR',0,'L',$fill);
            $this->pdf->CellExt($w[1],$height,$d['occupancy'],'LR',0,'L',$fill);
            $this->pdf->CellExt($w[2],$height,$d['hours100'],'LR',0,'L',$fill);
            $this->pdf->CellExt($w[3],$height,$d['hoursX'],'LR',0,'L',$fill);
            $this->pdf->CellExt($w[4],$height,$d['hoursWorked'],'LR',0,'L',$fill);
            $this->pdf->CellExt($w[5],$height,$d['nbreHolidaysDay'],'LR',0,'L',$fill);
            $this->pdf->CellExt($w[6],$height,$d['nbreHolidaysHour'],'LR',0,'L',$fill);
            $this->pdf->CellExt($w[7],$height,$d['nbreLeaveDay'],'LR',0,'L',$fill);
            $this->pdf->CellExt($w[8],$height,$d['nbreLeaveHour'],'LR',0,'L',$fill);
            $this->pdf->CellExt($w[9],$height,$d['holidayBalance'],'LR',0,'L',$fill);
            $this->pdf->CellExt($w[10],$height,$d['nbreAbsenceDay'],'LR',0,'L',$fill);
            $this->pdf->CellExt($w[11],$height,$d['nbreAbsenceHour'],'LR',0,'L',$fill);
            $this->pdf->CellExt($w[12],$height,$d['totalHours'],'LR',0,'L',$fill);
            $this->pdf->CellExt($w[13],$height,$d['monthBalance'],'LR',0,'L',$fill);
            $this->pdf->CellExt($w[14],$height,$d['hoursDueYear'],'LR',0,'L',$fill);
            $this->pdf->CellExt($w[15],$height,$d['hoursDueYearSubHolidays'],'LR',0,'L',$fill);
            $this->pdf->CellExt($w[16],$height,$d['average'],'LR',0,'L',$fill);
            $this->pdf->Ln();
            $fill=!$fill;
        }

        $this->pdf->render();

    }


    public function getData()
    {
        if(isset($this->Request['f1']))
        {
            $year = $this->Request['f3'];
            $this->employee = new employee($this->Request['f2'] );
        }
        else
        {
            $year = $this->FilterYear->getSelectedValue();
        }

        $months = array();
        $app = $this->getApplication()->getGlobalization();

        $nHolidays = 0;
        $totalYearHoursX = 0;
        for($i=1; $i<=12; $i++) {
            $wt = $this->employee->getWorkingTime(1, $i, $year);
            $monthTemp = 0;
            if($wt) {
                $nHolidays = bcadd($nHolidays, bcdiv($wt['holidaysByYear'],12,4),4);

                $nbreOfDay = date("t",mktime(0,0,0,$i,1,$year));

                for($day=1; $day<=$nbreOfDay;$day++) {
                    $todo = $this->employee->getDayTodo($day, $i, $year);
                    if($todo)
                        $totalYearHoursX = bcadd($totalYearHoursX, $todo,4);
                }

            }

        }
       

        $this->daysVacation->Text = sprintf("%.02f %s / %.02f ".Prado::localize('weeks'),$nHolidays,Prado::localize('days'),bcdiv($nHolidays,5,4));

        $this->daysVacationLastYear->Text = sprintf("%.02f",$this->employee->geHolidaystMonth($year-1,12));

        $this->totalVacation->Text = sprintf("%.02f",bcadd($nHolidays,$this->daysVacationLastYear->Text,4));

        $this->hoursBalance->Text = sprintf("%.02f",$this->employee->getOvertimeLastYear($year-1));

        $totalYearHours100 = 0;
        $totalYearHoursX2 = 0;
        $hoursWorked = 0;
        $nbreHolidaysDay = 0;
        $nbreHolidaysHour = 0;
        $nbreAbsenceDay2 = 0;
        $nbreAbsenceHour2 = 0;
        $totalHours = 0;



        for($month=1; $month<=12; $month++)
        {
            $absentHoursComp = 0;

            $_month = new DateFormat($app->getCulture());
            $months[$month]['month'] = $_month->format("1.$month.$year", "MMMM");

            $months[$month]['occupancy'] = $this->employee->getPercentage(1, $month, $year);

            $nbreOfDay = date("t",mktime(0,0,0,$month,1,$year));

            $HoursMonth = 0;
            $HoursMonthAtX = 0;
            $HoursDone = 0;
            $nWorkingDay = 0;
            

            for($day=1; $day<=$nbreOfDay;$day++) {

                $dayTodo = $this->employee->getDayTodo($day, $month, $year);
                $HoursMonth = bcadd($HoursMonth,$dayTodo,4);
                $tmp = $this->employee->getDayDone($day, $month, $year);

                if($tmp['compensation'] > 0) {
                    $absentHoursComp = bcadd($absentHoursComp, $tmp['compensation'],4);
                }

                $HoursDone = bcadd($HoursDone,$tmp['done'],4);

                if($dayTodo)
                    $nWorkingDay++;
                
            }

            $totalHourWorked = $HoursDone;



            if($HoursMonth>0) {
                if($months[$month]['occupancy']>0)
                    $months[$month]['hours100'] = sprintf("%.02f",bcdiv(bcmul($HoursMonth, 100.0,4),$months[$month]['occupancy'],4),4);
                else
                    $months[$month]['hours100'] = sprintf("%.02f", 0);
                
                $totalYearHours100 = bcadd($months[$month]['hours100'], $totalYearHours100,2);
            }

            $months[$month]['hoursX'] = sprintf("%.02f",$HoursMonth);
            $totalYearHoursX2 = bcadd($months[$month]['hoursX'], $totalYearHoursX2,2);


            $defaultHolidayTimeCode = $this->employee->getDefaultHolidaysCounter();
            $holidays = $this->employee->getRequest($year,$month,$defaultHolidayTimeCode);
            $months[$month]['nbreHolidaysDay'] = sprintf("%.02f",$holidays['nbre']);
            $nbreHolidaysDay = bcadd($nbreHolidaysDay,$months[$month]['nbreHolidaysDay'],2);

            if($nWorkingDay>0) {
                $holidaysHour = bcmul(bcdiv($HoursMonth,$nWorkingDay,4),$holidays['nbre'],4);
                $nbreHolidaysHour = bcadd($nbreHolidaysHour,$holidaysHour,2);
            }
            $months[$month]['nbreHolidaysHour'] = sprintf("%.02f",$holidaysHour);

            $totalHourWorked = bcsub($totalHourWorked, $holidaysHour,4);


            if($month==1)
                $months[$month]['holidayBalance'] = sprintf("%.02f",$this->totalVacation->Text-$months[$month]['nbreHolidaysDay']);
            else
                $months[$month]['holidayBalance'] = sprintf("%.02f",$months[$month-1]['holidayBalance']-$months[$month]['nbreHolidaysDay']);

            $nbreLeaveDay = $this->employee->getMonthLeaveRequest($month, $year);
            $months[$month]['nbreLeaveDay'] = sprintf("%.02f",$nbreLeaveDay);

            if($nWorkingDay>0)
                $nbreLeaveHour = bcmul(bcdiv($HoursMonth,$nWorkingDay,4),$nbreLeaveDay,4);
            
            $months[$month]['nbreLeaveHour'] = sprintf("%.02f",$nbreLeaveHour);
            $totalHourWorked = bcsub($totalHourWorked,$nbreLeaveHour,4);


            $nbreAbsenceDay = $this->employee->getMonthAbsentRequest($month, $year);

            if($this->employee->getHoursByDay($month, $year)>0)
                $nbreAbsenceDay = bcadd($nbreAbsenceDay, bcdiv($absentHoursComp,$this->employee->getHoursByDay($month, $year),4),4);

            $months[$month]['nbreAbsenceDay'] = sprintf("%.02f",$nbreAbsenceDay);
            $nbreAbsenceDay2 = bcadd($nbreAbsenceDay2,$nbreAbsenceDay ,2);

            if($nWorkingDay>0) {
                $nbreAbsenceHour = bcmul(bcdiv($HoursMonth,$nWorkingDay,4),$nbreAbsenceDay,4);

                $nbreAbsenceHour2 = bcadd($nbreAbsenceHour,$nbreAbsenceHour2,2);
            }
            
            $months[$month]['nbreAbsenceHour'] = sprintf("%.02f",$nbreAbsenceHour);
            $totalHourWorked = bcsub($totalHourWorked, $nbreAbsenceHour,4);


            $months[$month]['hoursWorked'] = sprintf("%.02f", $totalHourWorked ) ;
            $hoursWorked = bcadd($hoursWorked,$months[$month]['hoursWorked'],2);


            $months[$month]['totalHours'] = sprintf("%.02f",$HoursDone);
            $totalHours = bcadd($totalHours, $HoursDone, 2);

            $months[$month]['monthBalance'] = bcsub($months[$month]['totalHours'],$months[$month]['hoursX'],2);

            $totalYearHoursX = bcsub($totalYearHoursX, $months[$month]['totalHours'],2);
            $months[$month]['hoursDueYear'] = sprintf("%.02f",$totalYearHoursX);

            $hoursDueYearSubHolidays = 0;
            if($nWorkingDay>0)
                $hoursDueYearSubHolidays = bcmul(bcdiv($HoursMonth,$nWorkingDay,4),$months[$month]['holidayBalance'],4);
            $hoursDueYearSubHolidays = bcsub($totalYearHoursX, $hoursDueYearSubHolidays,2);
            $months[$month]['hoursDueYearSubHolidays'] = sprintf("%.02f",$hoursDueYearSubHolidays);

            if(12-$month > 0)
                $months[$month]['average'] = bcdiv($months[$month]['hoursDueYearSubHolidays'],(12-$month),2);
            else
                $months[$month]['average'] = "";

        }

        $months[13] = array();

        $months[14]['month'] = Prado::localize('Totals');
        $months[14]['hours100'] = sprintf("%.02f",$totalYearHours100);
        $months[14]['hoursX'] = sprintf("%.02f",$totalYearHoursX2);
        $months[14]['hoursWorked'] = sprintf("%.02f",$hoursWorked);
        $months[14]['nbreHolidaysDay'] = sprintf("%.02f",$nbreHolidaysDay);
        $months[14]['nbreHolidaysHour'] = sprintf("%.02f",$nbreHolidaysHour);
        $months[14]['nbreAbsenceDay'] = sprintf("%.02f",$nbreAbsenceDay2);
        $months[14]['nbreAbsenceHour'] = sprintf("%.02f",$nbreAbsenceHour2);
        $months[14]['totalHours'] = sprintf("%.02f",$totalHours);

        $months[15] = array();

        $months[16]['month'] = Prado::localize('Total do be done');
        $months[16]['hoursX'] = sprintf("%.02f",$totalYearHoursX2-$this->hoursBalance->Text);


        return $months;
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.panel'));
    }


    public function selectionChangedYear($sender, $param)
    {
        $this->onRefresh($sender, $param);
    }


    public function onRefresh($sender, $param)
    {
        $this->employee = new employee($this->FilterEmployee->getSelectedValue() );

        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterYear'] = $this->FilterYear->getSelectedValue();

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }

    public function itemCreated($sender, $param)
    {
        $item=$param->Item;

        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' )
        {

        }
    }

}
?>
