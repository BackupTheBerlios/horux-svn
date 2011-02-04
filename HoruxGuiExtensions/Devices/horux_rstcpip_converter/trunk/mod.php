<?php


Prado::using('horux.pages.hardware.device.horux_rstcpip_converter.sql');

class mod extends ModDevicePage {
    public function onLoad($param) {
        $this->deviceName = "horux_rstcpip_converter";
        parent::onLoad($param);
    }


    public function setData() {

        parent::setData();

        $this->ip->Text =  $this->data['ip'];
        $this->port->Text =  $this->data['port'];

    }

    public function saveData() {
        parent::saveData();

        $cmd = $this->db->createCommand( SQL::SQL_UPDATE_DEVICE );
        $cmd->bindValue(":ip",$this->ip->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":port",$this->port->SafeText,PDO::PARAM_STR);        
        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
        $cmd->Execute();

        return true;
    }
}
