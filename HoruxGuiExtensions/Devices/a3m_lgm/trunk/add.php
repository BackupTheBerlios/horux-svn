<?php

Prado::using('horux.pages.hardware.device.a3m_lgm.sql');

class add extends AddDevicePage {

    public function onLoad($param) {
        $this->deviceName = "a3m_lgm";

        parent::onLoad($param);
        
    }

    public function saveData() {
        parent::saveData();

        if($this->lastId !== false) {
            $cmd = $this->db->createCommand( SQL::SQL_ADD_DEVICE );
            $cmd->bindValue(":address",$this->address->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":id_device",$this->lastId,PDO::PARAM_STR);
            $cmd->bindValue(":serialNumberFormat",$this->serialNumberFormat->SafeText,PDO::PARAM_STR);

            $cmd->Execute();

            return true;
        } else {
            return false;
        }
    }

}
