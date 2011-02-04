<?php

Prado::using('horux.pages.hardware.device.horux_rstcpip_converter.sql');

class add extends AddDevicePage {
    protected $lastId;
    public function onLoad($param) {
        $this->deviceName = "horux_rstcpip_converter";
        $this->isAccessDevice = 0;

        parent::onLoad($param);
    }

    public function saveData() {
        parent::saveData();


        if($this->lastId !== false) {
            $cmd = $this->db->createCommand( SQL::SQL_ADD_DEVICE );
            $cmd->bindValue(":ip",$this->ip->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":port",$this->port->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":id_device",$this->lastId,PDO::PARAM_STR);

            $cmd->Execute();


            return true;
        } else {
            return false;
        }
    }

}
