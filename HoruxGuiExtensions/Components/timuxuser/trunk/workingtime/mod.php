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
            $this->onWorkingDayTimeChanged(NULL,NULL);
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
            $this->id->Value = $data['id'];
            $this->employee->setSelectedValue($data['user_id']);
            $this->from->Text = $this->dateFromSql($data['startDate']);
            $this->endActivity->setChecked( $data['endOfActivity'] );
            $this->remark->Text = $data['remark'];
            $this->hoursByWeek->Text = $data['hoursByWeek'];
            $this->workingPercent->Text = $data['workingPercent'];
            $this->totalHourByWeek->Text = $data['totalHourByWeek'];

            $this->monday_m->Text = sprintf("%.2f",$data['mondayTime_m']);
            $this->tuesday_m->Text = sprintf("%.2f",$data['tuesdayTime_m']);
            $this->wednesday_m->Text = sprintf("%.2f",$data['wednesdayTime_m']);
            $this->thursday_m->Text = sprintf("%.2f",$data['thursdayTime_m']);
            $this->friday_m->Text = sprintf("%.2f",$data['fridayTime_m']);
            $this->saturday_m->Text = sprintf("%.2f",$data['saturdayTime_m']);
            $this->sunday_m->Text = sprintf("%.2f",$data['sundayTime_m']);

            $this->monday_a->Text = sprintf("%.2f",$data['mondayTime_a']);
            $this->tuesday_a->Text = sprintf("%.2f",$data['tuesdayTime_a']);
            $this->wednesday_a->Text = sprintf("%.2f",$data['wednesdayTime_a']);
            $this->thursday_a->Text = sprintf("%.2f",$data['thursdayTime_a']);
            $this->friday_a->Text = sprintf("%.2f",$data['fridayTime_a']);
            $this->saturday_a->Text = sprintf("%.2f",$data['saturdayTime_a']);
            $this->sunday_a->Text = sprintf("%.2f",$data['sundayTime_a']);

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

            $this->totalHourByWeek->Text = $this->workingPercent->Text * $this->hoursByWeek->Text / 100;

            list($day,$month,$year) = explode("-",$this->from->Text);

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

                //if no data, try a second year before, but normaly this should appear
                if(!$data3) {
                    $lastYear--;
                    $cmd = $this->db->createCommand( "SELECT nbre FROM hr_timux_activity_counter WHERE timecode_id=:defHol AND user_id=:id AND year=:year AND month=12");
                    $cmd->bindValue(":defHol", $data2['id'], PDO::PARAM_STR);
                    $cmd->bindValue(":id", $data['user_id'], PDO::PARAM_STR);
                    $cmd->bindValue(":year", $lastYear, PDO::PARAM_STR);

                    $data3 = $cmd->query();
                    $data3 = $data3->read();

                    if($data3) {
                        $this->specialWT->Value = 1;
                    }
                }

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

                //if no data, try a second year before, but normaly this should appear
                if(!$data3) {

                    $lastYear--;
                    $cmd = $this->db->createCommand( "SELECT nbre FROM hr_timux_activity_counter WHERE timecode_id=:defHol AND user_id=:id AND year=:year AND month=12");
                    $cmd->bindValue(":defHol", $data2['id'], PDO::PARAM_STR);
                    $cmd->bindValue(":id", $data['user_id'], PDO::PARAM_STR);
                    $cmd->bindValue(":year", $lastYear, PDO::PARAM_STR);

                    $data3 = $cmd->query();
                    $data3 = $data3->read();

                    if($data3) {
                        $this->specialWT->Value = 1;
                    }
                }

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
                                            `user_id`=:user_id ,
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
                                            `startDate`=:startDate ,
                                            `remark`=:remark ,
                                            `endOfActivity`=:endOfActivity ,
                                            `holidaysByYear`=:holidaysByYear,
                                            `role`=:role
                                            WHERE id=:id" );

        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
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


        $userId = $this->employee->getSelectedValue();

        //update the employee counter
        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_timecode WHERE defaultHoliday=1");
        $data = $cmd->query();
        $data = $data->read();

        if($data) {

            $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE timecode_id=".$data['id'].' AND user_id='.$this->employee->getSelectedValue());
            $data2 = $cmd->query();
            $data2 = $data2->readAll();

            // the counter don't exist, we must create it
            if(count($data2)==0) {

                $cmd = $this->db->createCommand("INSERT hr_timux_activity_counter SET
                                                    user_id=:user_id,
                                                    timecode_id=:timecode_id,
                                                    year=:year,
                                                    month=:month,
                                                    nbre=:nbre,
                                                    isClosedMonth=:isClosedMonth
                                                ");
                $cmd->bindValue(":user_id",$this->employee->getSelectedValue(),PDO::PARAM_STR);
                $cmd->bindValue(":timecode_id",$data['id'],PDO::PARAM_STR);
                $nbre = 0;
                $nbreLast = 0;

                list($day,$month,$year) = explode("-",$this->from->SafeText);

                $month = 12;
                $year--;

                $cmd->bindValue(":year",$year,PDO::PARAM_STR);
                $cmd->bindValue(":month",$month,PDO::PARAM_STR);

                list($day,$month,$year) = explode("-",$this->from->SafeText);

                $nbre = $this->holidaysByYear->SafeText;
                $nbreLast = $this->holidaysLastYear->SafeText;

                $nbre = bcdiv( bcmul((12-$month+1), $nbre,2), 12.0, 2);
                $nbre = bcadd($nbre, $nbreLast,2 );

                $cmd->bindValue(":nbre",$nbreLast,PDO::PARAM_STR);
                $cmd->bindValue(":isClosedMonth",1,PDO::PARAM_STR);
                $res1 = $cmd->execute();

                $cmd->bindValue(":year",0,PDO::PARAM_STR);
                $cmd->bindValue(":month",0,PDO::PARAM_STR);
                $cmd->bindValue(":nbre",$nbre,PDO::PARAM_STR);
                $cmd->bindValue(":isClosedMonth",0,PDO::PARAM_STR);
                $res1 = $cmd->execute();

            } else {

                $diff = 0;

                list($day,$month,$year) = explode("-",$this->from->SafeText);

                $lastYear = $year-1;

                if($this->specialWT->Value == 1) {
                    $lastYear--;
                }

                $cmd = $this->db->createCommand( "SELECT nbre FROM hr_timux_activity_counter WHERE timecode_id=:defHol AND user_id=:id AND year=:year AND month=12");
                $cmd->bindValue(":defHol", $data['id'], PDO::PARAM_STR);
                $cmd->bindValue(":id", $userId, PDO::PARAM_STR);
                $cmd->bindValue(":year", $lastYear, PDO::PARAM_STR);

                $data2 = $cmd->query();
                $data2 = $data2->read();

                $diff = $data2['nbre'] - $this->holidaysLastYear->SafeText;

                $cmd = $this->db->createCommand( "UPDATE hr_timux_activity_counter SET nbre=nbre-:diff WHERE timecode_id=:defHol AND user_id=:id");
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

        }

        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_timecode WHERE defaultOvertime=1");
        $data = $cmd->query();
        $data = $data->read();

        if($data) {

            $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE timecode_id=".$data['id'].' AND user_id='.$this->employee->getSelectedValue());
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
                $cmd->bindValue(":timecode_id",$data['id'],PDO::PARAM_STR);
                $nbre = 0;
                $nbreLast = 0;

                list($day,$month,$year) = explode("-",$this->from->SafeText);

                $month = 12;
                $year--;

                $cmd->bindValue(":year",$year,PDO::PARAM_STR);
                $cmd->bindValue(":month",$month,PDO::PARAM_STR);

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

                $cmd->bindValue(":nbre",$nbreLast,PDO::PARAM_STR);
                $cmd->bindValue(":isClosedMonth",1,PDO::PARAM_STR);
                $res1 = $cmd->execute();

                $cmd->bindValue(":year",0,PDO::PARAM_STR);
                $cmd->bindValue(":month",0,PDO::PARAM_STR);
                $cmd->bindValue(":nbre",$nbre,PDO::PARAM_STR);
                $cmd->bindValue(":isClosedMonth",0,PDO::PARAM_STR);
                $res1 = $cmd->execute();


            } else {

                $diff = 0;

                list($day,$month,$lastYear) = explode("-",$this->from->SafeText);
                $lastYear--;

                if($this->specialWT->Value == 1) {
                    $lastYear--;
                }

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
        }


        return true;
    }


    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.workingtime.workingtime'));
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
