<?php
/**
* @version      $Id$
* @package      Horux
* @subpackage   Horux
* @copyright    Copyright (C) 2007  Letux. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* Horus is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

class addconfig extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        
       if(!$this->isPostBack)
        {	
	
			$this->accesspoint->DataSource = $this->Accesspoint;
			$this->accesspoint->dataBind();
	
			$this->display->DataSource = $this->AllDevice;
			$this->display->dataBind();

                        $this->lightinfo->DataSource = $this->AllDevice;
                        $this->lightinfo->dataBind();

      }
    }

	protected function getAccesspoint()
	{
        $cmd = $this->db->createCommand( "SELECT id AS Value, name AS Text FROM hr_device WHERE accessPoint=1" );
        $data =  $cmd->query();
        $data = $data->readAll();
        return $data;
	}

	protected function getAllDevice()
	{
        $cmd = $this->db->createCommand( "SELECT id AS Value, name AS Text FROM hr_device" );
        $data =  $cmd->query();
        $data = $data->readAll();
        $d[0]['Value'] = '0';
        $d[0]['Text'] = Prado::localize('---- None ----');
        $data = array_merge($d, $data);
        return $data;
	}

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($lastId = $this->saveData())
          {
            $id = $lastId;
            $pBack = array('okMsg'=>Prado::localize('The parking was added successfully'), 'id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('components.velopark.modconfig', $pBack));
          }
          else
          {
            $pBack = array('koMsg'=>Prado::localize('The parking was not added'));
          }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The parking was added successfully'));
          }
          else
            $pBack = array('koMsg'=>Prado::localize('The parking was not added'));
          $this->Response->redirect($this->Service->constructUrl('components.velopark.config',$pBack));
        }
    }

	public function onCancel($sender, $param)
	{
        $this->Response->redirect($this->Service->constructUrl('components.velopark.config'));	
	}


    protected function saveData()
    {
	$cmd = $this->db->createCommand( "INSERT INTO `hr_vp_parking` (`area` ,`display_id` ,`accesspoint_id`,`lightinfo_id`,`lightinfo_io`,`default_msg`,`access_ok_msg`,`access_ko_msg`,`displayTime` ) VALUES (:area, :display_id, :accesspoint_id,:lightinfo_id,:lightinfo_io,:default_msg,:access_ok_msg,:access_ko_msg, :displayTime)" );

      	$cmd->bindParameter(":area",$this->area->SafeText,PDO::PARAM_STR);
      	$cmd->bindParameter(":display_id",$this->display->getSelectedValue(), PDO::PARAM_STR);
	$cmd->bindParameter(":accesspoint_id",$this->accesspoint->getSelectedValue(), PDO::PARAM_STR);
        $cmd->bindParameter(":lightinfo_id",$this->lightinfo->getSelectedValue(), PDO::PARAM_STR);
        $cmd->bindParameter(":lightinfo_io",$this->lightinfo_io->getSelectedValue(), PDO::PARAM_STR);
        $cmd->bindParameter(":default_msg",$this->default_msg->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":access_ok_msg",$this->access_ok_msg->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":access_ko_msg",$this->access_ko_msg->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":displayTime",$this->displayTime->SafeText,PDO::PARAM_STR);

	$cmd->execute();

      	$lastId = $this->db->LastInsertID;

	return $lastId;

    }
} 
