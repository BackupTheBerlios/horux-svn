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
    protected $timcode = array();

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
        parent::onPrint();
        $app = $this->getApplication()->getGlobalization();

        $year = $this->Request['f3'];

        $this->pdf->AddPage();

        $data = $this->getData();

        $this->pdf->SetFont('Arial','',9);
        $this->pdf->Cell(0,10,utf8_decode(Prado::localize('Sign in/out')),0,0,'L');
        $this->pdf->Ln(10);
        //$this->pdf->setDefaultFont();

        $this->pdf->Cell(30,5,utf8_decode(Prado::localize('Employee'))." :",0,0,'L');
        $this->pdf->Cell(0,5,utf8_decode($this->employee->getFullName()),0,1,'L');

        $this->pdf->Cell(30,5,utf8_decode(Prado::localize('Department'))." :",0,0,'L');
        $this->pdf->Cell(0,5,utf8_decode($this->employee->getDepartment()),0,1,'L');

        $date = new DateFormat($app->getCulture());
        $date = $date->format('1-'.$this->Request['f4']."-".$this->Request['f3'], "P");
        $date = explode(" ", $date);
        $date = $date[2]." ".$date[3];

        $this->pdf->Cell(30, 5,utf8_decode(Prado::localize('Month'))." :",0,0,'L');
        $this->pdf->Cell(0,5,utf8_decode($date),0,1,'L');

        $this->pdf->Ln(10);


        $header = array(utf8_decode(Prado::localize("Date")),
            utf8_decode(Prado::localize("Signing")),
            utf8_decode(Prado::localize("To do")),
            utf8_decode(Prado::localize("Done")),
            utf8_decode(Prado::localize("Overtime")),
            utf8_decode(Prado::localize("Remark")),
        );

        $this->pdf->SetFillColor(124,124,124);
        $this->pdf->SetTextColor(255);
        $this->pdf->SetDrawColor(255);
        $this->pdf->SetLineWidth(.3);
        $this->pdf->SetFont('','B');
        $w=array(20,65,15,15,15,65);
        for($i=0;$i<count($header);$i++)
        $this->pdf->CellExt($w[$i],7,$header[$i],1,0,'C',1);
        $this->pdf->Ln();
        $this->pdf->SetFillColor(215,215,215);
        $this->pdf->SetTextColor(0);
        $this->pdf->SetFont('');

        $fill=false;

        $this->pdf->SetFont('courier','',7);

        foreach($data as $d)
        {
            $date = new DateFormat($app->getCulture());
            $date = $date->format($d['date'], "P");

            $date = explode(" ", $date);
            $date[0] = strtoupper(substr($date[0], 0,2));
            $date = implode(" ", $date);

            $date= utf8_decode($date);

            $nBr2 = $this->findall("<br/>", $d['sign']);

            $sign = str_replace("&nbsp;"," ",str_replace("<br/>","\n",$d['sign']));
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

            if($nBr2!==false && $nBr2>$nBr) $nBr = $nBr2;

            $height = 5;
            if($nBr !== false)
            {
                $height = 5.5 * count($nBr);
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
        $hoursForTheMonthAtX = $this->employee->getHoursMonth($this->Request['f3'], $this->Request['f4']);
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

        $this->pdf->Cell(10,3,"",0,0,'R');

        $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Holidays (days)'))."",0,0,'L');
        $this->pdf->Cell(20,3,"",0,1,'R');

//Ligne 2

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

        $this->pdf->Cell(10,3,"",0,0,'R');

        $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Holidays balance last year'))." :",0,0,'L');
        $this->pdf->Cell(20,3,sprintf("%.02f",$this->employee->geHolidaystMonth($year-1,12)),0,1,'R');


//Ligne 3

        $balanceForTheMonth = bcsub($this->signedValue,$hoursForTheMonthAtX,4);

        if($balanceForTheMonth>0)
        {
            $this->pdf->Cell(30,3,utf8_decode(str_replace("<br/>"," ",Prado::localize('Balance for the month')))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("+ %.02f",$balanceForTheMonth),0,0,'R');
        }
        elseif($balanceForTheMonth<0 || $balanceForTheMonth==0)
        {
            $this->pdf->Cell(30,3,utf8_decode(str_replace("<br/>"," ",Prado::localize('Balance for the month')))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf(" %.02f",$balanceForTheMonth),0,0,'R');
        }

        $this->pdf->Cell(10,3,"",0,0,'R');

        $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Holidays for the year'))." :",0,0,'L');
        $this->pdf->Cell(20,3,sprintf("%.02f",$this->employee->geHolidaystForTheYear($this->Request['f3'], $this->Request['f4'])),0,1,'R');

//Ligne 4
        $lastOvertime = $this->employee->getOvertimeLastMonth($this->Request['f3'], $this->Request['f4']);

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

        $this->pdf->Cell(10,3,"",0,0,'R');

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

        $wt = $this->employee->getWorkingTime($y, $m);

        if(!$wt)
        {
            $holidaysLastMonth = $this->employee->geHolidaystMonth($this->Request['f3'], $this->Request['f4']);
        }
        else
        {
            $holidaysLastMonth = $this->employee->geHolidaystLastMonth($this->Request['f3'], $this->Request['f4']);
        }

        if($holidaysLastMonth>0)
        {
            $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Holidays last month'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf(" %.02f",$holidaysLastMonth),0,1,'R');
        }
        elseif($holidaysLastMonth<0 || $holidaysLastMonth==0)
        {
            $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Holidays last month'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf(" %.02f",$holidaysLastMonth),0,1,'R');
        }

// ligne 5

        $balances = bcadd($balanceForTheMonth , $lastOvertime, 4);

        if($balances>0)
        {
            $this->pdf->Cell(30,3,utf8_decode(Prado::localize('Balances'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("+%.02f",$balances),0,0,'R');
        }
        elseif($balances<0 || $balances==0)
        {
            $this->pdf->Cell(30,3,utf8_decode(Prado::localize('Balances'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf(" %.02f",$balances),0,0,'R');
        }

        $this->pdf->Cell(10,3,"",0,0,'R');

        $defaultHolidayTimeCode = $this->employee->getDefaultHolidaysCounter();

        $holidays = $this->employee->getRequest($this->Request['f3'], $this->Request['f4'],$defaultHolidayTimeCode);

        if($holidays['nbre']>0)
        {
            $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Holidays for this month'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("- %.02f",$holidays['nbre']),0,1,'R');
        }
        elseif($holidays['nbre']==0)
        {
            $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Holidays for this month'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("%.02f",$holidays['nbre']),0,1,'R');
        }


// ligne 6

        $this->pdf->Cell(30,3,"",0,0,'L');
        $this->pdf->Cell(20,3,"",0,0,'R');
        $this->pdf->Cell(10,3,"",0,0,'R');


        $holidaysTotal = bcsub($holidaysLastMonth, $holidays['nbre'],4);
        if($holidaysTotal>0)
        {
            $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Total'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("%.02f",$holidaysTotal),0,1,'R');
        }
        elseif($holidaysTotal<0 || $holidaysTotal==0)
        {
            $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Total'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("%.02f",$holidaysTotal),0,1,'R');
        }


// ligne 7

        $this->pdf->ln(3);

// ligne 8
        $this->pdf->Cell(30,3,"",0,0,'L');
        $this->pdf->Cell(20,3,"",0,0,'R');
        $this->pdf->Cell(10,3,"",0,0,'R');

        $nonWorkingDay = $this->employee->getAllNonWorkingDay($this->Request['f3'], $this->Request['f4']);
        if($nonWorkingDay>0)
        {
            $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Non working days similar to a Sunday'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf(" %.02f",$nonWorkingDay),0,1,'R');
        }
        elseif($nonWorkingDay==0)
        {
            $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Non working days similar to a Sunday'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf(" %.02f",$nonWorkingDay),0,1,'R');
        }

// ligne 9
        $this->pdf->Cell(30,3,"",0,0,'L');
        $this->pdf->Cell(20,3,"",0,0,'R');
        $this->pdf->Cell(10,3,"",0,0,'R');

        $nonworkingdayendofyear = $this->employee->getNonWorkingDayEndOfYear($this->Request['f3'], $this->Request['f4']);
        if($nonworkingdayendofyear>0)
        {
            $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Non working days a the end of the year'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf(" %.02f",$nonworkingdayendofyear),0,1,'R');
        }
        elseif($nonworkingdayendofyear==0)
        {
            $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Non working days a the end of the year'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf(" %.02f",$nonworkingdayendofyear),0,1,'R');
        }


        $this->pdf->ln(7);


        foreach($this->timcode as $k=>$v)
        {
            $disp = $v['formatDisplay'] == 'day' ? Prado::localize('days') : Prado::localize('hours');
            $this->pdf->Cell(50,3,utf8_decode($k)." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("%.02f $disp",$v['value']),0,1,'R');

        }


        $this->pdf->render();

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

    public function getData()
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

        $nbreOfDay = date("t",mktime(0,0,0,$month,1,$year));

        $signed = 0.0;
        $due = 0.0;

        $todo = $this->employee->getTimeHoursDayTodo($year, $month);
        $config = $this->employee->getConfig();
        $holidays = 0.0;
        $timecode = array();

        for($i=1; $i<=$nbreOfDay;$i++)
        {
            $date = $year."-".$month."-".$i;

            $line = array();

            // date of th day
            $line['date'] = $date;

            // bookings done in the day
            $booking = $this->employee->getBookings('all',$date,$date,'ASC');
            $bookingIn = $this->employee->getBookings(1,$date,$date,'ASC');
            $bookingOut = $this->employee->getBookings(0,$date,$date,'ASC');

            $line['sign'] = '';

            $line['remark'] = '';
            $index_br = 1;
            //display the booking
            foreach($booking as $b)
            {
                $line['sign'].= '<a href="index.php?page=components.timuxuser.booking.mod&back=components.timuxuser.balances.balances&id='.$b['id'].'" >';
                if($b['internet'] == 1) $line['sign'].= "*";
                $inout = $b['inout'] == 'out' ? Prado::localize("out") : Prado::localize("in");
                $line['sign'] .= substr($b['time'],0,5)."/".$inout."</a>&nbsp;&nbsp;&nbsp;";

                if($index_br % 4 == 0) $line['sign'] .= "<br/>";
                
                $index_br++;

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

            if(substr($line['sign'],-5,5) == '<br/>')
                $line['sign'] = substr($line['sign'],0,strlen($line['sign'])-5);

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
            if(!$this->employee->isBreakOk($year, $month, $i) && count($booking) % 2 == 0)
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                 $line['remark'] .= "<span style=\"color:red\">".$config['minimumBreaks']." ".Prado::localize('min. for the break are required')."</span>";
            }

            // check the time between two booking

            if(!$this->employee->isTimeBetweenTwoBookingsOk($year, $month, $i) && count($booking) % 2 == 0)
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                 $line['remark'] .= "<span style=\"color:red\">".Prado::localize('A time between two bookings is too small')."</span>";

            }

            // get the time signed by the employee
            $timeWorked = $this->employee->getTimeWorked($year, $month, $i);
            $line['done'] = $timeWorked['time'] > 0 ? sprintf("%.02f",$timeWorked['time']) : '';
            

            // get the non working day
            $nwd = $this->employee->getNonWorkingDay($year, $month, $i);
            $nwdPeriod = $this->employee->getNonWorkingDayPeriod($year, $month, $i);

            // get the absences
            $a = $this->employee->getAbsence($year, $month, $i);
            $aPeriod = $this->employee->getAbsencePeriod($year, $month, $i);

            // check if the employee is working this day
            $isWorking = $this->employee->isWorking($year, $month, $i);

            // compute the hours that the employee must work according to the n.w.d. and the absence
            if($isWorking)
            {
                // what is the time period for the day
                $todoPeriod = $this->employee->isWorkingPeriod($year, $month, $i);

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
                    $tc = $this->employee->getTimeCode($date);
                    $line['remark'] .= $tc['name'];

                    // add the time code in list used to diplay the time code list, the default
                    // holidays time code is omitted
                    if($tc['timecodeId'] != $this->employee->getDefaultHolidaysCounter())
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
                    $tc = $this->employee->getTimeCode($date,'morning');
                    $line['remark'] .= $tc['name'].' / '.Prado::localize('morning');
                    
                    if($tc['timecodeId'] != $this->employee->getDefaultHolidaysCounter())
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
                    $tc = $this->employee->getTimeCode($date,'afternoon');
                    $line['remark'] .= $tc['name'].' / '.Prado::localize('afternoon');

                    if($tc['timecodeId'] != $this->employee->getDefaultHolidaysCounter())
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

        // get the hours that the employee should worked for the month
        $hoursForTheMonthAtX = $this->employee->getHoursMonth($year, $month);

        // display the value
        if($hoursForTheMonthAtX>0)
            $this->hoursDue->Text = sprintf("+%.02f",$hoursForTheMonthAtX);
        elseif($hoursForTheMonthAtX<0 || $hoursForTheMonthAtX==0)
            $this->hoursDue->Text = sprintf("%.02f",$hoursForTheMonthAtX);

        // display the hours that the employee has worked
        $this->signedValue = $signed;
        if($signed>0)
            $this->signed->Text = sprintf("+%.02f",$signed);
        elseif($signed<0 || $signed==0)
            $this->signed->Text = sprintf("%.02f",$signed);


        // compute the balance (overtime) for the month
        $balanceForTheMonth = bcsub($signed,$hoursForTheMonthAtX,4);

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


        //Nbre of holiday that the employee has for the year
        $this->holidayForTheYear->Text = sprintf("%.02f",$this->employee->geHolidaystForTheYear($year, $month));

        //Balance of holiday fot the last year
        $this->balanceHolidaysLastYear->Text = sprintf("%.02f",$this->employee->geHolidaystMonth($year-1,12));

        // compute the holdiday for the last month
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
            $holidaysLastMonth = $this->employee->geHolidaystMonth($year, $month);
        }
        else
        {
            $holidaysLastMonth = $this->employee->geHolidaystLastMonth($year, $month);
        }

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
        $this->timcode = $timecode;
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
