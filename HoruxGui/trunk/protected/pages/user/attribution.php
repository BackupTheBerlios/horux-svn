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

class Attribution extends Page
{
	
	public function getData()
	{
	  $id = $this->Request['id'];
      $cmd = $this->db->createCommand( SQL::SQL_GET_KEY );
      $cmd->bindParameter(":id",$id,PDO::PARAM_INT);		
      $data=$cmd->query();
      $connection->Active=false;

      return $data;      
	}
	
	public function getKey()
	{
      $cmd = $this->db->createCommand( SQL::SQL_GET_UNATTRIBUTED_KEY );
      $data=$cmd->query();
      $connection->Active=false;
 
      return $data;      		
	}
	
	public function onLoad($param)
    {
        parent::onLoad($param); 

		if(isset($this->Request['sn']))
		{
                  $cmd = $this->db->createCommand( "SELECT * FROM hr_keys WHERE serialNumber=:sn AND isUsed=0" );
                  $cmd->bindParameter(":sn",$this->Request['sn'],PDO::PARAM_STR);
                  $data=$cmd->query();
                  $data = $data->read();
                  if($data)
                  {
                          $cmd=$this->db->createCommand(SQL::SQL_ATTRIBUTE_KEY);
                          $cmd->bindParameter(":id_user", $this->Request['id']);
                          $cmd->bindParameter(":id_key",$data['id']);	
                          $cmd->execute();
                          
                          $cmd=$this->db->createCommand(SQL::SQL_SET_USED_KEY);
                          $cmd->bindParameter(":id",$data['id']);
                          $flag = 1;
                          $cmd->bindParameter(":flag",$flag);
                          $cmd->execute();
                          
                          $this->addStandalone('add', $data['id']);
                          
                          $this->Response->redirect($this->Service->constructUrl('user.attribution',array('id'=>$this->Request['id'])));

                  }
                  else
                  {
                    $cmd = $this->db->createCommand( "SELECT * FROM hr_keys WHERE serialNumber=:sn AND isUsed=1" );
                    $cmd->bindParameter(":sn",$this->Request['sn'],PDO::PARAM_STR);
                    $data=$cmd->query();
                    $data = $data->read();
                    if($data)
                    {
                      $this->displayMessage(Prado::localize("The key is already attributed"), false);
                    }
                    else
                    {
                         $cmd = NULL;
                        //! add the new key in the database
                        if($this->db->DriverName == 'sqlite')
                            $cmd=$this->db->createCommand(SQL::SQL_ADD_KEY_SQLITE);
                        else
                            $cmd=$this->db->createCommand(SQL::SQL_ADD_KEY);
                        $cmd->bindParameter(":serialNumber",$this->Request['sn']);   
                        $cmd->execute();
                        //! attribute the new key

                        $lastId = $this->db->LastInsertID;

                        $cmd=$this->db->createCommand(SQL::SQL_ATTRIBUTE_KEY);
                        $cmd->bindParameter(":id_user", $this->Request['id']);
                        $cmd->bindParameter(":id_key",$lastId);   
                        $cmd->execute();

                        $this->addStandalone('add', $lastId);
                          
                        $this->Response->redirect($this->Service->constructUrl('user.attribution',array('id'=>$this->Request['id'])));

                    }

                  }
		}

        
        $this->setAccessLink(true);
        
        if(!$this->IsPostBack)
        {
            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();
            
            $this->id->Value = $this->Request['id'];
        }

        $this->UnusedKey->DataSource=$this->Key;
        $this->UnusedKey->dataBind();   
        if($this->UnusedKey->getItemCount())
            $this->UnusedKey->setSelectedIndex(0);



        if(isset($this->Request['okMsg']))
        {
          $this->displayMessage($this->Request['okMsg'], true);
        }
        if(isset($this->Request['koMsg']))
        {
          $this->displayMessage($this->Request['koMsg'], false);
        }
    }
    
    public function onAttribute($sender,$param)
    {
    	$id_user = $this->id->Value;
    	$id_key = $this->UnusedKey->getSelectedValue();

	    $cmd=$this->db->createCommand(SQL::SQL_ATTRIBUTE_KEY);
    	$cmd->bindParameter(":id_user",$id_user);
    	$cmd->bindParameter(":id_key",$id_key);	
    	$cmd->execute();
    	
		$cmd=$this->db->createCommand(SQL::SQL_SET_USED_KEY);
        $cmd->bindParameter(":id",$id_key);
        $flag = 1;
        $cmd->bindParameter(":flag",$flag);
		$cmd->execute();
      	
      	$this->addStandalone('add', $id_key);

        $cmd=$this->db->createCommand(SQL::SQL_GET_PERSON);
        $cmd->bindParameter(":id",$id_user);
        $cmd = $cmd->query();
        $data = $cmd->read();

        $cmd=$this->db->createCommand(SQL::SQL_GET_KEY2);
        $cmd->bindParameter(":id",$id_key);
        $cmd = $cmd->query();
        $data2 = $cmd->read();

        $this->log("Attribute the key ".$data2['identificator']." to ".$data['name']." ".$data['firstname']);


		$this->Response->redirect($this->Service->constructUrl('user.attribution',array('id'=>$id_user)));    	
    }
    
