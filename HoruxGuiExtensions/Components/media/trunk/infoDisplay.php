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


class infoDisplay extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->IsPostBack)
        {
          $cmd = $this->db->createCommand( "SELECT * FROM hr_horux_infoDisplay_message WHERE type='ALL'" );
          $data =  $cmd->query();
          if($data)
          {
            $data = $data->read();
            $this->messageForAllUser->Text = $data['message'];
          }
          
          $cmd = $this->db->createCommand( "SELECT * FROM hr_horux_infoDisplay_message WHERE type='UNKNOWN'" );
          $data =  $cmd->query();
          if($data)
          {
            $data = $data->read();
            $this->messageForUnknownUser->Text = $data['message'];
          }
          
        }
    }
    
    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The messages was modified successfully'));
          }
          else
            $pBack = array('koMsg'=>Prado::localize('The messages was not modified'));
            
          $this->Response->redirect($this->Service->constructUrl('components.infoDisplay.infoDisplay',$pBack));
        }
    }
    
    protected function saveData()
    {
      $res1 = $res2 = $res3 = true;     
    
      $cmd = $this->db->createCommand( "UPDATE hr_horux_infoDisplay_message SET message=:message WHERE type='ALL'" );
      $cmd->bindParameter(":message",$this->messageForAllUser->SafeText,PDO::PARAM_STR);
      $cmd->execute();
      
      $cmd = $this->db->createCommand( "UPDATE hr_horux_infoDisplay_message SET message=:message WHERE type='UNKNOWN'" );
      $cmd->bindParameter(":message",$this->messageForUnknownUser->SafeText,PDO::PARAM_STR);
      $cmd->execute();
      
      return true;
    }    
}


?>
