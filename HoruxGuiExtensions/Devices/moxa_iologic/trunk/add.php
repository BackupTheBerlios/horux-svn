<?php

Prado::using('horux.pages.hardware.device.moxa_iologic.sql');

class add extends AddDevicePage {
    protected $lastId;
    public function onLoad($param) {
        $this->deviceName = "moxa_iologic";
        $this->isAccessDevice = 0;

        parent::onLoad($param);
    }

    public function saveData() {
        parent::saveData();

        if($this->lastId !== false) {
            $cmd = $this->db->createCommand( SQL::SQL_ADD_DEVICE );
            $cmd->bindValue(":id_device",$this->lastId,PDO::PARAM_STR);
            $cmd->bindValue(":ip",$this->ip->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":port",$this->port->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":password",$this->password->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":initialOutput",$this->initialOutput->SafeText,PDO::PARAM_STR);


            $cmd->bindValue(":output0_func",$this->getOuptutFunc($this->output0_func),PDO::PARAM_STR);
            $cmd->bindValue(":output1_func",$this->getOuptutFunc($this->output1_func),PDO::PARAM_STR);
            $cmd->bindValue(":output2_func",$this->getOuptutFunc($this->output2_func),PDO::PARAM_STR);
            $cmd->bindValue(":output3_func",$this->getOuptutFunc($this->output3_func),PDO::PARAM_STR);
            $cmd->bindValue(":output4_func",$this->getOuptutFunc($this->output4_func),PDO::PARAM_STR);
            $cmd->bindValue(":output5_func",$this->getOuptutFunc($this->output5_func),PDO::PARAM_STR);
            $cmd->bindValue(":output6_func",$this->getOuptutFunc($this->output6_func),PDO::PARAM_STR);
            $cmd->bindValue(":output7_func",$this->getOuptutFunc($this->output7_func),PDO::PARAM_STR);

            $cmd->bindValue(":output0Time",$this->output0Time->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":output1Time",$this->output1Time->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":output2Time",$this->output2Time->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":output3Time",$this->output3Time->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":output4Time",$this->output4Time->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":output5Time",$this->output5Time->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":output6Time",$this->output6Time->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":output7Time",$this->output7Time->SafeText,PDO::PARAM_STR);

            $cmd->Execute();

            return true;
          } else {
            return false;
          }
    }

    protected function getOuptutFunc($output) {
        $indices=$output->SelectedIndices;
        $result='';
        foreach($indices as $index)
        {
            $item=$output->Items[$index];
            $result[] = $item->Value;
        }

        return implode(',',$result);


    }
}