    public function setBlocked($sender,$param)
    {
      $id = $sender->Text;
      $cmd=$this->db->createCommand(SQL::SQL_UPDATE_SETBLOCK_KEY);
      $cmd->bindParameter(":id",$id);
	  $func = "";
      if($sender->ImageUrl == "./themes/letux/images/menu/icon-16-checkin.png")
      {
        $flag = 1;
        $sender->ImageUrl = "./themes/letux/images/menu/icon-16-access.png";
        $cmd->bindParameter(":flag",$flag);
        $func = 'sub';

        $cmd2=$this->db->createCommand(SQL::SQL_GET_KEY2);
        $cmd2->bindParameter(":id",$id);
        $cmd2 = $cmd2->query();
        $data2 = $cmd2->read();

        $this->log("Block the key ".$data2['identificator']);

      }
      else
      {
        $flag = 0;
        $sender->ImageUrl = "./themes/letux/images/menu/icon-16-checkin.png";
        $cmd->bindParameter(":flag",$flag);
        $func = 'add';

        $cmd2=$this->db->createCommand(SQL::SQL_GET_KEY2);
        $cmd2->bindParameter(":id",$id);
        $cmd2 = $cmd2->query();
        $data2 = $cmd2->read();

        $this->log("Unblock the key ".$data2['identificator']);

     }
     $cmd->execute();
	 
	 $this->addStandalone($func, $id);
	 
     $this->DataGrid->DataSource=$this->Data;
     $this->DataGrid->dataBind();
     $this->Page->CallbackClient->update('list', $this->DataGrid);
    }
    
	protected function addStandalone($function, $idkey)
	{
		$cmd=$this->db->createCommand("SELECT * FROM hr_keys WHERE id=:id");
		$cmd->bindParameter(":id",$idkey);
		$data = $cmd->query();
		$data = $data->read();
		
		$rfid = $data['serialNumber'];
		
		if( ($data['isBlocked'] == 0 && $function=='add' ) || $function=='sub')
		{
			
			$cmd=$this->db->createCommand(SQL::SQL_GET_GROUPS);
			$cmd->bindParameter(":id",$this->id->Value);
			$data2 = $cmd->query();
			$data2 = $data2->readAll();
			
			//pour chaque groupe
			foreach($data2 as $d2)
			{
				$idgroup = $d2['id'];
				$cmd=$this->db->createCommand("SELECT * FROM hr_user_group_access WHERE id_group=:id");
				$cmd->bindParameter(":id",$idgroup);
				$data3 = $cmd->query();
				$data3 = $data3->readAll();
				
				foreach($data3 as $d3)
				{
					$idreader = $d3['id_device'];
					
					$cmd=$this->db->createCommand("INSERT INTO hr_standalone_action_service (`type`, `serialNumber`, `rd_id`) VALUES (:func,:rfid,:rdid)");
					$cmd->bindParameter(":func",$function);
					$cmd->bindParameter(":rfid",$rfid);
					$cmd->bindParameter(":rdid",$idreader);
					$cmd->execute();
				}
				
			}
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
					$cmd=$this->db->createCommand(SQL::SQL_DELETE_KEY_ATTRIBUTION);
	                $cmd->bindParameter(":id",$cb->Value);
	                $cmd->execute();

					$cmd=$this->db->createCommand(SQL::SQL_SET_USED_KEY);
	                $cmd->bindParameter(":id",$cb->Value);
	                $flag = 0;
	                $cmd->bindParameter(":flag",$flag);
            	                
            	    $this->addStandalone('sub', $cb->Value);            
            	                
	                if($cmd->execute())
                    {
                        $nUnAttributed++;
                        $cmd=$this->db->createCommand(SQL::SQL_GET_PERSON);
                        $cmd->bindParameter(":id",$this->id->Value);
                        $cmd = $cmd->query();
                        $data = $cmd->read();

                        $cmd=$this->db->createCommand(SQL::SQL_GET_KEY2);
                        $cmd->bindParameter(":id",$cb->Value);
                        $cmd = $cmd->query();
                        $data2 = $cmd->read();

                        $this->log("Attribute the key ".$data2['identificator']." to ".$data['name']." ".$data['firstname']);
                    }
	            }
	        }
        }
        
        if($koMsg !== '')
        {
          $pBack = array('id'=>$this->id->Value, 'koMsg'=>$koMsg);
          $this->Response->redirect($this->Service->constructUrl('user.attribution',$pBack));    	
          
        }
        else
        {
          $pBack = array('id'=>$this->Request['id'],'okMsg'=>Prado::localize('{n} key was unattributed',array('n'=>$nUnAttributed)));
          $this->Response->redirect($this->Service->constructUrl('user.attribution',$pBack));
        }    	
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('user.UserList'));
    }
}

?>
