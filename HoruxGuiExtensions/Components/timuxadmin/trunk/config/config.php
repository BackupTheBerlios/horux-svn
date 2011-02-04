<?php


class config extends Page
{

    protected $emailError = '';

    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {
            $this->setData();
        }

        $superAdmin = $this->Application->getUser()->getSuperAdmin();
        $param = $this->Application->getParameters();

        if($param['appMode'] == 'demo' && $superAdmin == 0)
        {
            $this->tbb->Save->setEnabled(false);
        }
    }

    public function setData()
    {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_config WHERE id=1" );
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();

            $workginDays = explode(",", $data['workingDays']);

            $this->mon->setChecked($workginDays[0]);
            $this->tue->setChecked($workginDays[1]);
            $this->wed->setChecked($workginDays[2]);
            $this->thu->setChecked($workginDays[3]);
            $this->fri->setChecked($workginDays[4]);
            $this->sat->setChecked($workginDays[5]);
            $this->sun->setChecked($workginDays[6]);


            $this->breakMinimum->setChecked( $data['minimumBreaks'] );
            $this->rounding->setSelectedValue($data['bookingRounding']);
            $this->defaultHourByWeek->Text = $data['hoursByWeek'];
            $this->defaultHolidayByYear->Text = $data['holidayByYear'];

            $this->hoursBlockMorning1->Text = $data['hoursBlockMorning1'];
            $this->hoursBlockMorning2->Text = $data['hoursBlockMorning2'];
            $this->hoursBlockMorning3->Text = $data['hoursBlockMorning3'];
            $this->hoursBlockMorning4->Text = $data['hoursBlockMorning4'];

            $this->hoursBlockAfternoon1->Text = $data['hoursBlockAfternoon1'];
            $this->hoursBlockAfternoon2->Text = $data['hoursBlockAfternoon2'];
            $this->hoursBlockAfternoon3->Text = $data['hoursBlockAfternoon3'];
            $this->hoursBlockAfternoon4->Text = $data['hoursBlockAfternoon4'];
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {

            $res = $this->saveData();

            if($res)
            {
                $pBack = array('okMsg'=>Prado::localize('The config was modified successfully'));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The config was not modified').$this->emailError);

            }
            $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.config.config',$pBack));
        }
    }

    public function saveData()
    {
        $cmd = $this->db->createCommand( "UPDATE hr_timux_config SET 
                                            daysByWeek=:daysByWeek,
                                            workingDays=:workingDays,
                                            minimumBreaks=:minimumBreaks,
                                            bookingRounding=:bookingRounding,
                                            hoursByWeek=:hoursByWeek,
                                            holidayByYear=:holidayByYear,
                                            hoursBlockMorning1=:hoursBlockMorning1,
                                            hoursBlockMorning2=:hoursBlockMorning2,
                                            hoursBlockMorning3=:hoursBlockMorning3,
                                            hoursBlockMorning4=:hoursBlockMorning4,
                                            hoursBlockAfternoon1=:hoursBlockAfternoon1,
                                            hoursBlockAfternoon2=:hoursBlockAfternoon2,
                                            hoursBlockAfternoon3=:hoursBlockAfternoon3,
                                            hoursBlockAfternoon4=:hoursBlockAfternoon4
                                            WHERE id=1
                                            " );

        $daysByWeek = 0;
        $workingDays = array(0,0,0,0,0,0,0);

        if($this->mon->getChecked()) {
            $daysByWeek++;
            $workingDays[0] = 1;
        }
        if($this->tue->getChecked()) {
            $daysByWeek++;
            $workingDays[1] = 1;
        }
        if($this->wed->getChecked()) {
            $daysByWeek++;
            $workingDays[2] = 1;
        }
        if($this->thu->getChecked()) {
            $daysByWeek++;
            $workingDays[3] = 1;
        }
        if($this->fri->getChecked()) {
            $daysByWeek++;
            $workingDays[4] = 1;
        }
        if($this->sat->getChecked()) {
            $daysByWeek++;
            $workingDays[5] = 1;
        }
        if($this->sun->getChecked()) {
            $daysByWeek++;
            $workingDays[6] = 1;
        }

        $cmd->bindValue(":daysByWeek",$daysByWeek,PDO::PARAM_STR);
        $cmd->bindValue(":workingDays",implode(",", $workingDays),PDO::PARAM_STR);

        $cmd->bindValue(":minimumBreaks",$this->breakMinimum->getChecked(),PDO::PARAM_STR);
        $cmd->bindValue(":bookingRounding",$this->rounding->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":hoursByWeek",$this->defaultHourByWeek->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":holidayByYear",$this->defaultHolidayByYear->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":hoursBlockMorning1",$this->hoursBlockMorning1->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":hoursBlockMorning2",$this->hoursBlockMorning2->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":hoursBlockMorning3",$this->hoursBlockMorning3->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":hoursBlockMorning4",$this->hoursBlockMorning4->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":hoursBlockAfternoon1",$this->hoursBlockAfternoon1->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":hoursBlockAfternoon2",$this->hoursBlockAfternoon2->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":hoursBlockAfternoon3",$this->hoursBlockAfternoon3->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":hoursBlockAfternoon4",$this->hoursBlockAfternoon4->SafeText,PDO::PARAM_STR);


        $cmd->execute();

        $this->log("Modify the Timux configuration");

        return true;
    }


    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.panel'));
    }
}

?>
