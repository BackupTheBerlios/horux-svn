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

Prado::using('horux.pages.key.sql');

class recycling extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        $this->setHoruxSysTray(true);

        if(!$this->isPostBack)
        {

            if(isset($this->Request['sn']))
            {
                $this->label->Text = Prado::localize("The key was recycled");

                $cmd = $this->db->createCommand( "UPDATE hr_keys SET isUsed=0 WHERE serialNumber=:serialNumber" );
                $cmd->bindParameter(":serialNumber",$this->Request['sn'], PDO::PARAM_STR);
                $res1 = $cmd->execute();

                $cmd = $this->db->createCommand( "SELECT * FROM hr_keys WHERE serialNumber=:serialNumber" );
                $cmd->bindParameter(":serialNumber",$this->Request['sn'], PDO::PARAM_STR);
                $query = $cmd->query();

                if($query)
                {
                    $data = $query->read();

                    $cmd = $this->db->createCommand( "DELETE FROM hr_keys_attribution WHERE id_key=:id" );
                    $cmd->bindParameter(":id",$data['id'], PDO::PARAM_STR);
                    $res1 = $cmd->execute();

                }


            }
        }
    }

    public function onClearLavel($sender, $param)
    {
        $this->clearLabel->stopTimer();

        $this->label->Text = Prado::localize("Present the key to the reader for the recycle");
    }
}


?>
