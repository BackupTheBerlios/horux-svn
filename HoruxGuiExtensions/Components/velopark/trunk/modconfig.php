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

class modconfig extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        
       if(!$this->isPostBack)
        {
		  if(isset($this->Request['id']))
		  {

          	$userId=$this->Application->getUser()->getUserId();
    	  	$this->blockRecord('hr_vp_parking', $this->Request['id'], $userId);	

          	$this->id->Value = $this->Request['id'];


			$this->accesspoint->DataSource = $this->Accesspoint;
			$this->accesspoint->dataBind();

			$this->display->DataSource = $this->AllDevice;
			$this->display->dataBind();

                        $this->lightinfo->DataSource = $this->AllDevice;
                        $this->lightinfo->dataBind();

			$this->setData();
		  }
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

    protected function setData()
    {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_vp_parking WHERE id=:id" );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
          $data = $query->read();
          $this->id->Value = $data['id'];
          $this->area->Text = $data['area'];
          $this->filling->Text = $data['filling'];
          $this->accesspoint->setSelectedValue($data['accesspoint_id']);
          $this->display->setSelectedValue($data['display_id']);
          $this->lightinfo->setSelectedValue($data['lightinfo_id']);
          $this->lightinfo_io->setSelectedValue($data['lightinfo_io']);
          $this->default_msg->Text = $data['default_msg'];
          $this->access_ok_msg->Text = $data['access_ok_msg'];
          $this->access_ko_msg->Text = $data['access_ko_msg'];
          $this->displayTime->Text = $data['displayTime'];
        } 
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The parking was modified successfully'), 'id'=>$this->id->Value);
            $this->Response->redirect($this->Service->constructUrl('components.velopark.modconfig', $pBack));
          }
          else
          {
            $pBack = array('koMsg'=>Prado::localize('The parking was not modified'), 'id'=>$this->id->Value);
            $this->Response->redirect($this->Service->constructUrl('components.velopark.modconfig', $pBack));
          }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The parking was modified successfully'));
          }
          else
            $pBack = array('koMsg'=>Prado::localize('The parking was not modified'));
            
          $this->blockRecord('hr_vp_parking', $this->id->Value, 0);
          $this->Response->redirect($this->Service->constructUrl('components.velopark.config',$pBack));
        }
    }

	public function onCancel($sender, $param)
	{
		$this->blockRecord('hr_vp_parking', $this->id->Value, 0);	
        $this->Response->redirect($this->Service->constructUrl('components.velopark.config'));	
	}


    protected function saveData()
    {
	$cmd = $this->db->createCommand( "UPDATE hr_vp_parking SET `area` = :area,`display_id` = :display_id,`accesspoint_id` = :accesspoint_id, `filling` = :filling, `lightinfo_id` =:lightinfo_id, `lightinfo_io`=:lightinfo_io, `default_msg`=:default_msg, `access_ok_msg`=:access_ok_msg, `access_ko_msg`=:access_ko_msg, `displayTime`=:displayTime  WHERE id =:id" );

      	$cmd->bindParameter(":area",$this->area->SafeText,PDO::PARAM_STR);
      	$cmd->bindParameter(":filling",$this->filling->SafeText,PDO::PARAM_STR);
      	$cmd->bindParameter(":display_id",$this->display->getSelectedValue(), PDO::PARAM_STR);
        $cmd->bindParameter(":lightinfo_id",$this->lightinfo->getSelectedValue(), PDO::PARAM_STR);
        $cmd->bindParameter(":lightinfo_io",$this->lightinfo_io->getSelectedValue(), PDO::PARAM_STR);
      	$cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_STR);
	$cmd->bindParameter(":accesspoint_id",$this->accesspoint->getSelectedValue(), PDO::PARAM_STR);
        $cmd->bindParameter(":default_msg",$this->default_msg->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":access_ok_msg",$this->access_ok_msg->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":access_ko_msg",$this->access_ko_msg->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":displayTime",$this->displayTime->SafeText,PDO::PARAM_STR);

	return $cmd->execute();
    }
} 
