<?php


Prado::using('horux.pages.openTime.sql');

class attribute extends Page
{

    public function getData()
    {
        $id = $this->Request['id'];
        $cmd = $this->db->createCommand( SQL::SQL_GET_ATTRIBUTION );
        $cmd->bindValue(":id",$id,PDO::PARAM_INT);
        $data=$cmd->query();
        $connection->Active=false;

        return $data;
    }

    public function getOpenTime()
    {
        $id = $this->Request['id'];
        $cmd = $this->db->createCommand( SQL::SQL_GET_ALL_OPEN_TIME2 );
        $cmd->bindValue(":id",$id,PDO::PARAM_INT);
        $data=$cmd->query();
        $connection->Active=false;

        return $data;
    }

    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->IsPostBack)
        {
            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();

            $this->id->Value = $this->Request['id'];
        }

        $this->OpenTime->DataSource=$this->getOpenTime();
        $this->OpenTime->dataBind();

        if($this->OpenTime->getItemCount())
        $this->OpenTime->setSelectedIndex(0);

        if(isset($this->Request['okMsg']))
        {
            $this->displayMessage($this->Request['okMsg'], true);
        }
        if(isset($this->Request['koMsg']))
        {
            $this->displayMessage($this->Request['koMsg'], false);
        }
    }

    public function onAttribute($sender,$param)
    {
        $id_device = $this->id->Value;
        $id_openTime = $this->OpenTime->getSelectedValue();

        if($id_openTime)
        {

            $cmd=$this->db->createCommand(SQL::SQL_ATTRIBUTE_OPEN_TIME);
            $cmd->bindValue(":id_device",$id_device);
            $cmd->bindValue(":id_openTime",$id_openTime);
            $cmd->execute();

            $horuxService = new THoruxService();
            $horuxService->onStopDevice($id_device);
            $horuxService->onStartDevice($id_device);

            $sa = new TStandAlone();
            $sa->addStandalone("add", $id_device, 'reinit');

            $this->Response->redirect($this->Service->constructUrl('openTime.attribute',array('id'=>$id_device)));
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

    public function onUnAttribute($sender, $param)
    {
        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nUnAttributed = 0;
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

                    $cmd = $this->db->createCommand("SELECT * FROM hr_openTime_attribution WHERE id=:id");
                    $cmd->bindValue(":id",$cb->Value);
                    $data = $cmd->query();
                    $data = $data->read();

                    $deviceId = $data['id_device'];

                    $cmd=$this->db->createCommand(SQL::SQL_DELETE_OPEN_TIME_ATTRIBUTION);
                    $cmd->bindValue(":id",$cb->Value);

                    if($cmd->execute())
                    {
                        $nUnAttributed++;


                        $horuxService = new THoruxService();
                        $horuxService->onStopDevice($deviceId);
                        $horuxService->onStartDevice($deviceId);

                        $sa = new TStandAlone();
                        $sa->addStandalone("add", $deviceId, 'reinit');
                    }
                }
            }
        }

        if($koMsg !== '')
        {
            $pBack = array('id'=>$this->id->Value, 'koMsg'=>$koMsg);
            $this->Response->redirect($this->Service->constructUrl('openTime.attribute',$pBack));

        }
        else
        {
            $pBack = array('id'=>$this->Request['id'],'okMsg'=>Prado::localize('{n} open time was unattributed',array('n'=>$nUnAttributed)));
            $this->Response->redirect($this->Service->constructUrl('openTime.attribute',$pBack));
        }
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('hardware.HardwareList'));
    }
}

?>
