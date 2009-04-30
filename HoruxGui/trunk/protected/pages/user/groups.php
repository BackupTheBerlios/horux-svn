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

Prado::using('horux.pages.user.sql');

class Groups extends Page
{
	public function getData()
	{
	  $id = $this->id->Value;
      $cmd = $this->db->createCommand( SQL::SQL_GET_GROUPS );
      $cmd->bindParameter(":id",$id,PDO::PARAM_INT);		
      $data=$cmd->query();
      $connection->Active=false;

      return $data;      
	}
	
	public function getGroups()
	{
	  $id = $this->id->Value;
      $cmd = $this->db->createCommand( SQL::SQL_GET_UNATTRIBUTED_GROUP );
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
        	$this->id->Value = $this->Request['id'];	
        
            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();
             
        }

        $this->UnusedGroup->DataSource=$this->Groups;
        $this->UnusedGroup->dataBind();
        if($this->UnusedGroup->getItemCount())
            $this->UnusedGroup->setSelectedIndex(0);

        if(isset($this->Request['okMsg']))
        {
          $this->displayMessage($this->Request['okMsg'], true);
        }
        if(isset($this->Request['koMsg']))
        {
          $this->displayMessage($this->Request['koMsg'], false);
        }
    }	
    
    public function attribute($sender,$param)
    {
    	$id_user = $this->id->Value;
    	$id_group = $this->UnusedGroup->getSelectedValue();

	    $cmd=$this->db->createCommand(SQL::SQL_ATTRIBUTE_GROUP);
    	$cmd->bindParameter(":id_user",$id_user);
    	$cmd->bindParameter(":id_group",$id_group);	
    	$cmd->execute();

        $cmd=$this->db->createCommand(SQL::SQL_GET_PERSON);
        $cmd->bindParameter(":id",$id_user);
        $cmd = $cmd->query();
        $data = $cmd->read();

        $cmd=$this->db->createCommand(SQL::SQL_GET_GROUPS3);
        $cmd->bindParameter(":id",$id_group);
        $cmd = $cmd->query();
        $data2 = $cmd->read();

        $this->log("Attribute the group ".$data2['name']." to ".$data['name']." ".$data['firstname']);

		$this->Response->redirect($this->Service->constructUrl('user.groups',array('id'=>$id_user)));    	
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
					$cmd=$this->db->createCommand(SQL::SQL_DELETE_GROUP_ATTRIBUTION);
	                $cmd->bindParameter(":id_group",$cb->Value);
	                $cmd->bindParameter(":id_user",$this->id->Value);

                    if($cmd->execute())
                    {
	                    $nUnAttributed++;

                        $cmd=$this->db->createCommand(SQL::SQL_GET_PERSON);
                        $cmd->bindParameter(":id",$this->id->Value);
                        $cmd = $cmd->query();
                        $data = $cmd->read();

                        $cmd=$this->db->createCommand(SQL::SQL_GET_GROUPS3);
                        $cmd->bindParameter(":id",$cb->Value);
                        $cmd = $cmd->query();
                        $data2 = $cmd->read();

                        $this->log("Unattribute the group ".$data2['name']." to ".$data['name']." ".$data['firstname']);
                    }
	            }
	        }
        }
        
        if($koMsg !== '')
        {
          $pBack = array('id'=>$this->Request['id'], 'koMsg'=>$koMsg);
          $this->Response->redirect($this->Service->constructUrl('user.groups',$pBack));    	
          
        }
        else
        {
          $pBack = array('id'=>$this->Request['id'],'okMsg'=>Prado::localize('{n} group was unattributed',array('n'=>$nUnAttributed)));
          $this->Response->redirect($this->Service->constructUrl('user.groups',$pBack));
        }    	
    }
}

?>