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
            $cmd = $this->db->createCommand( "SELECT CONCAT(name, ' ', firstname) AS Text, id AS Value FROM hr_user WHERE $id department=$department AND name!='??'");
        else
            $cmd = $this->db->createCommand( "SELECT CONCAT(name, ' ', firstname) AS Text, id AS Value FROM hr_user WHERE $id name!='??'");

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

        $this->hoursBalance->Text = sprintf("%.02f",$this->employee->getOvertimeMonth($year-1, 12));

        $this->daysVacationLastYear->Text = sprintf("%.02f",$this->employee->geHolidaystMonth($year-1,12));

        $totalYearHours100 = 0.0;
        $totalYearHoursX = $totalYearHoursX2 = 0.0;
        $vacations = 0.0;
        for($i=1; $i<=12; $i++)
        {
            $p = $this->employee->getPercentage($year,$i);
            $h = $this->employee->getHoursMonth($year, $i, false);
            $totalYearHours100 = bcadd($h, $totalYearHours100,2);
            $h = bcdiv(bcmul($h,$p,2),100.00,2);
            $totalYearHoursX = bcadd($h, $totalYearHoursX,2);
            $totalYearHoursX2 = $totalYearHoursX;
            $wt = $this->employee->getWorkingTime($year, $i);
            
            $vByMonth = bcdiv($wt['holidaysByYear'],12,4);
            $vByMonth = bcdiv(bcmul($vByMonth,$p,4),100.00,4);

            $vacations += $vByMonth;
        }



        $this->daysVacation->Text = sprintf("%.02f %s / %.02f ".Prado::localize('weeks'),$vacations,Prado::localize('days'), bcdiv($vacations, $this->employee->getDaysByWeek(),4));
        $this->totalVacation->Text = sprintf("%.02f",$vacations+$this->daysVacationLastYear->Text);

        $hoursWorked = 0.0;
        $nbreHolidaysDay = 0.0;
        $nbreHolidaysHour = 0.0;
        $nbreAbsenceDay = 0.0;
        $nbreAbsenceHour = 0.0;
        $totalHours = 0.0;


        for($i=1; $i<=12; $i++)
        {
            $month = new DateFormat($app->getCulture());
            $months[$i]['month'] = $month->format("1.$i.$year", "MMMM");
            
            $months[$i]['occupancy'] = $this->employee->getPercentage($year,$i);

            $HoursMonth = $this->employee->getHoursMonth($year, $i, false);
            $HoursMonthX = bcdiv(bcmul($HoursMonth, $this->employee->getPercentage($year,$i),2),100.00,4);
            
            $months[$i]['hours100'] = sprintf("%.02f",$HoursMonth);
            $months[$i]['hoursX'] = sprintf("%.02f",$HoursMonthX);

            $monthTimeWorked = $this->employee->getMonthTimeWorked($year,$i);

            $months[$i]['hoursWorked'] = sprintf("%.02f",$monthTimeWorked['done']) ;
            $hoursWorked  = bcadd($hoursWorked,$months[$i]['hoursWorked'],2);

            $defaultHolidayTimeCode = $this->employee->getDefaultHolidaysCounter();
            $holidays = $this->employee->getRequest($year,$i,$defaultHolidayTimeCode);
            $months[$i]['nbreHolidaysDay'] = sprintf("%.02f",$holidays['nbre']);

            $nbreHolidaysDay = bcadd($nbreHolidaysDay,$months[$i]['nbreHolidaysDay'],2);

            $months[$i]['nbreHolidaysHour'] = sprintf("%.02f",bcmul($holidays['nbre'], $this->employee->getTimeHoursDayTodo($year,$i),4));
            $nbreHolidaysHour = bcadd($nbreHolidaysHour,$months[$i]['nbreHolidaysHour'],2);

            if($i==1)
                $months[$i]['holidayBalance'] = sprintf("%.02f",$this->totalVacation->Text-$months[$i]['nbreHolidaysDay']);
            else
                $months[$i]['holidayBalance'] = sprintf("%.02f",$months[$i-1]['holidayBalance']-$months[$i]['nbreHolidaysDay']);

            $leaveRequest = $this->employee->getLeaveRequest($year, $i);
            $months[$i]['nbreLeaveDay'] = sprintf("%.02f",$leaveRequest['nbre']);
            $months[$i]['nbreLeaveHour'] = sprintf("%.02f",bcmul($leaveRequest['nbre'], $this->employee->getTimeHoursDayTodo($year,$i),4));

            $absence = $this->employee->getAbsenceMonth($year, $i);
            if($this->employee->getTimeHoursDayTodo($year,$i)>0)
            {
                $aDay = bcdiv($monthTimeWorked['absence'],$this->employee->getTimeHoursDayTodo($year,$i),4);
                $absence = bcadd($absence,$aDay,4);
            }
            else
            {
                $absence = 0;
            }

            $months[$i]['nbreAbsenceDay'] = sprintf("%.02f",$absence);

            $nbreAbsenceDay = bcadd($nbreAbsenceDay,$absence,4);
            $months[$i]['nbreAbsenceHour'] = sprintf("%.02f",bcmul($absence, $this->employee->getTimeHoursDayTodo($year,$i),4));
            $nbreAbsenceHour = sprintf("%.02f",bcadd($nbreAbsenceHour,bcmul($absence, $this->employee->getTimeHoursDayTodo($year,$i),4),4));
            $months[$i]['totalHours'] = sprintf("%.02f",$months[$i]['hoursWorked'] + $months[$i]['nbreHolidaysHour'] + $months[$i]['nbreAbsenceHour'] + $months[$i]['nbreLeaveHour']);
            $totalHours = bcadd($totalHours,$months[$i]['totalHours'],2);

            $months[$i]['monthBalance'] = bcsub($months[$i]['totalHours'],$months[$i]['hoursX'],2);

            $totalYearHoursX = bcsub($totalYearHoursX, $months[$i]['totalHours'],2);
            $months[$i]['hoursDueYear'] = $totalYearHoursX;

            $months[$i]['hoursDueYearSubHolidays'] = bcsub($months[$i]['hoursDueYear'], bcmul($months[$i]['holidayBalance'],$this->employee->getHoursByDay(),2),2);

            if(12-$i > 0)
                $months[$i]['average'] = bcdiv($months[$i]['hoursDueYearSubHolidays'],(12-$i),2);
            else
                $months[$i]['average'] = "";
        }

        $months[13] = array();

        $months[14]['month'] = Prado::localize('Totals');
        $months[14]['hours100'] = sprintf("%.02f",$totalYearHours100);
        $months[14]['hoursX'] = sprintf("%.02f",$totalYearHoursX2);
        $months[14]['hoursWorked'] = sprintf("%.02f",$hoursWorked);
        $months[14]['nbreHolidaysDay'] = sprintf("%.02f",$nbreHolidaysDay);
        $months[14]['nbreHolidaysHour'] = sprintf("%.02f",$nbreHolidaysHour);
        $months[14]['nbreAbsenceDay'] = sprintf("%.02f",$nbreAbsenceDay);
        $months[14]['nbreAbsenceHour'] = sprintf("%.02f",$nbreAbsenceHour);
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

        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterYear', $this->FilterYear->getSelectedValue());

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
