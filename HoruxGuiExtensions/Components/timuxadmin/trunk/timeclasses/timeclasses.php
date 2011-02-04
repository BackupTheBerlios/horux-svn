<?php

class timeclasses extends PageList
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        $cmd=$this->db->createCommand("SELECT id
                                            FROM `hr_timux_booking_bde` AS tbb
                                            LEFT JOIN hr_timux_booking AS tb ON tbb.tracking_id = tb.tracking_id
                                            WHERE `action` =254");

        $data = $cmd->query();
        $data = $data->readAll();

        foreach($data as $d) {

            $cmd=$this->db->createCommand("UPDATE hr_timux_booking_bde SET tracking_id = tracking_id +1 WHERE id=".$d['id']);
            $cmd->execute();
        }


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
    }

    public function getData()
    {
        $cmd=$this->db->createCommand("SELECT * FROM hr_timux_timeclass ORDER BY id");
        $data = $cmd->query();
        $data = $data->readAll();

        return $data;
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.panel'));
    }

    public function itemCreated($sender, $param)
    {
        $item=$param->Item;


        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' )
        {
            $item->mmultiplier->multiplier->Text = sprintf("%.2f",$item->DataItem['multiplier']);
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
                    $cmd=$this->db->createCommand("DELETE FROM hr_timux_timeclass WHERE id=:id");
                    $cmd->bindValue(":id",$cb->Value);
                    if($cmd->execute())
                    {
                        $nDelete++;
                    }
                    //$this->log("Delete the key: ".$data['serialNumber']);

                }
            }
        }

        if($koMsg !== '')
            $pBack = array('koMsg'=>$koMsg);
        else
            $pBack = array('okMsg'=>Prado::localize('{n} time classes was deleted',array('n'=>$nDelete)));

        $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timeclasses.timeclasses',$pBack));
    }

    public function onEdit($sender,$param)
    {
        if(count($this->DataGrid->DataKeys) === 0)
        {
            $pBack = array('koMsg'=>Prado::localize('Select one item'));
            $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timeclasses.timeclasses',$pBack));

        }

        $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];
        if(is_numeric($id))
        {
            $pBack = array('id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timeclasses.mod',$pBack));
        }

        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            {
                $pBack = array('id'=>$cb->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timeclasses.mod',$pBack));
            }
        }

        $pBack = array('koMsg'=>Prado::localize('Select one item'));
        $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timeclasses.timeclasses',$pBack));
    }
}

?>