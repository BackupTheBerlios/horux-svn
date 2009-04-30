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

Prado::using('horux.pages.hardware.device.accessLink_Interface.sql');

class add extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        
       if(!$this->isPostBack)
        {
			$param = $this->Application->getParameters();
			$superAdmin = $this->Application->getUser()->getSuperAdmin();
	
			if($param['appMode'] == 'demo' && $superAdmin == 0)
			{
				$this->Save->setEnabled(false);
				$this->Apply->setEnabled(false);
			}           
        }        
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($id = $this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The interface was added successfully'), 'id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('hardware.device.accessLink_Interface.mod', $pBack));
          }
          else
          {
            $pBack = array('koMsg'=>Prado::localize('The interface was not added'));
          }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The interface was added successfully'));
          }
          else
            $pBack = array('koMsg'=>Prado::localize('The interface was not added'));
          $this->Response->redirect($this->Service->constructUrl('hardware.HardwareList',$pBack));
        }
    }

    protected function saveData()
    {
      $cmd = $this->db->createCommand( SQL::SQL_ADD_DEVICE );
 	  $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":description",$this->comment->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":isLog",$this->isLog->getChecked(),PDO::PARAM_STR);
      $cmd->execute();

      $lastId = $this->db->getLastInsertID();

      //! if the user don't give an ip address for s2 and s3, take the value from s1
      if(empty($this->server2->SafeText))
              $this->server2->Text = $this->server1->SafeText; 
      if(empty($this->server3->SafeText))
              $this->server3->Text = $this->server1->SafeText;
  
      $cmd = $this->db->createCommand( SQL::SQL_ADD_INTERFACE );
      $cmd->bindParameter(":id_device",$lastId);
      $cmd->bindParameter(":ip",$this->ip->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":mask",$this->mask->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":gateway",$this->gateway->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":data_port",$this->data_port->SafeText, PDO::PARAM_INT);
      $cmd->bindParameter(":server1",$this->server1->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":server2",$this->server2->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":server3",$this->server3->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":temp_max",$this->temp_max->SafeText, PDO::PARAM_INT);

      $cmd->execute();

      return $lastId;


    }

    public function serverValidateName($sender, $param)
    {
      $cmd = $this->db->createCommand( SQL::SQL_IS_NAME_EXIST);
      $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
      $array = $cmd->query()->readAll();

      if(count($array) > 0)
        $param->IsValid=false;
      else 
        $param->IsValid=true;
	}

    public function serverValidateIp($sender, $param)
    {
      $cmd = $this->db->createCommand( SQL::SQL_IS_IP_EXIST);
      $cmd->bindParameter(":ip",$this->ip->SafeText,PDO::PARAM_STR);
      $array = $cmd->query()->readAll();

      if(count($array) > 0)
        $param->IsValid=false;
      else 
        $param->IsValid=true;
    }

}
