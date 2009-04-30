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


class easymailing extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->IsPostBack)
        {          
            $asset = $this->Application->getAssetManager();
            $asset->publishFilePath('./protected/pages/components/easymailing/assets/icon-48-easymailing.png');


            $cmd = $this->db->createCommand( "SELECT id AS Value, name AS Text FROM hr_user_group");
            $query = $cmd->query();
            $data = $query->readAll();
            
            $this->groups->DataSource=$data;
            $this->groups->dataBind();
            
            $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT(name,' ',firstname) AS Text FROM hr_user WHERE name!='??'");
            $query = $cmd->query();
            $data = $query->readAll();
            
            $this->users->DataSource=$data;
            $this->users->dataBind();
            
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
     
     
    public function onSendMail($sender, $param)
    {
      $mailer = new TMailer();
      
      $mailer->setObject($this->object->SafeText);
      $mailer->setBody($this->Body->Text);
      
      $recipient = array();
      
     
      if($this->send_groups->getChecked())
      {
        $indices=$this->groups->SelectedIndices;
        $result='';
        foreach($indices as $index)
        {
            $item=$this->groups->Items[$index];

            $cmd = $this->db->createCommand( "SELECT u.id, email1, email2  FROM hr_user AS u LEFT JOIN hr_user_group_attribution AS uga ON uga.id_user=u.id  WHERE uga.id_group=".$item->Value);
            $query = $cmd->query();
            $data = $query->readAll();

            foreach($data as $d)
            {
              if($d['email1'] != '')
                $recipient[$d['id']] = $d['email1'];
              elseif($d['email2'] != '')
                $recipient[$d['id']] = $d['email2'];
            
            }
        }
      }
      
      if($this->send_users->getChecked())
      {
        $indices=$this->users->SelectedIndices;
        $result='';
        foreach($indices as $index)
        {
            $item=$this->users->Items[$index];
            
            $cmd = $this->db->createCommand( "SELECT email1, email2  FROM hr_user WHERE id=".$item->Value);
            $query = $cmd->query();
            $data = $query->read();
            
            if($data['email1'] != '')
              $recipient[$item->Value] = $data['email1'];
            elseif($data['email2'] != '')
              $recipient[$item->Value] = $data['email2'];
        }
      }
      
     
      foreach($recipient as $r)
      {
        $mailer->addRecipient($r);
      }
      
      
      if($this->attachment->HasFile)
      {
         $this->attachment->saveAs('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$this->attachment->FileName);
         $mailer->addAttachment('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$this->attachment->FileName, $this->attachment->FileName);
      }
      
      $res = $mailer->sendHtmlMail($this->mailing->getChecked());
      
      if($this->attachment->HasFile)
      {
        unlink('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$this->attachment->FileName);
      }
      
      
      if($res)
      {
        $pBack = array('okMsg'=>Prado::localize('The mail was delivered successfully'));
        $this->Response->redirect($this->Service->constructUrl('components.easymailing.easymailing',$pBack));
      }
      else
      {
        $pBack = array('koMsg'=>Prado::localize('The mail was not delivered successfully'));
        $this->Response->redirect($this->Service->constructUrl('components.easymailing.easymailing',$pBack));
      }
    }
}

?>
