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
            $this->daysByWeek->Text = $data['daysByWeek'];
            $this->breakMinimum->Text = $data['minimumBreaks'];
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

        $cmd->bindParameter(":daysByWeek",$this->daysByWeek->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":minimumBreaks",$this->breakMinimum->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":bookingRounding",$this->rounding->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindParameter(":hoursByWeek",$this->defaultHourByWeek->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":holidayByYear",$this->defaultHolidayByYear->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":hoursBlockMorning1",$this->hoursBlockMorning1->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":hoursBlockMorning2",$this->hoursBlockMorning2->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":hoursBlockMorning3",$this->hoursBlockMorning3->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":hoursBlockMorning4",$this->hoursBlockMorning4->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":hoursBlockAfternoon1",$this->hoursBlockAfternoon1->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":hoursBlockAfternoon2",$this->hoursBlockAfternoon2->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":hoursBlockAfternoon3",$this->hoursBlockAfternoon3->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":hoursBlockAfternoon4",$this->hoursBlockAfternoon4->SafeText,PDO::PARAM_STR);

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
