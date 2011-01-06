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
            if($data)
            {
                $this->holidaysByYear->Text = $data['holidayByYear'];


                $this->hoursByWeek->Text = $data['hoursByWeek'];
                    
                $this->totalHourByWeek->text = $data['hoursByWeek'];
                $this->onDivideTheDays(NULL,NULL);
                $this->onWorkingDayTimeChanged(NULL,NULL);
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
                                            `remark` ,
                                            `endOfActivity` ,
                                            `holidaysByYear`,
                                            `role`
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
                                            :remark,
                                            :endOfActivity,
                                            :holidaysByYear,
                                            :role
                                            );" );

        $cmd->bindValue(":user_id",$this->employee->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":workingPercent",$this->workingPercent->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":hoursByWeek",$this->hoursByWeek->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":mondayTime_m",$this->monday_m->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":tuesdayTime_m",$this->tuesday_m->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":wednesdayTime_m",$this->wednesday_m->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":thursdayTime_m",$this->thursday_m->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":fridayTime_m",$this->friday_m->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":saturdayTime_m",$this->saturday_m->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":sundayTime_m",$this->sunday_m->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":mondayTime_a",$this->monday_a->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":tuesdayTime_a",$this->tuesday_a->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":wednesdayTime_a",$this->wednesday_a->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":thursdayTime_a",$this->thursday_a->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":fridayTime_a",$this->friday_a->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":saturdayTime_a",$this->saturday_a->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":sundayTime_a",$this->sunday_a->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":startDate",$this->dateToSql($this->from->SafeText), PDO::PARAM_STR);
        $cmd->bindValue(":remark",$this->remark->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":holidaysByYear",$this->holidaysByYear->SafeText, PDO::PARAM_STR);

        $endActivity = false;
        if($this->endActivity->getChecked())
            $endActivity = true;

        $cmd->bindValue(":endOfActivity",$endActivity, PDO::PARAM_STR);

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

        //create the employee counter
        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_timecode WHERE (type='overtime' OR type='leave') AND (defaultHoliday=1 OR defaultOvertime=1)");
        $data = $cmd->query();
        $data = $data->readAll();

        foreach($data as $d)
        {
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

                $month = 12;
                $year--;

                $cmd->bindValue(":year",$year,PDO::PARAM_STR);
                $cmd->bindValue(":month",$month,PDO::PARAM_STR);

                list($day,$month,$year) = explode("-",$this->from->SafeText);

                if($d['defaultHoliday'] == 1)
                {
                    $nbre = $this->holidaysByYear->SafeText;
                    $nbreLast = $this->holidaysLastYear->SafeText;

                    $nbre = bcdiv( bcmul((12-$month+1), $nbre,2), 12.0, 2);
                    $nbre = bcadd($nbre, $nbreLast,2 );

                }

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

                    if($month != 12) {
                        $cmd->bindValue(":year",$year-1,PDO::PARAM_STR);
                        $cmd->bindValue(":month",12,PDO::PARAM_STR);
                        $cmd->bindValue(":nbre",$nbreLast,PDO::PARAM_STR);
                        $cmd->bindValue(":isClosedMonth",1,PDO::PARAM_STR);
                        $res1 = $cmd->execute();
                    }

                    $cmd->bindValue(":year",$year,PDO::PARAM_STR);
                    $cmd->bindValue(":month",$month,PDO::PARAM_STR);

                }


                $cmd->bindValue(":nbre",$nbreLast,PDO::PARAM_STR);
                $cmd->bindValue(":isClosedMonth",1,PDO::PARAM_STR);
                $res1 = $cmd->execute();

                $cmd->bindValue(":year",0,PDO::PARAM_STR);
                $cmd->bindValue(":month",0,PDO::PARAM_STR);
                $cmd->bindValue(":nbre",$nbre,PDO::PARAM_STR);
                $cmd->bindValue(":isClosedMonth",0,PDO::PARAM_STR);
                $res1 = $cmd->execute();


            }
            else
            {
                if($d['defaultHoliday'] == 1)
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

                }
            }
        }       

        return $lastId;
    }


    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.workingtime.workingtime'));
    }

    public function onClear($sender, $param)
    {
        $this->monday_m->Text = "";
        $this->tuesday_m->Text = "";
        $this->wednesday_m->Text = "";
        $this->thursday_m->Text = "";
        $this->friday_m->Text = "";
        $this->saturday_m->Text = "";
        $this->sunday_m->Text = "";
        $this->monday_a->Text = "";
        $this->tuesday_a->Text = "";
        $this->wednesday_a->Text = "";
        $this->thursday_a->Text = "";
        $this->friday_a->Text = "";
        $this->saturday_a->Text = "";
        $this->sunday_a->Text = "";

        $this->onWorkingDayTimeChanged($sender, $param);
    }

    public function onDivideTheDays($sender, $param)
    {
        $byDay = bcdiv( (float)$this->hoursByWeek->Text , (float)$this->daysByWeek*2 ,2 );
        $byDay = bcdiv( bcmul($byDay, $this->workingPercent->Text,2), 100.0, 2);

        $this->monday_m->Text = $byDay;
        $this->tuesday_m->Text = $byDay;
        $this->wednesday_m->Text = $byDay;
        $this->thursday_m->Text = $byDay;
        $this->friday_m->Text = $byDay;
        $this->monday_a->Text = $byDay;
        $this->tuesday_a->Text = $byDay;
        $this->wednesday_a->Text = $byDay;
        $this->thursday_a->Text = $byDay;
        $this->friday_a->Text = $byDay;

        $this->onWorkingDayTimeChanged($sender, $param);

    }

    public function onHoursByWeekChanged($sender, $param)
    {

        $this->totalHourByWeek->Text = $this->workingPercent->Text * $this->hoursByWeek->Text / 100;


        $this->onWorkingDayTimeChanged($sender, $param);
    }

    public function onWorkingPercentChanged($sender, $param)
    {
        $this->totalHourByWeek->Text = $this->workingPercent->Text * $this->hoursByWeek->Text / 100;
        
        
        $this->onWorkingDayTimeChanged($sender, $param);
    }

    public function onWorkingDayTimeChanged($sender, $param)
    {
        $count = 0.0;
        $count = bcadd($count,$this->monday_m->Text,2);
        $count = bcadd($count,$this->tuesday_m->Text,2);
        $count = bcadd($count,$this->wednesday_m->Text,2);
        $count = bcadd($count,$this->thursday_m->Text,2);
        $count = bcadd($count,$this->friday_m->Text,2);
        $count = bcadd($count,$this->saturday_m->Text,2);
        $count = bcadd($count,$this->sunday_m->Text,2);
        $count = bcadd($count,$this->monday_a->Text,2);
        $count = bcadd($count,$this->tuesday_a->Text,2);
        $count = bcadd($count,$this->wednesday_a->Text,2);
        $count = bcadd($count,$this->thursday_a->Text,2);
        $count = bcadd($count,$this->friday_a->Text,2);
        $count = bcadd($count,$this->saturday_a->Text,2);
        $count = bcadd($count,$this->sunday_a->Text,2);

        if((float)$count >= (float)$this->totalHourByWeek->Text)
            $this->totalCheck->Text = '<span style="color:red">> '.$count.'</span>';
        if((float)$count <= (float)$this->totalHourByWeek->Text)
            $this->totalCheck->Text = '<span style="color:red">< '.$count.'</span>';
        if((float)$count == (float)$this->totalHourByWeek->Text)
            $this->totalCheck->Text = '<span style="color:green">'.$count.'</span>';

    }

}
