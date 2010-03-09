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

    protected $signedValue = 0.0;
    protected $timecode = array();

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

            if(Prado::getApplication()->getSession()->contains($this->getApplication()->getService()->getRequestedPagePath().'FilterYear'))
            {
                $FilterYear= $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterYear'];
                $FilterMonth = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterMonth'];
            }
            else
            {
                $FilterYear= date('Y');
                $FilterMonth = date('n');
            }

            $FilterEmployee = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterEmployee'];
            $FilterDepartment = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterDepartment'];

            $this->FilterDepartment->DataSource=$this->DepartmentList;
            $this->FilterDepartment->dataBind();

            if($FilterDepartment)
            {
                $this->FilterDepartment->setSelectedValue($FilterDepartment);
            }
            else
            {
                $this->FilterDepartment->setSelectedIndex(0);
            }

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
            $this->Page->CallbackClient->update('timecode', $this->TimeCode);
        
    }

    public function selectionChangedDepartment($sender, $param)
    {
            $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterDepartment'] = $this->FilterDepartment->getSelectedValue();

            $this->FilterEmployee->DataSource=$this->EmployeeList;
            $this->FilterEmployee->dataBind();

            if(count($this->EmployeeList)>0)
            {
                $this->FilterEmployee->setSelectedIndex(0);
            }

            $this->employee = new employee($this->FilterEmployee->getSelectedValue() );
            $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterEmployee'] = $this->FilterEmployee->getSelectedValue();


            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();
            $this->Page->CallbackClient->update('list', $this->DataGrid);
            $this->Page->CallbackClient->update('timecode', $this->TimeCode);

    }


    protected function onPrint()
    {
        $this->employee = new employee($this->Request['f2'] );
        $this->employee->generatePDF($this->Request['f3'], $this->Request['f4']);
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

    public function getData($isPrint=false)
    {
        if(isset($this->Request['f1']))
        {
            $year = $this->Request['f3'];
            $month = $this->Request['f4'];
            $this->employee = new employee($this->Request['f2'] );
        }
        else
        {
            $year = $this->FilterYear->getSelectedValue();
            $month = $this->FilterMonth->getSelectedValue();
        }

        $res = $this->employee->getMonthReportData($year, $month, false);


        // get the hours that the employee should worked for the month
        $hoursForTheMonthAtX = $this->employee->getHoursMonth($year, $month);

        // display the value
        if($hoursForTheMonthAtX>0)
            $this->hoursDue->Text = sprintf("+%.02f",$hoursForTheMonthAtX);
        elseif($hoursForTheMonthAtX<0 || $hoursForTheMonthAtX==0)
            $this->hoursDue->Text = sprintf("%.02f",$hoursForTheMonthAtX);

        // display the hours that the employee has worked
        //$this->signedValue = $signed;
        $this->signedValue = $this->employee->signedValue;
        if($this->signedValue>0)
            $this->signed->Text = sprintf("+%.02f",$this->signedValue);
        elseif($this->signedValue<0 || $this->signedValue==0)
            $this->signed->Text = sprintf("%.02f",$this->signedValue);


        // compute the balance (overtime) for the month
        $balanceForTheMonth = bcsub($this->signedValue,$hoursForTheMonthAtX,4);

        if($balanceForTheMonth>0)
            $this->balanceForTheMonth->Text = sprintf("+%.02f",$balanceForTheMonth);
        elseif($balanceForTheMonth<0 || $balanceForTheMonth==0)
            $this->balanceForTheMonth->Text = sprintf("%.02f",$balanceForTheMonth);

        // get the overtime from the last month
        $lastOvertime = $this->employee->getOvertimeLastMonth($year, $month);
        
        // display the last overtime
        if($lastOvertime>0)
            $this->lastMonth->Text = sprintf("+%.02f",$lastOvertime);
        elseif($lastOvertime<0 || $lastOvertime==0)
            $this->lastMonth->Text = sprintf("%.02f",$lastOvertime);

        // compute the overtime balance
        $balances = bcadd($balanceForTheMonth , $lastOvertime, 4);

        // display the value
        if($balances>0)
            $this->balances->Text = sprintf("+%.02f",$balances);
        elseif($balances<0 || $balances==0)
            $this->balances->Text = sprintf("%.02f",$balances);


        //Balance of holiday fot the last year
        $this->balanceHolidaysLastYear->Text = sprintf("%.02f",$this->employee->geHolidaystMonth($year-1,12));


        //Nbre of holiday that the employee has for the year
        $nvy = $this->employee->geHolidaystForTheYear($year, $month);

        for($i=1; $i<$month;$i++)
        {
            $nv = $this->employee->getRequest($year, $i, $this->employee->getDefaultHolidaysCounter());
            $nvy -= $nv['nbre'];
        }


        $this->holidayForTheYear->Text = sprintf("%.02f",$nvy);

        // compute the holdiday for the last month
        $holidaysLastMonth = $this->holidayForTheYear->Text + $this->balanceHolidaysLastYear->Text;

        // display the value
        if($holidaysLastMonth>0)
            $this->holidaysLastMonth->Text = sprintf("+%.02f",$holidaysLastMonth);
        elseif($holidaysLastMonth<0 || $holidaysLastMonth==0)
            $this->holidaysLastMonth->Text = sprintf("%.02f",$holidaysLastMonth);

        // get the holiday for this month
        $defaultHolidayTimeCode = $this->employee->getDefaultHolidaysCounter();
        $holidays = $this->employee->getRequest($year,$month,$defaultHolidayTimeCode);

        if($holidays['nbre']>0)
            $this->holidaysThisMonth->Text = sprintf("-%.02f",$holidays['nbre']);
        elseif($holidays['nbre']==0)
            $this->holidaysThisMonth->Text = sprintf("%.02f",$holidays['nbre']);

        // balance of the last month and the current month
        $holidaysTotal = bcsub($holidaysLastMonth, $holidays['nbre'],4);
        if($holidaysTotal>0)
            $this->holidaysTotal->Text = sprintf("+%.02f",$holidaysTotal);
        elseif($holidaysTotal<0 || $holidaysTotal==0)
            $this->holidaysTotal->Text = sprintf("%.02f",$holidaysTotal);

        // number of n.w.d. for this month
        $nonWorkingDay = $this->employee->getAllNonWorkingDay($year, $month);
        if($nonWorkingDay>0)
            $this->nonworkingday->Text = sprintf("%.02f",$nonWorkingDay);
        elseif($nonWorkingDay==0)
            $this->nonworkingday->Text = sprintf("%.02f",$nonWorkingDay);


        // number of n.w.d. until the end of the year
        $this->nonworkingdayendofyear->Text = sprintf("%.02f",$this->employee->getNonWorkingDayEndOfYear($year, $month));
        // display the time code list
        $tc = array();
        //$this->timecode = $timecode;
        $this->timecode = $this->employee->timcode;
        foreach($this->timecode as $k=>$v)
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
        
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterYear'] = $this->FilterYear->getSelectedValue();
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterMonth'] = $this->FilterMonth->getSelectedValue();

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
