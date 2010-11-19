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
