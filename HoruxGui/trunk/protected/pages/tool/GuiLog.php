<?php

class GuiLog extends PageList
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->IsPostBack)
        {
            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();


        }
    }

    protected function getData()
    {
        $sql = "SELECT * FROM hr_gui_log ORDER BY id DESC LIMIT 0,1000";
        $command=$this->db->createCommand($sql);
        $dataObj=$command->query();
        return $dataObj->readAll();
    }

    public function onRefresh($sender, $param)
    {
        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }
}

?>