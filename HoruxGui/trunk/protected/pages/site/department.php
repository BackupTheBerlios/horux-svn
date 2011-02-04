<?php


class department extends PageList
{
    protected function getData()
    {

        $cmd=$this->db->createCommand("SELECT * FROM hr_department");
        $data=$cmd->query();

        return $data;
    }

    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->IsPostBack)
        {
            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();
        }

        if(isset($this->Request['okMsg']))
        {
            $this->displayMessage($this->Request['okMsg'], true);
        }
        if(isset($this->Request['koMsg']))
        {
            $this->displayMessage($this->Request['koMsg'], false);
        }

        $param = $this->Application->getParameters();
        $superAdmin = $this->Application->getUser()->getSuperAdmin();

        if($param['appMode'] == 'demo' && $superAdmin == 0)
        {
            $this->tbb->delete->setEnabled(false);
        }
    }

    public function checkboxAllCallback($sender, $param)
    {
        $cbs = $this->findControlsByType("TActiveCheckBox");
        $isChecked = $sender->getChecked();

        foreach($cbs as $cb)
        {
            $cb->setChecked($isChecked);
        }

    }

    public function onDelete($sender,$param)
    {
        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        $koMsg = '';
        $cbChecked = 0;

        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            $cbChecked++;
        }

        if($cbChecked==0)
        {
            $koMsg = Prado::localize('Select one item');
        }
        else
        {

            foreach($cbs as $cb)
            {

                if( (bool)$cb->getChecked() && $cb->Value != "0")
                {
                    $id = $cb->Value;

                    if($id>0)
                    {

                        $cmd=$this->db->createCommand("SELECT name FROM hr_department WHERE id=".$id);
                        $query = $cmd->query();
                        $data = $query->read();

                        $this->log("Delete the department: ".$data['name']);

                        $cmd=$this->db->createCommand("DELETE FROM hr_department WHERE id=:id");
                        $cmd->bindValue(":id",$id);
                        $res = $cmd->query();


                        $nDelete++;
                    }
                }
            }
        }

        if($koMsg !== '')
            $pBack = array('koMsg'=>$koMsg);
        else
            $pBack = array('okMsg'=>Prado::localize('{n} department(s) was deleted',array('n'=>$nDelete)));
        $this->Response->redirect($this->Service->constructUrl('site.department',$pBack));
    }


    public function onEdit($sender,$param)
    {

        if(count($this->DataGrid->DataKeys) === 0)
        {
            $pBack = array('koMsg'=>Prado::localize('Select one item'));
            $this->Response->redirect($this->Service->constructUrl('site.department',$pBack));

        }

        $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];

        if(is_numeric($id))
        {
            $pBack = array('id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('site.mod',$pBack));
        }

        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            {
                $pBack = array('id'=>$cb->Value);
                $this->Response->redirect($this->Service->constructUrl('site.mod',$pBack));
            }
        }

        $pBack = array('koMsg'=>Prado::localize('Select one item'));
        $this->Response->redirect($this->Service->constructUrl('site.department',$pBack));
    }
}
?>
