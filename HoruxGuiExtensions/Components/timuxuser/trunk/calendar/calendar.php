<?php


$param = Prado::getApplication()->getParameters();
$computation = $param['computation'];

Prado::using('horux.pages.components.timuxuser.'.$computation);

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

    protected function onPrint()
    {
        $from = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'Start'];
        $to = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'End'];

        $event = $this->getData($from, $to);
//print_r($event); exit;
        parent::onPrint();

        $data = array();

        $header = array(Prado::localize('Mon'),
                        Prado::localize('Tue'),
                        Prado::localize('Wed'),
                        Prado::localize('Thu'),
                        Prado::localize('Fri'),
                        Prado::localize('Sat'),
                        Prado::localize('Sun'));

        $this->pdf->AddPage('L');

        $this->pdf->SetFillColor(255,255,255);
        $this->pdf->SetTextColor(0,0,0);
        $this->pdf->SetDrawColor(0,0,0);
        $this->pdf->SetLineWidth(.3);
        $this->pdf->SetFont('','B');
        $this->pdf->SetFontSize(10);
        //En-tête
        $this->pdf->Ln();

        $w=array(39.5,39.5,39.5,39.5,39.5,39.5,39.5);
        for($i=0;$i<count($header);$i++)
            $this->pdf->Cell($w[$i],4,$header[$i],1,0,'C',1);
        $this->pdf->Ln();

        //Restauration des couleurs et de la police
        
        $this->pdf->SetTextColor(0);
        $this->pdf->SetFont('');

        $from = strtotime($from);
        $to = strtotime($to);

        $from_default = $from;

        $x = 46;
        $y = 37;

        $this->pdf->SetFontSize(8);

        $startMonth = false;

        $from = $from_default;
        $eventPerLine = array(0,0,0,0,0,0);
        $eventPerDay = array();

        $i=1;
        while($from < $to)
        {
            $eventPerDay[$i] = 0;
            foreach($event as $e) {
                $eventDateStart = strtotime($e['start']);
                $eventDateEnd = strtotime($e['end']);

                if($eventDateStart <= $from && $from <= $eventDateEnd) {
                    $eventPerDay[$i] = $eventPerDay[$i]+1;
                }
            }
            $i++;
            $from = strtotime("+1 day", $from);
        }

        $line = 1;
        $i=1;
        foreach($eventPerDay as $k=>$v) {

            if($v > $eventPerLine[$line-1])
                $eventPerLine[$line-1] = $v;

            if($i % 7 == 0) $line++;

            $i++;
        }

        $line = 1;
        $i=1;

        $from = $from_default;
        while($from < $to)
        {

            $height = $eventPerLine[$line-1] * 3.4 + 6;

            $this->pdf->SetFontSize(10);
            $this->pdf->Cell($w[($i%7)],$height,'',1,0,'C',0);
            $this->pdf->SetFontSize(8);

            $day = date("j", $from);

            if(!$startMonth) {
                $this->pdf->SetTextColor(179,179,179);
            }

            if($startMonth && $day == 1) {
                 $startMonth = false;
                 $this->pdf->SetTextColor(179,179,179);
            }
            else {
                if($i>=1 && $day == 1) {
                    $startMonth = true;


                    $month = new TDateFormat();
                    $month->setValue( date("d-n-Y", $from) );
                    $month->Pattern = "MMM  yyyy";
                    $this->pdf->SetFontSize(12);
                    $this->pdf->SetTextColor(0,0,0);
                    $this->pdf->Text(10, 26, utf8_decode($month->FormattedDate));
                    $this->pdf->SetFontSize(8);
                    $this->pdf->SetTextColor(255,255,255);
                }

                if($startMonth)
                    $this->pdf->SetTextColor(0,0,0);
            }
            
            $this->pdf->Text($x, $y, $day);

            $x += 39.5;


            if($i%7 == 0) {
                $this->pdf->Ln();
                $y += $eventPerLine[$line-1] * 3.4 + 6;
                $x = 46;
                $line++;
            }

            $i++;


            $from = strtotime("+1 day", $from);
        }

        $eventIndex = 0;

        $eventPerLine2 = array(1=>array(),2=>array(),3=>array(),4=>array(),5=>array(),6=>array());
        $eventIndexPerLine = array(0,0,0,0,0,0);

        $this->pdf->SetTextColor(255,255,255);
        $this->pdf->SetFontSize(8);

        foreach($event as $e) {
            $y = 38;

            $colorR = hexdec(substr($e['color'],1,2));
            $colorG = hexdec(substr($e['color'],3,2));
            $colorB = hexdec(substr($e['color'],5,2));

            $this->pdf->SetFillColor($colorR,$colorG,$colorB);

            $x = 10.1;

            $eventDateStart = strtotime($e['start']);
            $eventDateEnd = strtotime($e['end']);

            $from = $from_default;
            $fromStart = 0;
            $i = 1;
            $line = 1;
            $eventStart = false;
            $countEvent = false;
            $j = 0;
            while($from < $to)
            {
                if(!isset($eventPerLine2[$line][$from]))
                    $eventPerLine2[$line][$from] = 0;


                if($eventDateStart == $from && !$eventStart) {
                    $eventStart = true;
                    $countEvent = true;
                    $eventIndexPerLine[$line-1]++;
                    $fromStart = $from;
                    if($eventDateEnd == $from) {
                        $y += $eventPerLine2[$line][$from] * 3.4;
                        $this->pdf->RoundedRect($x + ($j * 39.5) + 1 ,$y,37, 3, 0.5, '1234' ,'F');
                        $eventPerLine2[$line][$from]++;
                    } else {
                        if($i%7 == 0) {
                            $y += $eventPerLine2[$line][$from] * 3.4;
                            $this->pdf->RoundedRect($x + ($j * 39.5) + 1,$y,39, 3, 0.5, '14' ,'F');
                            $eventPerLine2[$line][$from]++;
                        } else {
                            $y += $eventPerLine2[$line][$from] * 3.4;
                            $this->pdf->RoundedRect($x + ($j * 39.5) + 1,$y,39, 3, 0.5, '14' ,'F');
                            $eventPerLine2[$line][$from]++;
                        }
                    }
                }

                if($eventDateStart < $from && !$eventStart) {
                    $eventStart = true;
                    $eventIndexPerLine[$line-1]++;
                    $countEvent = true;
                    $fromStart = $from;
                    if($eventDateEnd == $from) {
                        $y += $eventPerLine2[$line][$from] * 3.4;
                        $this->pdf->RoundedRect($x + ($j  * 39.5),$y,39.5, 3, 0.5, '23' ,'F');
                        $eventPerLine2[$line][$from]++;
                    }
                    else {
                        $y += $eventPerLine2[$line][$from] * 3.4;
                        $this->pdf->RoundedRect($x + ($j  * 39.5),$y,40, 3, 0.5, '' ,'F');
                        $eventPerLine2[$line][$from]++;
                    }
                }

                if($eventDateEnd == $from && $eventStart && $eventDateEnd!=$fromStart) {
                    if(!$countEvent) {
                        $y += $eventPerLine2[$line][$from] * 3.4;
                        $countEvent = true;
                        $eventIndexPerLine[$line-1]++;
                    }
                    $this->pdf->RoundedRect($x + ($j  * 39.5),$y,38.5, 3, 0.5, '23' ,'F');
                    $eventPerLine2[$line][$from]++;
                }

                if($eventDateEnd > $from && $eventStart && $from > $fromStart) {
                    if(!$countEvent) {
                        $y += $eventPerLine2[$line][$from] * 3.4;
                        $countEvent = true;
                        $eventIndexPerLine[$line-1]++;
                    }
                    $this->pdf->RoundedRect($x + ($j * 39.5),$y,39.5, 3, 0.5, '' ,'F');
                    $eventPerLine2[$line][$from]++;
                }

                $j++;

                if($i%7 == 0) {
                    //next line
                    $y = 38;

                    $y += $eventPerLine[$line-1] * 3.4 + 6;

                    for($k = 1; $k<$line; $k++) {
                        $y += $eventPerLine[$k-1] * 3.4 + 6;
                    }


                    $countEvent = false;
                    $line++;
                    $j = 0;
                }

                $from = strtotime("+1 day", $from);

                $i++;


            }

            $eventIndex++;
        }


        // text
        $eventIndex = 0;

        $eventPerLine2 = array(1=>array(),2=>array(),3=>array(),4=>array(),5=>array(),6=>array());
        $eventIndexPerLine = array(0,0,0,0,0,0);

        foreach($event as $e) {
            $y = 38;

            $x = 10.1;

            $eventDateStart = strtotime($e['start']);
            $eventDateEnd = strtotime($e['end']);

            $from = $from_default;
            $fromStart = 0;
            $i = 1;
            $line = 1;
            $eventStart = false;
            $countEvent = false;
            $j = 0;
            while($from < $to)
            {
                if(!isset($eventPerLine2[$line][$from]))
                    $eventPerLine2[$line][$from] = 0;

                if($eventDateStart == $eventDateEnd) {
                    $width = $this->pdf->GetStringWidth(utf8_decode($e["title"]));
                    $fontSize = 8;
                    if($width > 35) {
                        while($this->pdf->GetStringWidth(utf8_decode($e["title"])) > 35) {
                            $fontSize -= 0.1;
                            $this->pdf->SetFontSize($fontSize);
                        }
                    }
                } else {
                    $this->pdf->SetFontSize(8);
                }

                if($eventDateStart == $from && !$eventStart) {
                    $eventStart = true;
                    $countEvent = true;
                    $eventIndexPerLine[$line-1]++;
                    $fromStart = $from;
                    if($eventDateEnd == $from) {
                        $y += $eventPerLine2[$line][$from] * 3.4;
                        $this->pdf->Text($x + ($j * 39.5) + 1 + 1, $y+2.4, utf8_decode($e["title"]));
                        $eventPerLine2[$line][$from]++;
                    } else {
                        if($i%7 == 0) {
                            $y += $eventPerLine2[$line][$from] * 3.4;
                            $this->pdf->Text($x + ($j * 39.5) + 1 + 1, $y+2.4, utf8_decode($e["title"]));
                            $eventPerLine2[$line][$from]++;
                        } else {
                            $y += $eventPerLine2[$line][$from] * 3.4;
                            $this->pdf->Text($x + ($j * 39.5) + 1 + 1, $y+2.4, utf8_decode($e["title"]));
                            $eventPerLine2[$line][$from]++;
                        }
                    }
                }

                if($eventDateStart < $from && !$eventStart) {
                    $eventStart = true;
                    $eventIndexPerLine[$line-1]++;
                    $countEvent = true;
                    $fromStart = $from;
                    if($eventDateEnd == $from) {
                        $y += $eventPerLine2[$line][$from] * 3.4;
                        $eventPerLine2[$line][$from]++;
                        $this->pdf->Text($x + ($j * 39.5) + 1 + 1, $y+2.4, utf8_decode($e["title"]));
                    }
                    else {
                        $y += $eventPerLine2[$line][$from] * 3.4;
                        $eventPerLine2[$line][$from]++;
                        $this->pdf->Text($x + ($j * 39.5) + 1 + 1, $y+2.4, utf8_decode($e["title"]));
                    }
                }

                if($eventDateEnd == $from && $eventStart && $eventDateEnd!=$fromStart) {
                    if(!$countEvent) {
                        $y += $eventPerLine2[$line][$from] * 3.4;
                        $countEvent = true;
                        $eventIndexPerLine[$line-1]++;
                    }
                    $eventPerLine2[$line][$from]++;

                }

                if($eventDateEnd > $from && $eventStart && $from > $fromStart) {
                    if(!$countEvent) {
                        $y += $eventPerLine2[$line][$from] * 3.4;
                        $countEvent = true;
                        $eventIndexPerLine[$line-1]++;
                        $this->pdf->Text($x + ($j * 39.5) + 1 + 1, $y+2.4, utf8_decode($e["title"]));
                    }
                    $eventPerLine2[$line][$from]++;
                }

                $j++;

                if($i%7 == 0) {
                    //next line
                    $y = 38;

                    $y += $eventPerLine[$line-1] * 3.4 + 6;

                    for($k = 1; $k<$line; $k++) {
                        $y += $eventPerLine[$k-1] * 3.4 + 6;
                    }


                    $countEvent = false;
                    $line++;
                    $j = 0;
                }

                $from = strtotime("+1 day", $from);

                $i++;


            }

            $eventIndex++;
        }

        $this->pdf->SetTextColor(0,0,0);

        //$this->pdf->Cell(array_sum($w),0,'','T');
        $this->pdf->render();
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

    protected function getData($from, $to) {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_non_working_day WHERE `from`>='$from' AND `until`<='$to'  ORDER BY `from`" );
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

            if($this->isAccess('nonWorkingDay.mod')) {
                $url = "index.php?page=nonWorkingDay.mod&id=".$row['id']."&back=components.timuxuser.calendar.calendar";
            } else {
                $url = "#";
            }

            $event[] = array(
                            'id'=>$row['id'],
                            'title' => $row['name'],
                            'start' => $row['from'].$time,
                            'end' => $row['until'],
                            'url' => $url,
                            'className'=>'nonworking_'.substr($row['color'],1,6),
                            'description'=>$row['comment'],
                            'allDay'=> $row['period'] == 'allday' ? true : false,
                            'color'=>$row['color'],
                            'nwd' => true
                            );
        }

        while(strtotime($from) <= strtotime($to))
        {
            $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_request AS tr LEFT JOIN hr_timux_request_leave AS trl ON trl.request_id=tr.id LEFT JOIN hr_timux_timecode AS tt ON tr.timecodeId=tt.id WHERE trl.datefrom<='$from' AND trl.dateto>='$from'  AND ( tr.state='validate' OR  tr.state='closed') ORDER BY trl.datefrom" );
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

                if($this->isAccess('components.timuxuser.leave.mod')) {
                    $url = 'index.php?page=components.timuxuser.leave.mod&id='.$row['request_id'].'&back=components.timuxuser.calendar.calendar';
                } else {
                    $url = "#";
                }

                $event[$row['request_id']] = array(
                                'id'=>$row['request_id'],
                                'title' => $row['name']." (".$employee->getFullName().")",
                                'start' => $row['datefrom'].$time,
                                'end' => $row['dateto'],
                                'url' => $url,
                                'description'=>$row['remark'],
                                'className'=>'leave_'.substr($row['color'],1,6),
                                'allDay'=> $row['period'] == 'allday' ? true : false,
                                'color'=>$row['color'],
                                'nwd' => false
                                );
            }

            $from = date("Y-m-d",strtotime(date("Y-m-d", strtotime($from)) . " +1 day"));

        }

        function compare($a, $b)
        {
            return strcasecmp($a["start"], $b["start"]);
        }

        usort($event, "compare");


        return $event;
    }


    protected function getDate()
    {

        $from = date("Y-n-j", $this->Request['start']);
        $to = date("Y-n-j", $this->Request['end']);

        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'Start'] =  $from;
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'End'] =  $to;


        $cmd = $this->db->createCommand( "SELECT * FROM hr_non_working_day WHERE `from`>='$from' AND `until`<='$to' " );
        $data =  $cmd->query();
        $data =  $data->readAll();

        $event = $this->getData($from, $to);

        echo json_encode(array_values($event));

    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.panel'));
    }


    public function isAccess($page) {
        $app = $this->getApplication();
        $db = $app->getModule('horuxDb')->DbConnection;

        if(!$db) return true;

        $db->Active=true;

        $usedId = $app->getUser()->getUserID() == null ? 0 : $app->getUser()->getUserID();
        $groupId = $app->getUser()->getGroupID() == null ? 0 : $app->getUser()->getGroupID();

        $sql = 	'SELECT `allowed` FROM hr_gui_permissions WHERE ' .
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
            foreach ($res as $allowed) {
                // If we get deny here
                if (! $allowed)
                    return false;
            }

        return true;
    }
}

?>
