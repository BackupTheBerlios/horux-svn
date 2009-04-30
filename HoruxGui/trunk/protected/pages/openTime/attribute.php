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

Prado::using('horux.pages.openTime.sql');

class attribute extends Page
{
	
	public function getData()
	{
	  $id = $this->Request['id'];
      $cmd = $this->db->createCommand( SQL::SQL_GET_ATTRIBUTION );
      $cmd->bindParameter(":id",$id,PDO::PARAM_INT);		
      $data=$cmd->query();
      $connection->Active=false;

      return $data;      
	}
	
	public function getOpenTime()
	{
	  $id = $this->Request['id'];
      $cmd = $this->db->createCommand( SQL::SQL_GET_ALL_OPEN_TIME2 );
      $cmd->bindParameter(":id",$id,PDO::PARAM_INT);		
      $data=$cmd->query();
      $connection->Active=false;
 
      return $data;      		
	}
	
	public function onLoad($param)
    {
        parent::onLoad($param); 
        
        if(!$this->IsPostBack)
        {
            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();
            
            $this->id->Value = $this->Request['id'];
        }

        $this->OpenTime->DataSource=$this->getOpenTime();
        $this->OpenTime->dataBind();   



        if(isset($this->Request['okMsg']))
        {
          $this->displayMessage($this->Request['okMsg'], true);
        }
        if(isset($this->Request['koMsg']))
        {
          $this->displayMessage($this->Request['koMsg'], false);
        }
    }
    
    public function attributeOpenTime($sender,$param)
    {
		$id_device = $this->id->Value;
    	$id_openTime = $this->OpenTime->getSelectedValue();

		if($id_openTime)
		{
		
			$cmd=$this->db->createCommand(SQL::SQL_ATTRIBUTE_OPEN_TIME);
			$cmd->bindParameter(":id_device",$id_device);
			$cmd->bindParameter(":id_openTime",$id_openTime);	
			$cmd->execute();
          	
			$this->Response->redirect($this->Service->constructUrl('openTime.attribute',array('id'=>$id_device))); 
		}
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
    
    public function onUnattribute($sender, $param)
    {
        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nUnAttributed = 0;
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
				
			$cmd=$this->db->createCommand(SQL::SQL_DELETE_OPEN_TIME_ATTRIBUTION);
	                $cmd->bindParameter(":id",$cb->Value);
          	                
	                if($cmd->execute())
	                  $nUnAttributed++;
	            }
	        }
        }
        
        if($koMsg !== '')
        {
          $pBack = array('id'=>$this->id->Value, 'koMsg'=>$koMsg);
          $this->Response->redirect($this->Service->constructUrl('openTime.attribute',$pBack));    	
          
        }
        else
        {
          $pBack = array('id'=>$this->Request['id'],'okMsg'=>Prado::localize('{n} open time was unattributed',array('n'=>$nUnAttributed)));
          $this->Response->redirect($this->Service->constructUrl('openTime.attribute',$pBack));
        }    	
    }
}

?>
