<?php


$param = Prado::getApplication()->getParameters();
$computation = $param['computation'];

Prado::using('horux.pages.components.timuxuser.'.$computation);

class add extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {
            $this->timecode->DataSource=$this->TimeCode;
            $this->timecode->dataBind();
            $this->timecode->setSelectedIndex(0);


            $this->employeelst->DataSource=$this->EmployeeList;
            $this->employeelst->dataBind();

        }
    }

    protected function getTimeCode()
    {

        $cmd = $this->db->createCommand( "SELECT CONCAT('[',abbreviation,'] - ', name) AS Text, id AS Value FROM hr_timux_timecode");
        $data = $cmd->query();
        return $data->readAll();

    }

    public function getEmployeeList()
    {
        $cmd = $this->db->createCommand( "SELECT CONCAT(name, ' ', firstname) AS Text, id AS Value FROM hr_user WHERE name!='??' ORDER BY name, firstname");

        $data = $cmd->query();
        return $data->readAll();

    }




    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The activity counter was added successfully'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.counter.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The activity counter was not added'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.counter.mod', $pBack));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The activity counter was added successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The activity counter was not added'));

            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.counter.counter',$pBack));
        }
    }

    protected function saveData()
    {

        $cmd = $this->db->createCommand( "INSERT INTO `hr_timux_activity_counter` (user_id, timecode_id, nbre, year, month, day, isClosedMonth, remark) VALUES (
                                            :user_id,
                                            :timecode_id,
                                            :nbre,
                                            :year,
                                            :month,
                                            :day,
                                            0,
                                            :remark
                                            );" );

        $date = explode("-", $this->date->SafeText);

        $cmd->bindValue(":user_id",$this->employeelst->getSelectedValue(), PDO::PARAM_STR);
        $cmd->bindValue(":timecode_id",$this->timecode->getSelectedValue(), PDO::PARAM_STR);
        $cmd->bindValue(":nbre",$this->nbre->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":year",$date[2], PDO::PARAM_STR);
        $cmd->bindValue(":month",$date[1], PDO::PARAM_STR);
        $cmd->bindValue(":day",$date[0], PDO::PARAM_STR);
        $cmd->bindValue(":remark",$this->remark->SafeText, PDO::PARAM_STR);

        $res1 = $cmd->execute();

        return $res1;
    }


    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.counter.counter'));
    }
}
