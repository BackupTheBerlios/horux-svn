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
    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->isPostBack)
        {
            $this->id->Value = $this->Request['id'];
            $this->setData();
            $this->onTypeChanged(NULL,NULL);
        }
    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_timecode WHERE id=:id");
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();

        if($query)
        {
            $data = $query->read();
            $this->id->Value = $data['id'];
            $this->type->setSelectedValue($data['type']);
            $this->name->Text = $data['name'];
            $this->deviceDisplay->Text = $data['deviceDisplay'];
            $this->abbreviation->Text = $data['abbreviation'];
            $this->useMinMax->setChecked($data['useMinMax']);
            $this->timeworked->setChecked($data['timeworked']);
            $this->minHour->Text = $data['minHour'];
            $this->maxHour->Text = $data['maxHour'];


            if($data['formatDisplay'] == 'hour')
            {
                $this->formatHour->setChecked(true);
                $this->formatDay->setChecked(false);
            }
            if($data['formatDisplay'] == 'day')
            {
                $this->formatHour->setChecked(false);
                $this->formatDay->setChecked(true);
            }

            if($data['defaultHoliday'] == 1)
                $this->defaultHoliday->setChecked(true);

            if($data['defaultOvertime'] == 1)
                $this->defaultOvertime->setChecked(true);



            $this->signtype->setSelectedValue($data['signtype']);
        }
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The time code was modified successfully'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timecode.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The time code was not modified'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timecode.mod', $pBack));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The time code was modified successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The time code was modified'));
            $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timecode.timecode',$pBack));
        }
    }

    protected function saveData()
    {

        $cmd = $this->db->createCommand( "UPDATE `hr_timux_timecode` SET
                                            `name`=:name ,
                                            `abbreviation`=:abbreviation,
                                            `type`=:type,
                                            `useMinMax`=:useMinMax,
                                            `minHour`=:minHour,
                                            `maxHour`=:maxHour,
                                            `defaultHoliday` =:defaultHoliday,
                                            `defaultOvertime` =:defaultOvertime,
                                            `formatDisplay` =:formatDisplay,
                                            `signtype`=:signtype,
                                            `timeworked`=:timeworked,
                                            `deviceDisplay`=:deviceDisplay
                                            WHERE id=:id
                                            ;" );


        $cmd->bindParameter(":type",$this->type->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindParameter(":name",$this->name->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":deviceDisplay",$this->deviceDisplay->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":abbreviation",$this->abbreviation->SafeText, PDO::PARAM_STR);

        $useMinMax = false;
        if($this->useMinMax->getChecked())
            $useMinMax=  true;
        else
        {
           $this->minHour->Text = 0;
           $this->maxHour->Text = 0;
        }

        $timeworked = false;
        if($this->timeworked->getChecked())
            $timeworked = true;

        $cmd->bindParameter(":timeworked",$timeworked, PDO::PARAM_STR);

        $cmd->bindParameter(":useMinMax",$useMinMax, PDO::PARAM_STR);
        $cmd->bindParameter(":minHour",$this->minHour->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":maxHour",$this->maxHour->SafeText, PDO::PARAM_STR);
       
        $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);


        $checkO = 0;
        if($this->defaultOvertime->getChecked())
            $checkO = 1;

        $cmd->bindParameter(":defaultOvertime",$checkO, PDO::PARAM_STR);

        $checkH = 0;
        if($this->defaultHoliday->getChecked())
            $checkH = 1;
        $cmd->bindParameter(":defaultHoliday",$checkH, PDO::PARAM_STR);


        $format = "hour";

        if($this->formatHour->getChecked())
            $format = 1;

        if($this->formatDay->getChecked())
            $format = 2;

        $cmd->bindParameter(":formatDisplay",$format, PDO::PARAM_STR);

        if($checkO == 1)
        {
            $cmdU = $this->db->createCommand( "UPDATE `hr_timux_timecode` SET
                                                `defaultOvertime` =0
                                                ;" );
            $cmdU->execute();

        }

        if($checkH == 1)
        {
            $cmdU = $this->db->createCommand( "UPDATE `hr_timux_timecode` SET
                                                `defaultHoliday` =0
                                                ;" );
            $cmdU->execute();

        }

        $cmd->bindParameter(":signtype",$this->signtype->getSelectedValue(), PDO::PARAM_STR);

        $res = $cmd->execute();

        if($this->signtype->getSelectedValue() != 'none')
        {
            $sa = new TStandAlone();
            $sa->addStandalone("sub", $this->id->Value, 'timuxAddSubReason');
            $sa->addStandalone("add", $this->id->Value, 'timuxAddSubReason');
        }

        return $res;
    }


    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timecode.timecode'));
    }


    public function onTypeChanged($sender, $param)
    {
        if($this->type->getSelectedValue() == 'overtime')
        {
            $this->useMinMax->setEnabled(true);
            $this->minHour->setEnabled(true);
            $this->maxHour->setEnabled(true);
        }
        else
        {
            $this->useMinMax->setEnabled(false);
            $this->minHour->setEnabled(false);
            $this->maxHour->setEnabled(false);

            $this->useMinMax->setChecked(false);
            $this->minHour->Text = "";
            $this->maxHour->Text = "";
        }
    }

    public function defaultChange($sender, $param)
    {
        if($sender == $this->defaultHoliday)
        {
            if($this->defaultHoliday->getChecked())
                $this->defaultOvertime->setChecked(false);
        }

        if($sender == $this->defaultOvertime)
        {
            if($this->defaultOvertime->getChecked())
                $this->defaultHoliday->setChecked(false);
        }
    }
}
