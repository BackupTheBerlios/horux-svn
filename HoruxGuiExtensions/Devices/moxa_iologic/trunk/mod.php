<?php
/**
 * @version      $Id$
 * @package      Horux
 * @subpackage   Horux
 * @copyright    Copyright (C) 2007  Letux. All rights reserved.
 * @license      GNU/GPL, see LICENSE.php
 * Horux is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

Prado::using('horux.pages.hardware.device.moxa_iologic.sql');

class mod extends ModDevicePage {
    public function onLoad($param) {
        $this->deviceName = "moxa_iologic";
        parent::onLoad($param);
    }

    public function setData() {

        parent::setData();

        $this->ip->Text =  $this->data['ip'];
        $this->port->Text =  $this->data['port'];

        $this->password->Text = $this->data['password'];
        $this->initialOutput->Text = $this->data['initialOutput'];

        $this->setOutputFunc($this->output0_func, $this->data['output0_func']);
        $this->setOutputFunc($this->output1_func, $this->data['output1_func']);
        $this->setOutputFunc($this->output2_func, $this->data['output2_func']);
        $this->setOutputFunc($this->output3_func, $this->data['output3_func']);
        $this->setOutputFunc($this->output4_func, $this->data['output4_func']);
        $this->setOutputFunc($this->output5_func, $this->data['output5_func']);
        $this->setOutputFunc($this->output6_func, $this->data['output6_func']);
        $this->setOutputFunc($this->output7_func, $this->data['output7_func']);

        $this->output0Time->Text = $this->data['output0Time'];
        $this->output1Time->Text = $this->data['output1Time'];
        $this->output2Time->Text = $this->data['output2Time'];
        $this->output3Time->Text = $this->data['output3Time'];
        $this->output4Time->Text = $this->data['output4Time'];
        $this->output5Time->Text = $this->data['output5Time'];
        $this->output6Time->Text = $this->data['output6Time'];
        $this->output7Time->Text = $this->data['output7Time'];
    }

    protected function setOutputFunc($output, $value) {
        $vs = explode(",", $value);

        $output->setSelectedValues($vs);
    }

    public function saveData() {

        parent::saveData();

        $cmd = $this->db->createCommand( SQL::SQL_UPDATE_DEVICE );
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
     
        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
        $cmd->Execute();

        return true;
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
