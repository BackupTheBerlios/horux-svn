<?php


$param = Prado::getApplication()->getParameters();
$computation = $param['computation'];

Prado::using('horux.pages.components.timuxuser.'.$computation);

class balances extends PageList
{
    protected $userId = 0;
    protected $employee = null;

    protected $signedValue = 0.0;
    protected $timcode = array();
    protected $overTimeMonth = 0;
    protected $overTimeLastMonth = 0;

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

            if($this->employee->getUserId() > 0 ) {
                $this->DataGrid->DataSource=$this->Data;
                $this->DataGrid->dataBind();
            }
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

        $data = $this->getData(true);

        $this->pdf->SetFont('Arial','',9);
        $this->pdf->Cell(0,10,utf8_decode(Prado::localize('Sign in/out')),0,0,'L');
        $this->pdf->Ln(10);

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
        $this->pdf->Cell(30,3,utf8_decode(Prado::localize('Hours due'))." :",0,0,'L');
        $this->pdf->Cell(20,3,$this->hoursDue->Text,0,0,'R');


        $this->pdf->Cell(10,3,"",0,0,'R');

        $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Holidays (days)'))."",0,0,'L');
        $this->pdf->Cell(20,3,"",0,1,'R');

//Ligne 2

        $this->pdf->Cell(30,3,utf8_decode(Prado::localize('Signed'))." :",0,0,'L');
        $this->pdf->Cell(20,3,$this->signed->Text,0,0,'R');

        $this->pdf->Cell(10,3,"",0,0,'R');

        $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Holidays balance last year'))." :",0,0,'L');
        $this->pdf->Cell(20,3,$this->balanceHolidaysLastYear->Text,0,1,'R');


//Ligne 3


        if($this->overTimeMonth>0)
        {
            $this->pdf->Cell(30,3,utf8_decode(str_replace("<br/>"," ",Prado::localize('Balance for the month')))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("+%.02f",$this->overTimeMonth),0,0,'R');
        }
        elseif($this->overTimeMonth<0 || $this->overTimeMonth==0)
        {
            $this->pdf->Cell(30,3,utf8_decode(str_replace("<br/>"," ",Prado::localize('Balance for the month')))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf(" %.02f",$this->overTimeMonth),0,0,'R');
        }

        $this->pdf->Cell(10,3,"",0,0,'R');

        

        $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Holidays for the year'))." :",0,0,'L');
        $this->pdf->Cell(20,3,sprintf(" %.02f",$this->holidayForTheYear->Text),0,1,'R');

//Ligne 4

        if($this->overTimeLastMonth>0)
        {
            $this->pdf->Cell(30,3,utf8_decode(str_replace("<br/>"," ",Prado::localize('Last month')))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("+%.02f",$this->overTimeLastMonth),0,0,'R');
        }
        elseif($this->overTimeLastMonth<0 || $this->overTimeLastMonth==0)
        {
            $this->pdf->Cell(30,3,utf8_decode(str_replace("<br/>"," ",Prado::localize('Last month')))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf(" %.02f",$this->overTimeLastMonth),0,0,'R');
        }
        

        $this->pdf->Cell(10,3,"",0,0,'R');
        
        $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Holidays last month'))." :",0,0,'L');
        $this->pdf->Cell(20,3,$this->holidaysLastMonth->Text,0,1,'R');


// ligne 5

        $balances = bcadd($this->overTimeMonth , $this->overTimeLastMonth, 4);

        $overtTimeActivityCounter = $this->employee->getActivityCounter($year, $this->Request['f4'], $this->employee->getDefaultOvertimeCounter() );

        if($overtTimeActivityCounter != 0) {
            $balances = bcadd($balances, $overtTimeActivityCounter, 4);
        }


