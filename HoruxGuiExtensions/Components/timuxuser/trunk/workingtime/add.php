<?php


class add extends Page
{
    protected $daysByWeek;

    public function onLoad($param)
    {
        parent::onLoad($param);

        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_config" );
        $query = $cmd->query();
        $data = NULL;

        if($query)
        {
            $data = $query->read();
            $this->daysByWeek = $data['daysByWeek'];
        }

        if(!$this->isPostBack)
        {

            $this->calendarType->setSelectedIndex(0);
            $this->session['calendarType'] = 1;

            if($data)
            {
                $this->holidaysByYear->Text = $data['holidayByYear'];

                $this->hoursByWeek->Text = $data['hoursByWeek'];

            }


            $this->employee->DataSource = $this->PersonList;
            $this->employee->dataBind();


            if($this->employee->getItemCount() && $this->employee->getSelectedValue() == '')
            {
                $this->employee->setSelectedIndex(0);
            }

        }
    }

    protected function getTimeCode()
    {
        $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT('[',abbreviation,'] - ', ' ', name) AS Text FROM hr_timux_timecode WHERE type='leave'" );
        $data =  $cmd->query();
        $data = $data->readAll();
        return $data;
        
    }

    protected function getPersonList()
    {
        $cmd = NULL;
        $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT(name, ' ', firstname) AS Text FROM hr_user WHERE name<>'??' ORDER BY name, firstname" );
        $data =  $cmd->query();
        $data = $data->readAll();
        $d[0]['Value'] = 'null';
        $d[0]['Text'] = Prado::localize('---- Choose a employee ----');
        $data = array_merge($d, $data);
        return $data;
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($lastId = $this->saveData())
            {
                $id = $lastId;
                $pBack = array('okMsg'=>Prado::localize('The working time was added successfully'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.workingtime.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The working time was not added'));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The working time was added successfully'));
            }
            else
            $pBack = array('koMsg'=>Prado::localize('The working time was not added'));
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.workingtime.workingtime',$pBack));
        }
    }

    protected function saveData()
    {

        $cmd = $this->db->createCommand( "INSERT INTO `hr_timux_workingtime` (
                                            `user_id` ,
                                            `workingPercent` ,
                                            `hoursByWeek` ,
                                            `mondayTime_m` ,
                                            `tuesdayTime_m` ,
                                            `wednesdayTime_m` ,
                                            `thursdayTime_m` ,
                                            `fridayTime_m` ,
                                            `saturdayTime_m` ,
                                            `sundayTime_m` ,
                                            `mondayTime_a` ,
                                            `tuesdayTime_a` ,
                                            `wednesdayTime_a` ,
                                            `thursdayTime_a` ,
                                            `fridayTime_a` ,
                                            `saturdayTime_a` ,
                                            `sundayTime_a` ,
                                            `startDate` ,
                                            `endDate` ,
                                            `remark` ,
                                            `holidaysByYear`,
                                            `role`,
                                            `calendarType`
                                            )
                                            VALUES (
                                            :user_id,
                                            :workingPercent,
                                            :hoursByWeek,
                                            :mondayTime_m,
                                            :tuesdayTime_m,
                                            :wednesdayTime_m,
                                            :thursdayTime_m,
                                            :fridayTime_m,
                                            :saturdayTime_m,
                                            :sundayTime_m,
                                            :mondayTime_a,
                                            :tuesdayTime_a,
                                            :wednesdayTime_a,
                                            :thursdayTime_a,
                                            :fridayTime_a,
                                            :saturdayTime_a,
                                            :sundayTime_a,
                                            :startDate,
                                            :endDate,
                                            :remark,
                                            :holidaysByYear,
                                            :role,
                                            :calendarType
                                            );" );

        $cmd->bindValue(":user_id",$this->employee->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":workingPercent",$this->workingPercent->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":hoursByWeek",$this->hoursByWeek->SafeText, PDO::PARAM_STR);

        if($this->calendarType->getSelectedValue() == 1) {
            $cmd->bindValue(":mondayTime_m",$this->monday_m->getChecked(), PDO::PARAM_STR);
            $cmd->bindValue(":tuesdayTime_m",$this->tuesday_m->getChecked(), PDO::PARAM_STR);
            $cmd->bindValue(":wednesdayTime_m",$this->wednesday_m->getChecked(), PDO::PARAM_STR);
            $cmd->bindValue(":thursdayTime_m",$this->thursday_m->getChecked(), PDO::PARAM_STR);
            $cmd->bindValue(":fridayTime_m",$this->friday_m->getChecked(), PDO::PARAM_STR);
            $cmd->bindValue(":saturdayTime_m",$this->saturday_m->getChecked(), PDO::PARAM_STR);
            $cmd->bindValue(":sundayTime_m",$this->sunday_m->getChecked(), PDO::PARAM_STR);
            $cmd->bindValue(":mondayTime_a",$this->monday_a->getChecked(), PDO::PARAM_STR);
            $cmd->bindValue(":tuesdayTime_a",$this->tuesday_a->getChecked(), PDO::PARAM_STR);
            $cmd->bindValue(":wednesdayTime_a",$this->wednesday_a->getChecked(), PDO::PARAM_STR);
            $cmd->bindValue(":thursdayTime_a",$this->thursday_a->getChecked(), PDO::PARAM_STR);
            $cmd->bindValue(":fridayTime_a",$this->friday_a->getChecked(), PDO::PARAM_STR);
            $cmd->bindValue(":saturdayTime_a",$this->saturday_a->getChecked(), PDO::PARAM_STR);
            $cmd->bindValue(":sundayTime_a",$this->sunday_a->getChecked(), PDO::PARAM_STR);
        }

        if($this->calendarType->getSelectedValue() == 2) {
            $cmd->bindValue(":mondayTime_m",$this->monday_m2->SafeText, PDO::PARAM_STR);
            $cmd->bindValue(":tuesdayTime_m",$this->tuesday_m2->SafeText, PDO::PARAM_STR);
            $cmd->bindValue(":wednesdayTime_m",$this->wednesday_m2->SafeText, PDO::PARAM_STR);
            $cmd->bindValue(":thursdayTime_m",$this->thursday_m2->SafeText, PDO::PARAM_STR);
            $cmd->bindValue(":fridayTime_m",$this->friday_m2->SafeText, PDO::PARAM_STR);
            $cmd->bindValue(":saturdayTime_m",$this->saturday_m2->SafeText, PDO::PARAM_STR);
            $cmd->bindValue(":sundayTime_m",$this->sunday_m2->SafeText, PDO::PARAM_STR);
            $cmd->bindValue(":mondayTime_a",$this->monday_a2->SafeText, PDO::PARAM_STR);
            $cmd->bindValue(":tuesdayTime_a",$this->tuesday_a2->SafeText, PDO::PARAM_STR);
            $cmd->bindValue(":wednesdayTime_a",$this->wednesday_a2->SafeText, PDO::PARAM_STR);
            $cmd->bindValue(":thursdayTime_a",$this->thursday_a2->SafeText, PDO::PARAM_STR);
            $cmd->bindValue(":fridayTime_a",$this->friday_a2->SafeText, PDO::PARAM_STR);
            $cmd->bindValue(":saturdayTime_a",$this->saturday_a2->SafeText, PDO::PARAM_STR);
            $cmd->bindValue(":sundayTime_a",$this->sunday_a2->SafeText, PDO::PARAM_STR);
        }

        $cmd->bindValue(":startDate",$this->dateToSql($this->from->SafeText), PDO::PARAM_STR);

        if($this->to->SafeText != '')
            $cmd->bindValue(":endDate",$this->dateToSql($this->to->SafeText), PDO::PARAM_STR);
        else
            $cmd->bindValue(":endDate",NULL);

        $cmd->bindValue(":remark",$this->remark->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":holidaysByYear",$this->holidaysByYear->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":calendarType",$this->calendarType->getSelectedValue(), PDO::PARAM_STR);

        $role = 'employee';

        if($this->r_employee->getChecked())
            $role = 'employee';
        if($this->r_manager->getChecked())
            $role = 'manager';
        if($this->r_rh->getChecked())
            $role = 'rh';

        $cmd->bindValue(":role",$role, PDO::PARAM_STR);


        $res1 = $cmd->execute();
        $lastId = $this->db->LastInsertID;

        /*
         * Create the employee counter
         *
         */

        // get the default time code for the holiday and the overtime
        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_timecode WHERE (type='overtime' OR type='leave') AND (defaultHoliday=1 OR defaultOvertime=1)");
        $data = $cmd->query();
        $data = $data->readAll();

        foreach($data as $d)
        {
            // do we have an existing activity counter
            $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE timecode_id=".$d['id'].' AND user_id='.$this->employee->getSelectedValue());
            $data2 = $cmd->query();
            $data2 = $data2->readAll();

            // if not exist, create the counter for the employee
            if(count($data2)==0)
            {
                $cmd = $this->db->createCommand("INSERT hr_timux_activity_counter SET
                                                    user_id=:user_id,
                                                    timecode_id=:timecode_id,
                                                    year=:year,
                                                    month=:month,
                                                    nbre=:nbre,
                                                    isClosedMonth=:isClosedMonth
                                                ");

                $cmd->bindValue(":user_id",$this->employee->getSelectedValue(),PDO::PARAM_STR);
                $cmd->bindValue(":timecode_id",$d['id'],PDO::PARAM_STR);
                $nbre = 0;
                $nbreLast = 0;

                list($day,$month,$year) = explode("-",$this->from->SafeText);
                
                list($dayEnd,$monthEnd,$yearEnd) = explode("-",$this->to->SafeText);

                $month = 12;
                $year--;

                $cmd->bindValue(":year",$year,PDO::PARAM_STR);
                $cmd->bindValue(":month",$month,PDO::PARAM_STR);

                list($day,$month,$year) = explode("-",$this->from->SafeText);

                // are we computing the holiday?
                if($d['defaultHoliday'] == 1)
                {
                    //holiday for the year
                    $nbre = $this->holidaysByYear->SafeText;

                    //holiday for the last year
                    $nbreLast = $this->holidaysLastYear->SafeText;

                    $totalMonth = 12.0;
                    // the working time end the same year than it start
                    if($year == $yearEnd) {
                        $totalMonth = $monthEnd;
                    }

                    //compute how many holiday we have until the end of the year
                    $nbre = bcdiv( bcmul(($totalMonth-$month+1), $nbre,2), 12.0, 2);

                    // add the last and the current
                    $nbre = bcadd($nbre, $nbreLast,2 );

                }

                // are we computing the overtime?
                if($d['defaultOvertime'] == 1)
                {
                    list($day,$month,$year) = explode("-",$this->from->SafeText);

                    if($month == 1) {
                        $month = 12;
                        $year--;
                    } else {
                        $month--;
                    }

                    $nbreLast = $this->overtimeLastYear->SafeText;
                    $nbre = 0;

                    // insert a first closed month for the month 12 and year-1
                    if($month != 12) {
                        $cmd->bindValue(":year",$year-1,PDO::PARAM_STR);
                        $cmd->bindValue(":month",12,PDO::PARAM_STR);
                        $cmd->bindValue(":nbre",$nbreLast,PDO::PARAM_STR);
                        $cmd->bindValue(":isClosedMonth",1,PDO::PARAM_STR);
                        $res1 = $cmd->execute();
                    }

                    // insert a first closed month for the month-1 and year
                    $cmd->bindValue(":year",$year,PDO::PARAM_STR);
                    $cmd->bindValue(":month",$month,PDO::PARAM_STR);

                }


                $cmd->bindValue(":nbre",$nbreLast,PDO::PARAM_STR);
                $cmd->bindValue(":isClosedMonth",1,PDO::PARAM_STR);
                $res1 = $cmd->execute();

                // insert the current value
                $cmd->bindValue(":year",0,PDO::PARAM_STR);
                $cmd->bindValue(":month",0,PDO::PARAM_STR);
                $cmd->bindValue(":nbre",$nbre,PDO::PARAM_STR);
                $cmd->bindValue(":isClosedMonth",0,PDO::PARAM_STR);
                $res1 = $cmd->execute();


            }
            else
            {
                // we add second working time, recompute the holiday



                /*if($d['defaultHoliday'] == 1)
                {
                    $nbre = $this->holidaysByYear->SafeText;

                    $date = explode("-",$this->from->SafeText);

                    $nbre = bcdiv( bcmul((12-$date[1]+1), $nbre,2), 12.0, 2);
                    $nbreReste = $nbre;

                    $cmd = $this->db->createCommand("UPDATE hr_timux_activity_counter SET
                                                        user_id=:user_id,                                                        
                                                        nbre=(nbre-:nbreReste)+:nbre
                                                     WHERE timecode_id=:timecode_id
                                                    ");
                    $cmd->bindValue(":user_id",$this->employee->getSelectedValue(),PDO::PARAM_STR);
                    $cmd->bindValue(":timecode_id",$d['id'],PDO::PARAM_STR);

                    $cmd->bindValue(":nbre",$nbre,PDO::PARAM_STR);
                    $cmd->bindValue(":nbreReste",$nbreReste,PDO::PARAM_STR);
                    $res1 = $cmd->execute();

                }*/
            }
        }       

        return $lastId;
    }


    public function onCancel($sender, $param)
    {
        unset($this->session['calendarType']);
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.workingtime.workingtime'));
    }
}
