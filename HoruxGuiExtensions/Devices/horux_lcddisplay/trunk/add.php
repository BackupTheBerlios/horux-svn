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

class add extends AddDevicePage {
    
    public function onLoad($param) {
        $this->deviceName = "horux_lcddisplay";
        $this->isAccessDevice = 0;

        parent::onLoad($param);
    }

    public function saveData() {
        parent::saveData();

        if($this->lastId !== false) {
            $cmd = $this->db->createCommand( SQL::SQL_ADD_LCDDISPLAY );
            $cmd->bindValue(":ip",$this->ip->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":port",$this->port->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":id_device",$this->lastId,PDO::PARAM_STR);
            $cmd->bindValue(":messageTimerDisplay",$this->messageTimerDisplay->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":defaultMessage",$this->defaultMessage->SafeText,PDO::PARAM_STR);

            $cmd->Execute();


            return true;
        } else {
            return false;
        }
    }

}
