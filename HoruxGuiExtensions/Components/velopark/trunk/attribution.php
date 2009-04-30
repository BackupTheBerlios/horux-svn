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


class attribution extends PageList
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->IsPostBack)
        {          
            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();		

	        $sql = "SELECT id AS Value, name AS Text FROM hr_vp_subscription";

    	    $cmd=$this->db->createCommand($sql);
        	$data = $cmd->query();
			$data = $data->readAll();

			$c[] = array("Value"=>0, "Text"=>Prado::localize("-- Select one --"));

			$data = array_merge($c,$data);

			$this->subscription->DataSource=$data;
            $this->subscription->dataBind();

			$this->userId->Value = $this->Request['id'];
				
        }

        if(isset($this->Request['okMsg']))
        {
          $this->displayMessage($this->Request['okMsg'], true);
        }
        if(isset($this->Request['koMsg']))
        {
          $this->displayMessage($this->Request['koMsg'], false);
        }
     }	

	protected function getData()
	{
        $sql = "SELECT sa.*, s.name AS type, s.description, s.credit AS totalCredit FROM hr_vp_subscription_attribution AS sa LEFT JOIN hr_vp_subscription AS s ON s.id=sa.subcription_id WHERE user_id=".$this->Request['id']." ORDER BY id DESC";

        $cmd=$this->db->createCommand($sql);
        $data = $cmd->query();
		$data = $data->readAll();

		foreach($data as $k=>$v)
		{
			$tmp = $data[$k]["status"];

			switch($tmp)
			{
				case "not_start":
					$tmp = "<span style=\"color:black\">".Prado::localize("Not started")."</span>";
					break;
				case "started":
					$tmp = "<span style=\"color:green\">".Prado::localize("Started")."</span>";
					break;
				case "finished":
					$tmp = "<span style=\"color:red\">".Prado::localize("Finished")."</span>";
					break;
			}

			$data[$k]["status_text"] = $tmp;

			$data[$k]["credit"] = ($data[$k]["totalCredit"] - $data[$k]["credit"])." / ".$data[$k]["totalCredit"];

		}

		return $data; 
	}

	public function attribute($sender, $param)
	{
		$subId = $this->subscription->getSelectedValue();

		if($subId == 0)
		{
			$koMsg = Prado::localize("Please, select one subscription in the list");
			$pBack = array('koMsg'=>$koMsg,'id'=>$this->userId->Value);
			$this->Response->redirect($this->Service->constructUrl('components.velopark.attribution',$pBack));
			return;
		}

		$sql = "SELECT * FROM hr_vp_subscription WHERE id=:id";
		$cmd=$this->db->createCommand($sql);
        $cmd->bindParameter(":id",$subId);
		$data = $cmd->query();
		$data = $data->read();	

		$sql = "INSERT INTO hr_vp_subscription_attribution (user_id, subcription_id, create_date, status, credit, start, end, create_by) VALUES (:user_id,  :subcription_id, NOW(), 'not_start', :credit, 'NULL', 'NULL', :create_by)";

		$cmd=$this->db->createCommand($sql);

        $cmd->bindParameter(":user_id",$this->userId->Value,PDO::PARAM_STR);
        $cmd->bindParameter(":subcription_id",$subId,PDO::PARAM_STR);
		$cmd->bindParameter(":credit",$data["credit"],PDO::PARAM_STR);


		$user = $this->Application->getUser();
		$createBy = $user->getName() ;
        $cmd->bindParameter(":create_by",$createBy,PDO::PARAM_STR);

		$cmd->execute();

		$pBack = array('id'=>$this->userId->Value);
		$this->Response->redirect($this->Service->constructUrl('components.velopark.attribution',$pBack));
	}

    public function checkboxAllCallback($sender, $param)
    {
        $cbs = $this->findControlsByType("TActiveCheckBox");
        $isChecked = $sender->getChecked();

        foreach($cbs as $cb)
        {
           $cb->setChecked($isChecked);
        }

    }

    public function onDelete($sender,$param)
    {
        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        $koMsg = '';
		$cbChecked = 0;

        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
				$cbChecked++;
        }

        if($cbChecked==0)
        {
        	$koMsg = Prado::localize('Select one item');
        }
        else
        {
         foreach($cbs as $cb)
         {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            {
                $cmd=$this->db->createCommand("DELETE FROM hr_vp_subscription_attribution WHERE id=:id");
                $cmd->bindParameter(":id",$cb->Value);
                $cmd->execute();
				$nDelete++;
                
            }
         }
        }
        
        if($koMsg !== '')
          $pBack = array('koMsg'=>$koMsg, 'id'=>$this->userId->Value);
        else
          $pBack = array('okMsg'=>Prado::localize('{n} subscription attributed was deleted',array('n'=>$nDelete)), 'id'=>$this->userId->Value);
        $this->Response->redirect($this->Service->constructUrl('components.velopark.attribution',$pBack));
    }

	public function printTicket($sender, $param)
	{
		$param = $this->Application->getParameters();

		if(file_exists("./protected/pages/components/velopark/printtemplate/".$param["printtemplate"].".php"))
		{
			include("./protected/pages/components/velopark/printtemplate/".$param["printtemplate"].".php");
			exit;
		}
		else
		{
			$koMsg = Prado::localize("The template {tmpname} does not exist", array("tmpname"=>$param["printtemplate"]));			  
			$pBack = array('koMsg'=>$koMsg, 'id'=>$this->userId->Value);
			$this->Response->redirect($this->Service->constructUrl('components.velopark.attribution',$pBack));
		}
	}
}

?>