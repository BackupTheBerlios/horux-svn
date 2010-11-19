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
