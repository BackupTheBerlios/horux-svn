<?php


class mod extends Page
{
    protected $listBox = array();

    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->IsPostBack)
        {
            $this->id->Value = $this->Request['id'];
            $this->setData();
        }

      $param = $this->Application->getParameters();
      $superAdmin = $this->Application->getUser()->getSuperAdmin();

      if($param['appMode'] == 'demo' && $superAdmin == 0)
      {
              $this->tbb->Save->setEnabled(false);
              $this->tbb->apply->setEnabled(false);
      }

    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_department WHERE id=:id" );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            $this->id->Value = $data['id'];
            $this->name->Text = $data['name'];
            $this->comment->Text = $data['description'];
        }
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The department was modified successfully'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('site.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The department was not modified'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('site.mod',$pBack));
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
        $cmd = $this->db->createCommand( "UPDATE hr_department SET name=:name, description=:description WHERE id=:id" );
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":description",$this->comment->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);

        if(!$cmd->execute()) return false;


        $this->log("Modifed the department: ".$this->name->SafeText);

        return true;
    }
}
