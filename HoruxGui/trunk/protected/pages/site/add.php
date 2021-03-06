<?php


class add extends Page
{
    protected $listBox = array();

    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->IsPostBack)
        {

        }

    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($lastId = $this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The department was added successfully'), 'id'=>$lastId);
                $this->Response->redirect($this->Service->constructUrl('site.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The department was not added'));
                $this->Response->redirect($this->Service->constructUrl('site.add',$pBack));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The department was added successfully'));
            }
            else
            $pBack = array('koMsg'=>Prado::localize('The department was not added'));

            $this->Response->redirect($this->Service->constructUrl('site.department',$pBack));
        }
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('site.department'));
    }

    protected function saveData()
    {
        $cmd = $this->db->createCommand( "INSERT hr_department SET name=:name, description=:description" );
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":description",$this->comment->SafeText,PDO::PARAM_STR);

        if(!$cmd->execute()) return false;

        $lastId = $this->db->getLastInsertID();


        $this->log("Add the department: ".$this->name->SafeText);

        return $lastId;
    }
}
