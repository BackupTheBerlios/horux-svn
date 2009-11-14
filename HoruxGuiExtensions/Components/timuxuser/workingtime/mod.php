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
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
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

            $this->hourblocks->setChecked($data['hourblocks']);

            $this->holidaysByYear->Text = $data['holidaysByYear'];

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
        }
    }

    protected function getPersonList()
    {
        $cmd = NULL;
        $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT(name, ' ', firstname) AS Text FROM hr_user WHERE name<>'??'" );
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
                                            `hourblocks`=:hourblocks,
                                            `holidaysByYear`=:holidaysByYear,
                                            `role`=:role
                                            WHERE id=:id" );

        $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);
        $cmd->bindParameter(":user_id",$this->employee->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindParameter(":workingPercent",$this->workingPercent->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":hoursByWeek",$this->hoursByWeek->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":mondayTime_m",$this->monday_m->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":tuesdayTime_m",$this->tuesday_m->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":wednesdayTime_m",$this->wednesday_m->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":thursdayTime_m",$this->thursday_m->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":fridayTime_m",$this->friday_m->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":saturdayTime_m",$this->saturday_m->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":sundayTime_m",$this->sunday_m->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":mondayTime_a",$this->monday_a->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":tuesdayTime_a",$this->tuesday_a->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":wednesdayTime_a",$this->wednesday_a->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":thursdayTime_a",$this->thursday_a->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":fridayTime_a",$this->friday_a->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":saturdayTime_a",$this->saturday_a->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":sundayTime_a",$this->sunday_a->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":startDate",$this->dateToSql($this->from->SafeText), PDO::PARAM_STR);
        $cmd->bindParameter(":remark",$this->remark->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":holidaysByYear",$this->holidaysByYear->SafeText, PDO::PARAM_STR);

        $endActivity = false;
        if($this->endActivity->getChecked())
            $endActivity = true;

        $cmd->bindParameter(":endOfActivity",$endActivity, PDO::PARAM_STR);

        $hourblocks = $this->hourblocks->getChecked();

        $cmd->bindParameter(":hourblocks",$hourblocks,PDO::PARAM_STR);

        $role = 'employee';

        if($this->r_employee->getChecked())
            $role = 'employee';
        if($this->r_manager->getChecked())
            $role = 'manager';
        if($this->r_rh->getChecked())
            $role = 'rh';

        $cmd->bindParameter(":role",$role, PDO::PARAM_STR);

        $res1 = $cmd->execute();

        return $res1;
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
