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


class addUserMessage extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->IsPostBack)
        {          
	  $this->id->Value = $this->Request['id'];
	  $cmd = $this->db->createCommand( "SELECT * FROM hr_horux_infoDisplay_message WHERE id_user=:id" );
	  $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);
	  
	  $res = $cmd->query();
	  $row = $res->read();
	  
	  if($row)
	  {
	    $this->message->Text = $row['message'];
	    $this->mid->Value = $row['id'];
	  }
	}
    }
    
   public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($lastId = $this->saveData())
          {
            $id = $lastId;
            $pBack = array('okMsg'=>Prado::localize('The message was setted successfully'), 'id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('components.infoDisplay.addUserMessage', $pBack));
          }
          else
          {
            $pBack = array('koMsg'=>Prado::localize('The message was not setted'));
          }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The message was setted successfully'));
          }
          else
            $pBack = array('koMsg'=>Prado::localize('The message was not setted'));
          $this->Response->redirect($this->Service->constructUrl('user.UserList',$pBack));
        }
    }
    
    protected function saveData()
    {

      if($this->mid->Value == 0)
      {
	$cmd = $this->db->createCommand( "INSERT INTO hr_horux_infoDisplay_message (`id_user` ,`message` ,`type` ) VALUES (:id_user, :message, 'USER')" );

      	$cmd->bindParameter(":id_user",$this->id->Value,PDO::PARAM_STR);
      	$cmd->bindParameter(":message",$this->message->SafeText, PDO::PARAM_STR);

	$cmd->execute();

      	$lastId = $this->db->LastInsertID;

	return $this->id->Value;
      }
      else
      {
	$cmd = $this->db->createCommand( "UPDATE hr_horux_infoDisplay_message SET message=:message WHERE id=:mid" );

      	$cmd->bindParameter(":mid",$this->mid->Value,PDO::PARAM_STR);
      	$cmd->bindParameter(":message",$this->message->SafeText, PDO::PARAM_STR);

	$cmd->execute();

	return $this->id->Value;

      }

    }    
    
}

?>