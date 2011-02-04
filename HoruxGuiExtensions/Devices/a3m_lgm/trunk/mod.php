<?php

Prado::using('horux.pages.hardware.device.a3m_lgm.sql');

class mod extends ModDevicePage {
    public function onLoad($param) {
        $this->deviceName = "a3m_lgm";
        parent::onLoad($param);
    }

    public function setData() {

        parent::setData();

        $this->address->Text =  $this->data['address'];
        $this->serialNumberFormat->Text =  $this->data['serialNumberFormat'];
    }

    public function saveData() {
        parent::saveData();

        $cmd = $this->db->createCommand( SQL::SQL_UPDATE_DEVICE );
        $cmd->bindValue(":address",$this->address->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":serialNumberFormat",$this->serialNumberFormat->SafeText,PDO::PARAM_STR);
        
        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
        $cmd->Execute();

        return true;
    }

}
