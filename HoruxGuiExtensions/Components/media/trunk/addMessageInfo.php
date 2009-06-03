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

class addMessageInfo extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        
       if(!$this->isPostBack)
        {       
        }
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($lastId = $this->saveData())
          {
            $id = $lastId;
            $pBack = array('okMsg'=>Prado::localize('The info message was added successfully'), 'id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('components.media.modMessageInfo', $pBack));
          }
          else
          {
            $pBack = array('koMsg'=>Prado::localize('The info message was not added'));
          }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The info message was added successfully'));
          }
          else
            $pBack = array('koMsg'=>Prado::localize('The info message was not added'));
          $this->Response->redirect($this->Service->constructUrl('components.media.messageInfoList',$pBack));
        }
    }

        public function onCancel($sender, $param)
        {
        $this->Response->redirect($this->Service->constructUrl('components.media.messageInfoList'));  
        }


    protected function saveData()
    {
        $cmd = $this->db->createCommand( "INSERT INTO `hr_horux_media_message` (`name` ,`message` ,`type`,`startDisplay`,`stopDisplay`  ) VALUES (:name, :message, 'INFO' ,:startDisplay,:stopDisplay)" );

        $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":message",$this->message->SafeText, PDO::PARAM_STR);

        $startDisplay = explode("-",$this->startDisplay->Text);
        $startDisplay = $startDisplay[2]."-".$startDisplay[1]."-".$startDisplay[0];
        $startDisplay .= " ";
        $startDisplay .= $this->startDisplayHour->SafeText.":".$this->startDisplayMinute->SafeText.":00";

        $stopDisplay = explode("-",$this->stopDisplay->Text);
        $stopDisplay = $stopDisplay[2]."-".$stopDisplay[1]."-".$stopDisplay[0];
        $stopDisplay .= " ";
        $stopDisplay .= $this->stopDisplayHour->SafeText.":".$this->stopDisplayMinute->SafeText.":00";


        $cmd->bindParameter(":startDisplay",$startDisplay, PDO::PARAM_STR);
        $cmd->bindParameter(":stopDisplay",$stopDisplay, PDO::PARAM_STR);

        $cmd->execute();

        $lastId = $this->db->LastInsertID;

        return $lastId;

    }
}
