<?php

class mod extends Page
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
            $this->employee->DataSource = $this->PersonList;
            $this->employee->dataBind();
            $this->id->Value = $this->Request['id'];
            $this->setData();
           // $this->onWorkingDayTimeChanged(NULL,NULL);
        }
        
    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_workingtime WHERE id=:id");
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        
        if($query)
        {
            $data = $query->read();

            list($day,$month,$year) = explode("-",$this->dateFromSql($data['startDate']));

            // Check if we can modify this working time. If the first month is closed, we can't modify it
            $cmd = $this->db->createCommand( "SELECT COUNT(*) AS n FROM hr_timux_activity_counter WHERE user_id=:id AND year=:year AND month=:month AND isClosedMonth=1");
            $cmd->bindValue(":id", $data['user_id'], PDO::PARAM_STR);
            $cmd->bindValue(":year", $year, PDO::PARAM_STR);
            $cmd->bindValue(":month", $month, PDO::PARAM_STR);

            $data3 = $cmd->query();
            $data3 = $data3->read();
            if($data3['n'] > 0) {
                $pBack = array('koMsg'=>Prado::localize('The first month was already closed. You are not able to modify this working time'));
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.workingtime.workingtime',$pBack));
            }



            $this->id->Value = $data['id'];
            $this->employee->setSelectedValue($data['user_id']);
            $this->from->Text = $this->dateFromSql($data['startDate']);
            $this->to->Text = $this->dateFromSql($data['endDate']);
            $this->remark->Text = $data['remark'];
            $this->hoursByWeek->Text = $data['hoursByWeek'];
            $this->workingPercent->Text = $data['workingPercent'];

            $this->calendarType->setSelectedValue($data['calendarType']);

            if($data['calendarType'] == '1') {
                $this->monday_m->setChecked($data['mondayTime_m']);
                $this->tuesday_m->setChecked($data['tuesdayTime_m']);
                $this->wednesday_m->setChecked($data['wednesdayTime_m']);
                $this->thursday_m->setChecked($data['thursdayTime_m']);
                $this->friday_m->setChecked($data['fridayTime_m']);
                $this->saturday_m->setChecked($data['saturdayTime_m']);
                $this->sunday_m->setChecked($data['sundayTime_m']);

                $this->monday_a->setChecked($data['mondayTime_a']);
                $this->tuesday_a->setChecked($data['tuesdayTime_a']);
                $this->wednesday_a->setChecked($data['wednesdayTime_a']);
                $this->thursday_a->setChecked($data['thursdayTime_a']);
                $this->friday_a->setChecked($data['fridayTime_a']);
                $this->saturday_a->setChecked($data['saturdayTime_a']);
                $this->sunday_a->setChecked($data['sundayTime_a']);
            }

            if($data['calendarType'] == '2') {
                $this->monday_m2->Text = $data['mondayTime_m'];
                $this->tuesday_m2->Text = $data['tuesdayTime_m'];
                $this->wednesday_m2->Text = $data['wednesdayTime_m'];
                $this->thursday_m2->Text = $data['thursdayTime_m'];
                $this->friday_m2->Text = $data['fridayTime_m'];
                $this->saturday_m2->Text = $data['saturdayTime_m'];
                $this->sunday_m2->Text = $data['sundayTime_m'];

                $this->monday_a2->Text = $data['mondayTime_a'];
                $this->tuesday_a2->Text = $data['tuesdayTime_a'];
                $this->wednesday_a2->Text = $data['wednesdayTime_a'];
                $this->thursday_a2->Text = $data['thursdayTime_a'];
                $this->friday_a2->Text = $data['fridayTime_a'];
                $this->saturday_a2->Text = $data['saturdayTime_a'];
                $this->sunday_a2->Text = $data['sundayTime_a'];
            }


            $this->holidaysByYear->Text = $data['holidaysByYear'];
            $this->holidaysByYearHidden->Value = $data['holidaysByYear'];

            switch($data['role'])
            {
                case 'employee':
                    $this->r_employee->setChecked(true);
                    break;
                case 'manager':
                    $this->r_manager->setChecked(true);
                    break;
                case 'rh':
                    $this->r_rh->setChecked(true);
                    break;
            }

            //recompute the activity counter for the holidays
            list($day,$month,$year) = explode("-",$this->from->Text);

            // get the id of the defaul time code for the holiday
            $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_timecode WHERE defaultHoliday=1");
            $data2 = $cmd->query();
            $data2 = $data2->read();

            if($data2) {

                $lastYear = $year-1;

                $cmd = $this->db->createCommand( "SELECT nbre FROM hr_timux_activity_counter WHERE timecode_id=:defHol AND user_id=:id AND year=:year AND month=12");
                $cmd->bindValue(":defHol", $data2['id'], PDO::PARAM_STR);
                $cmd->bindValue(":id", $data['user_id'], PDO::PARAM_STR);
                $cmd->bindValue(":year", $lastYear, PDO::PARAM_STR);

                $data3 = $cmd->query();
                $data3 = $data3->read();
               
                $this->holidaysLastYear->Text = $data3['nbre'];
            }


            $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_timecode WHERE defaultOvertime=1");
            $data2 = $cmd->query();
            $data2 = $data2->read();

            if($data2) {

                $lastYear = $year-1;

                $cmd = $this->db->createCommand( "SELECT nbre FROM hr_timux_activity_counter WHERE timecode_id=:defHol AND user_id=:id AND year=:year AND month=12");
                $cmd->bindValue(":defHol", $data2['id'], PDO::PARAM_STR);
                $cmd->bindValue(":id", $data['user_id'], PDO::PARAM_STR);
                $cmd->bindValue(":year", $lastYear, PDO::PARAM_STR);

                $data3 = $cmd->query();
                $data3 = $data3->read();

                $this->overtimeLastYear->Text = $data3['nbre'];
            }

        }
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
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The working time was modified successfully'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.workingtime.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The working time was not modified'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.workingtime.mod', $pBack));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The working time was modified successfully'));
            }
            else
            $pBack = array('koMsg'=>Prado::localize('The working time was not modified'));
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.workingtime.workingtime',$pBack));
        }
    }

    protected function saveData()
    {

        $cmd = $this->db->createCommand( "UPDATE `hr_timux_workingtime` SET
                                            `workingPercent`=:workingPercent ,
                                            `hoursByWeek`=:hoursByWeek ,
                                            `mondayTime_m`=:mondayTime_m ,
                                            `tuesdayTime_m`=:tuesdayTime_m ,
                                            `wednesdayTime_m`=:wednesdayTime_m ,
                                            `thursdayTime_m`=:thursdayTime_m ,
                                            `fridayTime_m`=:fridayTime_m ,
                                            `saturdayTime_m`=:saturdayTime_m ,
                                            `sundayTime_m`=:sundayTime_m ,
                                            `mondayTime_a`=:mondayTime_a ,
                                            `tuesdayTime_a`=:tuesdayTime_a ,
                                            `wednesdayTime_a`=:wednesdayTime_a ,
                                            `thursdayTime_a`=:thursdayTime_a ,
                                            `fridayTime_a`=:fridayTime_a ,
                                            `saturdayTime_a`=:saturdayTime_a ,
                                            `sundayTime_a`=:sundayTime_a ,
                                            `remark`=:remark ,
                                            `holidaysByYear`=:holidaysByYear,
                                            `role`=:role,
                                            `calendarType`=:calendarType
                                            WHERE id=:id" );

        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
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


        $userId = $this->employee->getSelectedValue();

        //update the employee counter
        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_timecode WHERE defaultHoliday=1");
        $data = $cmd->query();
        $data = $data->read();

        if($data) {
            $diff = 0;

            list($day,$month,$year) = explode("-",$this->from->Text);

            $lastYear = $year-1;

            $cmd = $this->db->createCommand( "SELECT nbre FROM hr_timux_activity_counter WHERE timecode_id=:defHol AND user_id=:id AND year=:year AND month=12");
            $cmd->bindValue(":defHol", $data['id'], PDO::PARAM_STR);
            $cmd->bindValue(":id", $userId, PDO::PARAM_STR);
            $cmd->bindValue(":year", $lastYear, PDO::PARAM_STR);

            $data2 = $cmd->query();
            $data2 = $data2->read();

            $diff = bcsub($data2['nbre'], $this->holidaysLastYear->SafeText, 2);

            $cmd = $this->db->createCommand( "UPDATE hr_timux_activity_counter SET nbre=ROUND(nbre-:diff,2) WHERE timecode_id=:defHol AND user_id=:id");
            $cmd->bindValue(":diff", $diff, PDO::PARAM_STR);
            $cmd->bindValue(":defHol", $data['id'], PDO::PARAM_STR);
            $cmd->bindValue(":id", $userId, PDO::PARAM_STR);

            $cmd->execute();

            $nbre = bcdiv( bcmul((12-$month+1), $this->holidaysByYear->SafeText ,2), 12.0, 2);
            $nbre2 = bcdiv( bcmul((12-$month+1), $this->holidaysByYearHidden->Value ,2), 12.0, 2);

            $diff = bcsub($nbre2, $nbre);

            $cmd = $this->db->createCommand( "UPDATE hr_timux_activity_counter SET nbre=ROUND(nbre-:diff,2) WHERE timecode_id=:defHol AND user_id=:id AND year!=:year AND month!=12");
            $cmd->bindValue(":diff", $diff, PDO::PARAM_STR);
            $cmd->bindValue(":defHol", $data['id'], PDO::PARAM_STR);
            $cmd->bindValue(":id", $userId, PDO::PARAM_STR);
            $cmd->bindValue(":year", $lastYear, PDO::PARAM_STR);

            $cmd->execute();

            $nbre = $this->holidaysByYear->SafeText;
            $nbreLast = $this->holidaysLastYear->SafeText;

            $nbre = bcdiv( bcmul((12-$month+1), $nbre,2), 12.0, 2);
            $nbre = bcadd($nbre, $nbreLast,2 );

            $cmd = $this->db->createCommand( "UPDATE hr_timux_activity_counter SET nbre=:diff WHERE timecode_id=:defHol AND user_id=:id AND year=0 AND month=0");
            $cmd->bindValue(":diff", $nbre, PDO::PARAM_STR);
            $cmd->bindValue(":defHol", $data['id'], PDO::PARAM_STR);
            $cmd->bindValue(":id", $userId, PDO::PARAM_STR);

            $cmd->execute();
        }

        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_timecode WHERE defaultOvertime=1");
        $data = $cmd->query();
        $data = $data->read();

        if($data) {

            $diff = 0;

            list($day,$month,$lastYear) = explode("-",$this->from->Text);
            $lastYear--;


            $cmd = $this->db->createCommand( "SELECT nbre FROM hr_timux_activity_counter WHERE timecode_id=:defOverTime AND user_id=:id AND year=:year AND month=12");
            $cmd->bindValue(":defOverTime", $data['id'], PDO::PARAM_STR);
            $cmd->bindValue(":id", $this->employee->getSelectedValue(), PDO::PARAM_STR);
            $cmd->bindValue(":year", $lastYear, PDO::PARAM_STR);


            $data2 = $cmd->query();
            $data2 = $data2->read();

            $diff = bcsub($data2['nbre'], $this->overtimeLastYear->SafeText,2);


            $cmd = $this->db->createCommand( "UPDATE hr_timux_activity_counter SET nbre=ROUND(nbre-:diff,2) WHERE timecode_id=:defOverTime AND user_id=:id AND month>0 AND year>0");
            $cmd->bindValue(":diff", $diff, PDO::PARAM_STR);
            $cmd->bindValue(":defOverTime", $data['id'], PDO::PARAM_STR);
            $cmd->bindValue(":id", $userId, PDO::PARAM_STR);

            $cmd->execute();

        }

        return true;
    }


    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.workingtime.workingtime'));
    }

}
