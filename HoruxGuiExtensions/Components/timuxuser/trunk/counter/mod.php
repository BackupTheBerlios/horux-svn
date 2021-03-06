<?php


$param = Prado::getApplication()->getParameters();
$computation = $param['computation'];

Prado::using('horux.pages.components.timuxuser.'.$computation);

class mod extends Page
{
    protected $userId = 0;
    protected $employee = null;

    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {

            $this->timecode->DataSource=$this->TimeCode;
            $this->timecode->dataBind();

            $this->employeelst->DataSource=$this->EmployeeList;
            $this->employeelst->dataBind();

            $this->id->Value = $this->Request['id'];
            $this->setData();
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

    protected function setData()
    {
        $cmd = $this->db->createCommand( "SELECT ac.nbre, ac.id, CONCAT('[',tt.abbreviation,'] - ', tt.name) AS timecode, ac.user_id, ac.year, ac.month, ac.day, ac.remark, ac.timecode_id FROM hr_timux_activity_counter AS ac LEFT JOIN hr_timux_timecode AS tt ON tt.id=ac.timecode_id WHERE ac.id=:id");
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();

        if($query)
        {
            $data = $query->read();
            $this->id->Value = $data['id'];
            $this->nbre->Text = sprintf("%.2f",$data['nbre']);
            $this->timecode->setSelectedValue( $data['timecode_id'] );
            $this->employeelst->setSelectedValue( $data['user_id'] );
            $this->date->Text = sprintf("%02d",$data['day'])."-".sprintf("%02d",$data['month'])."-".$data['year'];
            $this->remark->Text = $data['remark'];
        }
    }


    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The activity counter was modified successfully'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.counter.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The activity counter was not modified'), 'id'=>$this->id->Value);
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
                $pBack = array('okMsg'=>Prado::localize('The activity counter was modified successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The activity counter was not modified'));

            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.counter.counter',$pBack));
        }
    }

    protected function saveData()
    {

        $cmd = $this->db->createCommand( "UPDATE `hr_timux_activity_counter` SET
                                            user_id=:user_id,
                                            timecode_id=:timecode_id,
                                            nbre=:nbre,
                                            year=:year,
                                            month=:month,
                                            day=:day,
                                            remark=:remark
                                            WHERE id=:id;" );

        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);

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
