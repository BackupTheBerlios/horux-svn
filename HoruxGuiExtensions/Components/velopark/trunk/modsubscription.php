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

class modsubscription extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        
       if(!$this->isPostBack)
        {	
		  if(isset($this->Request['id']))
		  {

          	$userId=$this->Application->getUser()->getUserId();
    	  	$this->blockRecord('hr_vp_subscription', $this->Request['id'], $userId);	

          	$this->id->Value = $this->Request['id'];

			$this->setData();

			$sql = "SELECT devise FROM hr_site";
			$cmd = $this->db->createCommand( $sql );
			$res = $cmd->query();
      		$res = $res->read();
			$this->devise->Text = $res["devise"];
		  }
        }
    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_vp_subscription WHERE id=:id" );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
          $data = $query->read();
          $this->id->Value = $data['id'];
          $this->name->Text = $data['name'];
          $this->description->Text = $data['description'];
          $this->multiple->Text = $data['credit'];
          $this->price->Text = $data['price'];

          if($data['start'] == 'immediatly')
            $this->StartImmediatly->setChecked(true);
          else
            $this->StartFirstAccess->setChecked(true);


		  $validity = explode(":", $data['validity']);	
		  $this->year->setSelectedValue($validity[0]);	  
		  $this->month->setSelectedValue($validity[1]);	  
		  $this->day->setSelectedValue($validity[2]);	  
		  $this->hour->setSelectedValue($validity[3]);	  

        } 
    }


    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The subscription was modified successfully'), 'id'=>$this->id->Value);
            $this->Response->redirect($this->Service->constructUrl('components.velopark.modsubscription', $pBack));
          }
          else
          {
            $pBack = array('koMsg'=>Prado::localize('The subscription was not modified'), 'id'=>$this->id->Value);
            $this->Response->redirect($this->Service->constructUrl('components.velopark.modsubscription', $pBack));
          }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The subscription was modified successfully'));
          }
          else
            $pBack = array('koMsg'=>Prado::localize('The subscription was not modified'));
            
          $this->blockRecord('hr_vp_subscription', $this->id->Value, 0);
          $this->Response->redirect($this->Service->constructUrl('components.velopark.subscription',$pBack));
        }
    }

	public function onCancel($sender, $param)
	{
		$this->blockRecord('`hr_vp_subscription`', $this->id->Value, 0);	
        $this->Response->redirect($this->Service->constructUrl('components.velopark.subscription'));	
	}


    protected function saveData()
    {
		$cmd = $this->db->createCommand( "UPDATE hr_vp_subscription SET `name` = :name,`description` = :description,`validity` = :validity, `credit` = :credit, `price`=:price, `start`=:start  WHERE id =:id" );

      	$cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
      	$cmd->bindParameter(":description",$this->description->SafeText, PDO::PARAM_STR);

		$validity = $this->year->getSelectedValue().":".$this->month->getSelectedValue().":".$this->day->getSelectedValue().":".$this->hour->getSelectedValue();

		$cmd->bindParameter(":validity",$validity, PDO::PARAM_STR);
		$cmd->bindParameter(":credit",$this->multiple->SafeText, PDO::PARAM_STR);
		$cmd->bindParameter(":price",$this->price->SafeText, PDO::PARAM_STR);
      	$cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_STR);


        $checked = $this->StartFirstAccess->getChecked() ? 'firstaccess' : 'immediatly';

        $cmd->bindParameter(":start",$checked, PDO::PARAM_STR);

		return $cmd->execute();
    }
} 
