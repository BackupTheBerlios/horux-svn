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

class modMessageInfo extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {

          if(isset($this->Request['id']))
          {

                $userId=$this->Application->getUser()->getUserId();
                $this->blockRecord('hr_horux_media_message', $this->Request['id'], $userId);   

                $this->id->Value = $this->Request['id'];
                $this->setData();
          }
        }
    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_horux_media_message WHERE id=:id" );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
          $data = $query->read();
          $this->id->Value = $data['id'];
          $this->name->Text = $data['name'];
          $this->message->Text = $data['message'];

          $startDisplay = explode(" ", $data['startDisplay']);
          $date = explode("-", $startDisplay[0]);
          $time = explode(":", $startDisplay[1]);
          $this->startDisplay->Text = $date[2]."-".$date[1]."-".$date[0];
          $this->startDisplayHour->Text = $time[0];
          $this->startDisplayMinute->Text = $time[1];

          $stopDisplay = explode(" ", $data['stopDisplay']);
          $date = explode("-", $stopDisplay[0]);
          $time = explode(":", $stopDisplay[1]);
          $this->stopDisplay->Text = $date[2]."-".$date[1]."-".$date[0];
          $this->stopDisplayHour->Text = $time[0];
          $this->stopDisplayMinute->Text = $time[1];

        } 
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The info message was modified successfully'), 'id'=>$this->id->Value);
            $this->Response->redirect($this->Service->constructUrl('components.media.modMessageInfo', $pBack));
          }
          else
          {
            $pBack = array('koMsg'=>Prado::localize('The info message was not modified'), 'id'=>$this->id->Value);
            $this->Response->redirect($this->Service->constructUrl('components.media.modMessageInfo', $pBack));
          }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The info message was modified successfully'));
          }
          else
            $pBack = array('koMsg'=>Prado::localize('The info message was not modified'));
            
          $this->blockRecord('hr_keys', $this->id->Value, 0);
          $this->Response->redirect($this->Service->constructUrl('components.media.messageInfoList',$pBack));
        }
    }

        public function onCancel($sender, $param)
        {
                $this->blockRecord('hr_horux_media_message', $this->id->Value, 0);     
        $this->Response->redirect($this->Service->constructUrl('components.media.messageInfoList')); 
        }


    protected function saveData()
    {
      $res1 = true;     
    
      $cmd = $this->db->createCommand( "UPDATE hr_horux_media_message SET name=:name, message=:message, startDisplay=:startDisplay, stopDisplay=:stopDisplay WHERE id=:id" );
        $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);
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


      $res1 = $cmd->execute();
      
      
      return $res1;
    }
}