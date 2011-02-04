<?php


Prado::using('horux.pages.hardware.device.horux_lcddisplay.sql');

class mod extends ModDevicePage {
    public function onLoad($param) {
        $this->deviceName = "horux_lcddisplay";
        parent::onLoad($param);
    }

    public function setData() {

        parent::setData();

        $this->ip->Text =  $this->data['ip'];
        $this->port->Text =  $this->data['port'];
        $this->messageTimerDisplay->Text =  $this->data['messageTimerDisplay'];
        $this->defaultMessage->Text = $this->data['defaultMessage'];

    }


    public function saveData() {

        parent::saveData();

        $cmd = $this->db->createCommand( SQL::SQL_UPDATE_LCDDISPLAY );
        $cmd->bindValue(":ip",$this->ip->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":port",$this->port->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":messageTimerDisplay",$this->messageTimerDisplay->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":defaultMessage",$this->defaultMessage->SafeText,PDO::PARAM_STR);
        
        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
        $cmd->Execute();

        return true;
    }
}
