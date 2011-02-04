<?php


Prado::using('horux.pages.user.sql');

class Groups extends Page
{
	public function getData()
	{
	  $id = $this->id->Value;
      $cmd = $this->db->createCommand( SQL::SQL_GET_GROUPS );
      $cmd->bindValue(":id",$id,PDO::PARAM_INT);
      $data=$cmd->query();
      $connection->Active=false;

      return $data;      
	}
	
	public function getGroups()
	{
	  $id = $this->id->Value;
      $cmd = $this->db->createCommand( SQL::SQL_GET_UNATTRIBUTED_GROUP );
      $cmd->bindValue(":id",$id,PDO::PARAM_INT);
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

            $this->UnusedGroup->DataSource=$this->Groups;
            $this->UnusedGroup->dataBind();
            if($this->UnusedGroup->getItemCount())
                $this->UnusedGroup->setSelectedIndex(0);

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
    
    public function onAttribute($sender,$param)
    {
    	$id_user = $this->id->Value;
    	$id_group = $this->UnusedGroup->getSelectedValue();

	    $cmd=$this->db->createCommand(SQL::SQL_ATTRIBUTE_GROUP);
    	$cmd->bindValue(":id_user",$id_user);
    	$cmd->bindValue(":id_group",$id_group);
    	$cmd->execute();

        $this->addStandalone('add', $id_group.','.$id_user);


        $cmd=$this->db->createCommand(SQL::SQL_GET_PERSON);
        $cmd->bindValue(":id",$id_user);
        $cmd = $cmd->query();
        $data = $cmd->read();

        $cmd=$this->db->createCommand(SQL::SQL_GET_GROUPS3);
        $cmd->bindValue(":id",$id_group);
        $cmd = $cmd->query();
        $data2 = $cmd->read();

        $this->log("Attribute the group ".$data2['name']." to ".$data['name']." ".$data['firstname']);

		$this->Response->redirect($this->Service->constructUrl('user.groups',array('id'=>$id_user)));    	
    }    

    protected function addStandalone($function, $idgroup)
    {
        $sa = new TStandAlone();
        $sa->addStandalone($function, $idgroup, 'UserAttributionGroup');
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
	                $cmd->bindValue(":id_group",$cb->Value);
	                $cmd->bindValue(":id_user",$this->id->Value);

                    $this->addStandalone('sub', $cb->Value.','.$this->id->Value);

                    if($cmd->execute())
                    {
	                    $nUnAttributed++;

                        $cmd=$this->db->createCommand(SQL::SQL_GET_PERSON);
                        $cmd->bindValue(":id",$this->id->Value);
                        $cmd = $cmd->query();
                        $data = $cmd->read();

                        $cmd=$this->db->createCommand(SQL::SQL_GET_GROUPS3);
                        $cmd->bindValue(":id",$cb->Value);
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
    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('user.UserList'));
    }
}

?>
