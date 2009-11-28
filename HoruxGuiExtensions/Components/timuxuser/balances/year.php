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


    public function getData()
    {
        $year = $this->FilterYear->getSelectedValue();

        $months = array();
        $app = $this->getApplication()->getGlobalization();

        $this->hoursBalance->Text = 4.5;// sprintf("%.02f",$this->employee->getOvertimeMonth($year-1, 12));

        $this->daysVacationLastYear->Text = '32';//sprintf("%.02f",$this->employee->geHolidaystMonth($year-1,12));

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



        $this->daysVacation->Text = sprintf("%.02f",$vacations);
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
            $months[$i]['hours100'] = $this->employee->getHoursMonth($year, $i);
            $months[$i]['hoursX'] = bcdiv(bcmul($months[$i]['hours100'],$months[$i]['occupancy'],2),100.00,2);
            $months[$i]['hoursWorked'] = sprintf("%.02f",/*$this->employee->getTimeWorkedMonth($year,$i)*/0.0) ;
            $hoursWorked  = bcadd($hoursWorked,$months[$i]['hoursWorked'],2);
            $request = $this->employee->getRequest($year,$i,3);
            $nbreOfDay = $request[$this->employee->getDefaultHolidaysCounter()]['nbre'];
            $months[$i]['nbreHolidaysDay'] = sprintf("%.02f",$nbreOfDay);
            $nbreHolidaysDay = bcadd($nbreHolidaysDay,$months[$i]['nbreHolidaysDay'],2);
            $months[$i]['nbreHolidaysHour'] = sprintf("%.02f",bcmul($nbreOfDay, $this->employee->getHoursByDay()));
            $nbreHolidaysHour = bcadd($nbreHolidaysHour,$months[$i]['nbreHolidaysHour'],2);
            $months[$i]['holidayBalance'] = 0;//$this->employee->geHolidaystMonth($year,$i);

            //@todo put the right timecode
            $months[$i]['nbreAbsenceDay'] = sprintf("%.02f",$request[5]['nbre']);
            $nbreAbsenceDay = bcadd($nbreAbsenceDay,$months[$i]['nbreAbsenceDay'],2);
            $months[$i]['nbreAbsenceHour'] = sprintf("%.02f",bcmul($request[5]['nbre'], $this->employee->getHoursByDay()));
            $nbreAbsenceHour = bcadd($nbreAbsenceHour,$months[$i]['nbreAbsenceHour'],2);
            $months[$i]['totalHours'] = $months[$i]['hoursWorked'] + $months[$i]['nbreHolidaysHour'] + $months[$i]['nbreAbsenceHour'];
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
        $months[14]['hours100'] = $totalYearHours100;
        $months[14]['hoursX'] = $totalYearHoursX2 ;
        $months[14]['hoursWorked'] = $hoursWorked;
        $months[14]['nbreHolidaysDay'] = $nbreHolidaysDay;
        $months[14]['nbreHolidaysHour'] = $nbreHolidaysHour;
        $months[14]['nbreAbsenceDay'] = $nbreAbsenceDay;
        $months[14]['nbreAbsenceHour'] = $nbreAbsenceHour;
        $months[14]['totalHours'] = $totalHours;

        $months[15] = array();

        $months[16]['month'] = Prado::localize('Total do be done');
        $months[16]['hoursX'] = $totalYearHoursX2-$this->hoursBalance->Text;

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
