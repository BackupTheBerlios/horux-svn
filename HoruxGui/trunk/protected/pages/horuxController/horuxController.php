<?php


Prado::using('horux.pages.hardware.sql');

class horuxController extends PageList
{

    protected function getData()
    {

        $command=$this->db->createCommand("SELECT * FROM hr_horux_controller ORDER BY type");
        $dataReader=$command->query();

        $connection->Active=false;  // connection is established

        return $dataReader;
    }

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
        {

            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();

            $param = $this->Application->getParameters();
            $superAdmin = $this->Application->getUser()->getSuperAdmin();

            if($param['appMode'] == 'demo' && $superAdmin == 0)
            {
                $this->tbb->delete->setEnabled(false);
            }
        }

        if(isset($this->Request['okMsg']))
        {
            $this->displayMessage($this->Request['okMsg'], true);
        }
        if(isset($this->Request['koMsg']))
        {
            $this->displayMessage($this->Request['koMsg'], false);
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
        $cbChecked = 0;
        $koMsg = '';

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
                $id = $cb->Value;

                if( (bool)$cb->getChecked() && $cb->Value != "0")
                {
                    // Check for child device
                    $cmd=$this->db->createCommand("SELECT * FROM hr_device WHERE horuxControllerId=".$id);
                    $data = $cmd->query();
                    $data = $data->read();

                    if ($data) {
                        $pBack = array('koMsg'=>Prado::localize('Cannot delete a parent Horux Controller'));
                        $this->Response->redirect($this->Service->constructUrl('horuxController.horuxController',$pBack));
                    }
                    else {
                      $cmd=$this->db->createCommand("SELECT * FROM hr_horux_controller WHERE id=".$id);
                      $data = $cmd->query();
                      $data = $data->read();

                      if($data['id'] != 1) {

                          $cmd=$this->db->createCommand("DELETE FROM hr_horux_controller WHERE id=".$id);
                          $cmd->execute();
                          $nDelete++;
                      }
                      else {
                          $pBack = array('koMsg'=>Prado::localize('Cannot delete the master Horux Controller'));
                          $this->Response->redirect($this->Service->constructUrl('horuxController.horuxController',$pBack));
                      }
                    }
                }
            }
        }

        if($koMsg !== '')
            $pBack = array('koMsg'=>$koMsg);
        else
            $pBack = array('okMsg'=>Prado::localize('{n} Horux Controller was deleted',array('n'=>$nDelete)));
        $this->Response->redirect($this->Service->constructUrl('horuxController.horuxController',$pBack));
    }


    public function onEdit($sender,$param)
    {

        if(count($this->DataGrid->DataKeys) === 0)
        {
            $pBack = array('koMsg'=>Prado::localize('Select one item'));
            $this->Response->redirect($this->Service->constructUrl('horuxController.horuxController',$pBack));

        }

        $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];
        if(is_numeric($id))
        {
            $pBack = array('id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('horuxController.mod',$pBack));
        }

        $cbs = $this->findControlsByType("TActiveCheckBox");

        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            {
                $pBack = array('id'=>$cb->Value);
                $this->Response->redirect($this->Service->constructUrl('key.mod',$pBack));
            }
        }

        $pBack = array('koMsg'=>Prado::localize('Select one item'));
        $this->Response->redirect($this->Service->constructUrl('horuxController.horuxControlle',$pBack));
    }

    
}

?>