        if($balances>0)
        {
            $this->pdf->Cell(30,3,utf8_decode(Prado::localize('Balances'))." :",0,0,'L');
            if($overtTimeActivityCounter)
                $this->pdf->Cell(20,3,sprintf("* +%.02f",$balances),0,0,'R');
            else
                $this->pdf->Cell(20,3,sprintf("+%.02f",$balances),0,0,'R');
        }
        elseif($balances<0 || $balances==0)
        {
            $this->pdf->Cell(30,3,utf8_decode(Prado::localize('Balances'))." :",0,0,'L');
            if($overtTimeActivityCounter)
                $this->pdf->Cell(20,3,sprintf("* %.02f",$balances),0,0,'R');
            else
                $this->pdf->Cell(20,3,sprintf(" %.02f",$balances),0,0,'R');
        }

        $this->pdf->Cell(10,3,"",0,0,'R');

        $defaultHolidayTimeCode = $this->employee->getDefaultHolidaysCounter();

        $holidays = $this->employee->getRequest($this->Request['f3'], $this->Request['f4'],$defaultHolidayTimeCode);

        if($holidays['nbre']>0)
        {
            $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Holidays for this month'))." :",0,0,'L');
            $this->pdf->Cell(20,3,sprintf("-%.02f",$holidays['nbre']),0,1,'R');
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

        $holidayActivityCounter = $this->employee->getActivityCounter($year, $this->Request['f4'], $this->employee->getDefaultHolidaysCounter() );

        $holidaysTotal = bcsub($this->holidaysLastMonth->Text, $holidays['nbre'],4);
        if($holidaysTotal>0)
        {
            $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Total'))." :",0,0,'L');
            if($holidayActivityCounter)
                $this->pdf->Cell(20,3,sprintf("* +%.02f",$holidaysTotal),0,1,'R');
            else
                $this->pdf->Cell(20,3,sprintf("+%.02f",$holidaysTotal),0,1,'R');
        }
        elseif($holidaysTotal<0 || $holidaysTotal==0)
        {
            $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Total'))." :",0,0,'L');
            if($holidayActivityCounter)
                $this->pdf->Cell(20,3,sprintf("* %.02f",$holidaysTotal),0,1,'R');
            else
                $this->pdf->Cell(20,3,sprintf("%.02f",$holidaysTotal),0,1,'R');
        }


// ligne 7

        $this->pdf->ln(3);

// ligne 8
        $this->pdf->Cell(30,3,"",0,0,'L');
        $this->pdf->Cell(20,3,"",0,0,'R');
        $this->pdf->Cell(10,3,"",0,0,'R');

        $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Non working days similar to a Sunday'))." :",0,0,'L');
        $this->pdf->Cell(20,3,$this->nonworkingday->Text,0,1,'R');


// ligne 9
        $this->pdf->Cell(30,3,"",0,0,'L');
        $this->pdf->Cell(20,3,"",0,0,'R');
        $this->pdf->Cell(10,3,"",0,0,'R');

        $this->pdf->Cell(55,3,utf8_decode(Prado::localize('Non working days a the end of the year'))." :",0,0,'L');
        $this->pdf->Cell(20,3,sprintf(" %.02f",$this->nonworkingdayendofyear->Text),0,1,'R');

        $this->pdf->ln(7);


        foreach($this->TimeCode->DataSource as $v)
        {
            $this->pdf->Cell(50,3,utf8_decode($v['name'])." :",0,0,'L');
            $this->pdf->Cell(80,3,utf8_decode($v['value']),0,1,'R');

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

    public function getData($isPrint=false)
    {
        // data of the month
        $rows  = array();

        //according to to the filter, set the current month/year
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

        //number of day in the month selected according to the year
        $nbreOfDay = date("t",mktime(0,0,0,$month,1,$year));

        $todoMonth = 0;
        $doneMonth = 0;
        $overTimeMonth = 0;
        $timeCode = array();

        /*
         *  Representaiton of the array to be displayed
         *  line['date'] => date of the current day
         *  line['sign'] => liste of the booking for the current day
         */

        for($day=1; $day<=$nbreOfDay;$day++)
        {
            // create a line
            $line = array();

            //------------------------------- DATE ---------------------------------------------------------------------------------------------------
            // create the date
            $date = $year."-".$month."-".$day;
            $line['date'] = $date;

            //------------------------------- BOOKINGS -----------------------------------------------------------------------------------------------
            $bookingsDay = $this->employee->getBookingsDay($day, $month, $year);
            if (!is_array($bookingsDay)) {
              return array();
            }

            // when 4 bookings are displayed, add a new line
            $index_br = 1;

            foreach($bookingsDay as $b) {

                // if we print on a pdf, do not insert the link
                if(!$isPrint && $this->isAccess('components.timuxuser.booking.mod') && $b['closed'] == 0)
                {
                    $line['sign'].= '<a href="index.php?page=components.timuxuser.booking.mod&back=components.timuxuser.balances.balances&id='.$b['id'].'" >';
                }

                // add an * if the booking was modified by hand
                if($b['internet'] == 1) $line['sign'].= "*";

                $inout = $this->employee->isBookingIn($b) ? Prado::localize("in") : Prado::localize("out");

                // add the time without the second and close the link on th booking for an internet display
                if(!$isPrint)
                {
                    $line['sign'] .= substr($b['roundBooking'],0,5)."/".$inout."</a>&nbsp;&nbsp;&nbsp;";
                }
                else
                {
                    $line['sign'] .= substr($b['roundBooking'],0,5)."/".$inout."&nbsp;&nbsp;&nbsp;";
                }

                if($index_br % 4 == 0) $line['sign'] .= "<br/>";

                $index_br++;
            }

            if(substr($line['sign'],-5,5) == "<br/>")
               $line['sign'] = substr($line['sign'], 0, strlen($line['sign'])-5);

            //------------------------------- TIME TO DO -----------------------------------------------------------------------------------------

            $todo = $this->employee->getDayTodo($day, $month, $year);
            if($todo == 0)
                $line['todo'] = '';
            else {
                $line['todo'] = sprintf("%.02f",$todo);
                $todoMonth = bcadd($todoMonth, $todo,4);
            }

            //------------------------------- TIME DONE -----------------------------------------------------------------------------------------

            $done = $this->employee->getDayDone($day, $month, $year);
            if($done['done'] == 0)
                $line['done'] = '';
            else {
                $line['done'] = sprintf("%.02f",$done['done']);
                $doneMonth = bcadd($doneMonth, $done['done'],4);
            }

            if($done['compensation'] > 0) {
                $tc['timecodeName'] = $done['timecodeName'];
                $tc['compensation'] = bcadd($timeCode[$done['timecodeId']]['compensation'], $done['compensation'],4);
                $timeCode[$done['timecodeId']] = $tc;

            }

            //------------------------------- OVERTIME -----------------------------------------------------------------------------------------

            $overtime = bcsub($done['done'],$todo,4);
            if($overtime == 0)
                $line['overtime'] = '';
            else {
                $line['overtime'] = sprintf("%.02f",$overtime);
                $overTimeMonth = bcadd($overTimeMonth, $overtime,4);
            }

            //------------------------------- REMARKS -----------------------------------------------------------------------------------------

            $isNWD = false;
            //inform when it is a non working day
            if($this->employee->isNonWorkingDay($day, $month, $year) && $this->employee->isWorking($year, $month, $day)) {
                if($line['remark'] != '') $line['remark'].="<br>";
                
                $line['remark'] .= Prado::localize('Non working day');
                $isNWD = true;
            }

            $bookingIn = 0;
            $bookingOut = 0;
            //inform when the booking is a special time code
            foreach($bookingsDay as $b) {

                if($this->employee->isBookingIn($b))
                    $bookingIn++;
                else
                    $bookingOut++;

                if($this->employee->isSpecialTimeCode($b))
                {
                    if($line['remark'] != '') $line['remark'].="<br>";
                    if($this->employee->isBookingIn($b))
                    {
                        $line['remark'].= '&larr; ';
                        $line['remark'].= substr($b['roundBooking'],0,5)." ".$this->employee->getBookingTimeCode($b);
                    }
                    else
                    {
                        $line['remark'].= '&rarr; ';
                        $line['remark'].= substr($b['roundBooking'],0,5)." ".$this->employee->getBookingTimeCode($b);

                    }
                }
                
            }

            $isMiddleNwd = false;
            //check if the day is a absent request
            $requests = $this->employee->getDayRequest($day, $month, $year);

            if($requests && !$isNWD && $this->employee->isWorking($year, $month, $day)) {
                foreach($requests as $request) {
                    if($request['period'] === 'allday')
                    {
                        if($line['remark'] != '') $line['remark'].="<br>";
                            $line['remark'] .= $request['name'];
                    }
                    elseif($request['period'] === 'morning')
                    {
                        if($line['remark'] != '') $line['remark'].="<br>";
                        $line['remark'] .= $request['name'].' / '.Prado::localize('morning');
                    }
                    elseif($request['period'] === 'afternoon')
                    {
                        if($line['remark'] != '') $line['remark'].="<br>";
                        $line['remark'] .= $request['name'].' / '.Prado::localize('afternoon');
                    }
                }
            }

            // check the missing bookings
            if(count($bookingsDay) % 2 > 0)
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                 $line['remark'] .= "<span style=\"color:red\">".Prado::localize('Signing missing')."</span>";
            }
            else {
                // check the error booking
                if($bookingIn != $bookingOut)
                {
                    if($line['remark'] != '') $line['remark'].="<br>";
                     $line['remark'] .= "<span style=\"color:red\">".Prado::localize('Signing error')."</span>";
                } else {

                    $nextB = 'IN';
                    foreach($bookingsDay as $b) {
                        $type = $this->employee->isBookingIn($b) ? 'IN' : 'OUT';

                        if($type != $nextB) {
                            if($line['remark'] != '') $line['remark'].="<br>";
                            $line['remark'] .= "<span style=\"color:red\">".Prado::localize('Signing error')."</span>";
                            break;
                        }

                        $nextB = $nextB == 'IN' ? 'OUT' : 'IN';
                    }

                }
            }

            if($minBreak = $this->employee->isBreakOk($bookingsDay))
            {
                if($line['remark'] != '') $line['remark'].="<br>";
                 $line['remark'] .= "<span style=\"color:red\">".$minBreak." ".Prado::localize('min. for the break are required')."</span>";
            }


            // add a line in the rows
            $rows[] = $line;

        }

        //------------------------------- RESUME TIME -----------------------------------------------------------------------------------------

        if($todoMonth>0)
            $this->hoursDue->Text = sprintf("+%.02f",$todoMonth);
        elseif($todoMonth<0 || $todoMonth==0)
            $this->hoursDue->Text = sprintf("%.02f",$todoMonth);

        if($doneMonth>0)
            $this->signed->Text = sprintf("+%.02f",$doneMonth);
        elseif($doneMonth<0 || $doneMonth==0)
            $this->signed->Text = sprintf("%.02f",$doneMonth);

        $this->overTimeMonth = $overTimeMonth;


        if($overTimeMonth>0)
            $this->balanceForTheMonth->Text = sprintf("+%.02f",$overTimeMonth);
        elseif($overTimeMonth<0 || $overTimeMonth==0)
            $this->balanceForTheMonth->Text = sprintf("%.02f",$overTimeMonth);

        $overTimeLastMonth = $this->employee->getOvertimeLastMonth($month, $year);
        $this->overTimeLastMonth = $overTimeLastMonth;
        if($overTimeLastMonth>0)
            $this->lastMonth->Text = sprintf("+%.02f",$overTimeLastMonth);
        elseif($overTimeLastMonth<0 || $overTimeLastMonth==0)
            $this->lastMonth->Text = sprintf("%.02f",$overTimeLastMonth);

        $solde = bcadd($overTimeLastMonth,$overTimeMonth,4);

        $overtTimeActivityCounter = $this->employee->getActivityCounter($year, $month, $this->employee->getDefaultOvertimeCounter() );

        if($overtTimeActivityCounter != 0) {
            $solde = bcadd($solde, $overtTimeActivityCounter, 4);
        }

        if($solde>0) {
            if($overtTimeActivityCounter != 0)
                $this->balances->Text = sprintf("* +%.02f",$solde);
            else
                $this->balances->Text = sprintf("+%.02f",$solde);
        }
        elseif($solde<0 || $solde==0) {
            if($overtTimeActivityCounter != 0)
                $this->balances->Text = sprintf("* %.02f",$solde);
            else
                $this->balances->Text = sprintf("%.02f",$solde);
        }


        //------------------------------- RESUME HOLIDAYS -----------------------------------------------------------------------------------------


        //Balance of holiday fot the last year
        $balanceHolidaysLastYear = $this->employee->geHolidaystMonth($year-1,12);
        $this->balanceHolidaysLastYear->Text = sprintf("%.02f",$balanceHolidaysLastYear);


        // compute in this way when the 12 month of the last year is not closed

        if(!$this->employee->isLastMonthLastYeatClosed($year)) {
            $wt = $this->employee->getWorkingTime(1, $month, $year);

            // get the holiday for this month
            $defaultHolidayTimeCode = $this->employee->getDefaultHolidaysCounter();
            
            for($i=1; $i<$month ;$i++) {
                $holidays = $this->employee->getRequest($year,$i,$defaultHolidayTimeCode);                
                $wt['holidaysByYear'] = bcsub($wt['holidaysByYear'], $holidays['nbre'],4);
            }

            $this->holidayForTheYear->Text = sprintf("%.02f",$wt['holidaysByYear']);


            $holidaysLastMonth = bcadd($wt['holidaysByYear'],$balanceHolidaysLastYear,4);
            if($holidaysLastMonth>0)
                $this->holidaysLastMonth->Text = sprintf("+%.02f",$holidaysLastMonth);
            elseif($holidaysLastMonth<0 || $holidaysLastMonth==0)
                $this->holidaysLastMonth->Text = sprintf("%.02f",$holidaysLastMonth);


            $holidays = $this->employee->getRequest($year,$month,$defaultHolidayTimeCode);
            if($holidays['nbre']>0)
                $this->holidaysThisMonth->Text = sprintf("-%.02f",$holidays['nbre']);
            elseif($holidays['nbre']==0)
                $this->holidaysThisMonth->Text = sprintf("%.02f",$holidays['nbre']);


            $holidayActivityCounter = $this->employee->getActivityCounter($year, $month, $this->employee->getDefaultHolidaysCounter() );
            $holidaysTotal = bcsub($holidaysLastMonth,$holidays['nbre'],4);

            if($holidaysTotal>0) {
                if($holidayActivityCounter)
                    $this->holidaysTotal->Text = sprintf("* +%.02f",$holidaysTotal);
                else
                    $this->holidaysTotal->Text = sprintf("+%.02f",$holidaysTotal);
            }
            elseif($holidaysTotal<0 || $holidaysTotal==0) {
                if($holidayActivityCounter)
                    $this->holidaysTotal->Text = sprintf("* %.02f",$holidaysTotal);
                else
                    $this->holidaysTotal->Text = sprintf("%.02f",$holidaysTotal);
            }

        } else {

            $holidaysTotal = $this->employee->geHolidaystMonth($year,$month);

            $holidayActivityCounter = $this->employee->getActivityCounter($year, $month, $this->employee->getDefaultHolidaysCounter() );


            if($holidaysTotal>0) {
                if($holidayActivityCounter)
                    $this->holidaysTotal->Text = sprintf("* +%.02f",$holidaysTotal);
                else
                    $this->holidaysTotal->Text = sprintf("+%.02f",$holidaysTotal);
            }
            elseif($holidaysTotal<0 || $holidaysTotal==0) {
                if($holidayActivityCounter)
                    $this->holidaysTotal->Text = sprintf("* %.02f",$holidaysTotal);
                else
                    $this->holidaysTotal->Text = sprintf("%.02f",$holidaysTotal);
            }

            // get the holiday for this month
            $defaultHolidayTimeCode = $this->employee->getDefaultHolidaysCounter();
            $holidays = $this->employee->getRequest($year,$month,$defaultHolidayTimeCode);

            if($holidays['nbre']>0)
                $this->holidaysThisMonth->Text = sprintf("-%.02f",$holidays['nbre']);
            elseif($holidays['nbre']==0)
                $this->holidaysThisMonth->Text = sprintf("%.02f",$holidays['nbre']);

            $holidaysLastMonth = $holidaysTotal + $holidays['nbre'];

            // display the value
            if($holidaysLastMonth>0)
                $this->holidaysLastMonth->Text = sprintf("+%.02f",$holidaysLastMonth);
            elseif($holidaysLastMonth<0 || $holidaysLastMonth==0)
                $this->holidaysLastMonth->Text = sprintf("%.02f",$holidaysLastMonth);


            $this->holidayForTheYear->Text = sprintf("%.02f",$this->holidaysLastMonth->Text - $balanceHolidaysLastYear);
        }


       

        
        //------------------------------- RESUME NON WORKING DAY -------------------------------------------------------------------------


        $nonWorkingDay = $this->employee->getAllNonWorkingDay($year, $month);
        if($nonWorkingDay>0)
            $this->nonworkingday->Text = sprintf("%.02f",$nonWorkingDay);
        elseif($nonWorkingDay==0)
            $this->nonworkingday->Text = sprintf("%.02f",$nonWorkingDay);


        // number of n.w.d. until the end of the year
        $this->nonworkingdayendofyear->Text = sprintf("%.02f",$this->employee->getNonWorkingDayEndOfYear($month,$year));


        //------------------------------- RESUME OTHER REQUEST -------------------------------------------------------------------------

        $absences = $this->employee->getMonthAbsence($month, $year);

        foreach($timeCode as $k=>$v) {
            for($i=0; $i< count($absences); $i++) {
                if($absences[$i]['name'] == $v['timecodeName'] ) {
                    if($absences[$i]['disp'] == 'day') {
                        //convert in day
                        $v['compensation'] = bcdiv($v['compensation'],$this->employee->getHoursByDay($month, $year),4);
                        $absences[$i]['nbre'] = bcadd($absences[$i]['nbre'],$v['compensation'],4);
                    } else {
                        $absences[$i]['nbre'] = bcadd($absences[$i]['nbre'],$v['compensation'],4);
                    }
                }
            }
        }

        $tc = array();
        foreach($absences as $v)
        {
            $disp = $v['disp'] == 'day' ? Prado::localize('days') : Prado::localize('hours');
            if($v['nbre']>0) {
                $tc[] = array('name'=>$v['name'], 'value'=>sprintf("%.02f $disp",$v['nbre']) );
            }
        }


        $ac = $this->employee->getAllActivityCounter($year,$month);

        foreach($ac as $v) {
            $disp = $v['formatDisplay'] == 'day' ? Prado::localize('days') : Prado::localize('hours');
            $remark = $v['remark'];
            $tc[] = array('name'=>$v['name'], 'value'=>sprintf("%.02f $disp / $remark",$v['nbre']) );
        }

        $this->TimeCode->DataSource=$tc;
        $this->TimeCode->dataBind();


        return $rows;
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


    public function isAccess($page)
    {
        $app = $this->getApplication();
        $db = $this->db;

        $usedId = $app->getUser()->getUserID() == null ? 0 : $app->getUser()->getUserID();
        $groupId = $app->getUser()->getGroupID() == null ? 0 : $app->getUser()->getGroupID();

        $sql =  'SELECT `allowed` FROM hr_gui_permissions WHERE ' .
                      '(`page`=\''.$page.'\' OR `page` IS NULL) ' .
                      "AND (" .
                              "(`selector`='user_id' AND `value`=".$usedId.") " .
                              "OR (`selector`='group_id' AND `value`=".$groupId.") " .
                      ")" .
              'ORDER BY `page` DESC';

        $cmd = $db->createCommand($sql);
        $res = $cmd->query();
        $res = $res->readAll();
        // If there were no results
        if (!$res)
        return false;
        else
        // Traverse results
        foreach ($res as $allowed)
        {
            // If we get deny here
            if (! $allowed)
            return false;
        }

        return true;
    }

    function couldAddBooking() {

        if(!$this->employee) {
            return false;
        }

        if($this->employee->isClosedMonth($this->FilterMonth->getSelectedValue(),$this->FilterYear->getSelectedValue() ) ) {
            return false;
        } else {
            return true;
        }
    }
}
?>
