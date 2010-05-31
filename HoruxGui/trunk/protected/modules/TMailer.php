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

require_once('swift/lib/Swift.php');
require_once ('swift/lib/Swift/Connection/SMTP.php');
require_once ('swift/lib/Swift/Connection/Sendmail.php');
require_once ('swift/lib/Swift/Connection/NativeMail.php');


class TMailer extends TModule
{
  private $db = NULL;

  private $mailer = '';
  private $mail_from = '';
  private $from_name = '';
  private $sendmail_path = '';
  private $smtp_auth = 0;
  private $smtp_safe = 'none';
  private $smtp_username= '';
  private $smtp_password = '';
  private $smtp_host= 'localhost';
  private $smtp_port= 25;

  private $swift = NULL;
  private $body = "";
  private $object = "";
  private $attachment = array();
  private $addRecipient = array();

  public function init($config) 
  {
    parent::init($config);
  }
  
  private function openConnection()
  {
    $this->db = $this->Application->getModule('horuxDb')->DbConnection;
    //$this->db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
    $this->db->Active=true;
    
    $sql = "SELECT * FROM hr_config WHERE `id`=1";
    
    $cmd=$this->db->createCommand($sql);
    $data = $cmd->query();
    $data = $data->read();
    
    $this->mailer = $data['mail_mailer'];
    $this->mail_from = $data['mail_mail_from'];
    $this->from_name = $data['mail_from_name'];
    $this->sendmail_path = $data['mail_sendmail_path'];
    $this->smtp_auth = $data['mail_smtp_auth'];
    $this->smtp_safe = $data['mail_smtp_safe'];
    $this->smtp_username = $data['mail_smtp_username'];
    $this->smtp_password = $data['mail_smtp_password'];
    $this->smtp_host = $data['mail_smtp_host'];
    $this->smtp_port = $data['mail_smtp_port'];  
  

    try
    {
      switch($this->mailer)
      {
        case 'mail':
            $this->swift = new Swift(new Swift_Connection_NativeMail());
            break;
        case 'sendmail':
            $this->swift = new Swift(new Swift_Connection_Sendmail($this->sendmail_path));
            break;
        case 'smtp':
            //! no authentication
            $smtp = NULL;
            
            switch($this->smtp_safe)
            {
              case 'none':
                $smtp = new Swift_Connection_SMTP($this->smtp_host, $this->smtp_port);
                break;
              case 'ssl':
                $smtp = new Swift_Connection_SMTP($this->smtp_host, $this->smtp_port, Swift_Connection_SMTP::ENC_SSL);
                break;
              case 'tls':
                if($this->smtp_port>0)
                  $smtp = new Swift_Connection_SMTP($this->smtp_host, $this->smtp_port, Swift_Connection_SMTP::ENC_TLS);
                else
                  $smtp = new Swift_Connection_SMTP($this->smtp_host, Swift_Connection_SMTP::PORT_SECURE, Swift_Connection_SMTP::ENC_TLS);
                break;
            }
            
            if($this->smtp_auth)
            {
              $smtp->setUsername($this->smtp_username);
              $smtp->setpassword($this->smtp_password);
            }

            $this->swift = new Swift($smtp);
            break;
      }
      
      return true;
    }
    catch (Swift_ConnectionException $e) {
        echo "There was a problem communicating with : " . $e->getMessage(); exit;
        return false;
    } catch (Swift_Message_MimeException $e) {
        echo "There was an unexpected problem building the email:" . $e->getMessage();exit;
        return false;
    }

  }

  public function sendConfigChange()
  {
    if(!$this->openConnection()) return false;

    try
    {
      $message = new Swift_Message(Prado::localize("Horux Configuration Changed"), Prado::localize("The Horux configuration was changed succeffuly!"));
      $res = $this->swift->send($message, $this->mail_from, new Swift_Address($this->mail_from, $this->from_name));
  
      $this->swift->disconnect();
      return $res;
    }
    catch(Swift_Message_MimeException  $e)
    {
      return false;
    }
  }

  public function sendSuperUser($email, $username, $password)
  {
    if(!$this->openConnection()) return false;

    try
    {
      $text = Prado::localize("Hello {username}<br/><br/>A new super user has registered for the access control.<br/>This e-mail contains their details:<br/><br/>Username: {un}<br/>Password: {password}<br/><br/>Please do not respond to this message. It is automatically generated and is for information purposes only.", array('username'=>$username, 'un'=>$username, 'password'=>$password));

      $message = new Swift_Message(Prado::localize("Horux super user password"));
      $message->attach(new Swift_Message_Part($text, "text/html"));
      $res = $this->swift->send($message, $email, new Swift_Address($this->mail_from, $this->from_name));

      $this->swift->disconnect();
      return $res;
    }
    catch(Swift_Message_MimeException  $e)
    {
      return false;
    }

  }

  public function sendUser($email, $name, $firstname, $password, $url, $sitename)
  {
    if(!$this->openConnection()) return false;

    try
    {
      $text = Prado::localize("Hello {name} {firstname}<br/><br/>A new user has registered for {sitename}.<br/>This e-mail contains their details:<br/><br/>Username: {email}<br/>Password: {password}<br/>Web site: {url}<br/><br/>Please do not respond to this message. It is automatically generated and is for information purposes only.",
                        array('name'=>$name, 'firstname'=>$firstname,'email'=>$email, 'password'=>$password, 'sitename'=>$sitename, 'url'=>$url));

      $message = new Swift_Message(Prado::localize("{sitename} user password", array('sitename'=>$sitename)));
      $message->attach(new Swift_Message_Part($text, "text/html"));
      $res = $this->swift->send($message, $email, new Swift_Address($this->mail_from, $this->from_name));

      $this->swift->disconnect();
      return $res;
    }
    catch(Swift_Message_MimeException  $e)
    {
      return false;
    }

  }

  public function sendHtmlMail($newLetter = false)
  {
    if(!$this->openConnection()) return false;
  
    try
    {
      $message = new Swift_Message($this->object);
      $message->attach(new Swift_Message_Part($this->body, "text/html"));

      $recipients = new Swift_RecipientList();
      foreach($this->addRecipient as $r)
      {
        if($r['name'] != '')
        {
          $recipients->addTo($r['mail'], $r['name']);
        }
        else
        {
          $recipients->addTo($r['mail']);
        }
      }
      
      foreach( $this->attachment as $a)
      {
        if($a['type'] != NULL)
          $message->attach(new Swift_Message_Attachment(new Swift_File($a['file']), $a['filename'], $a['type']));
        else
          $message->attach(new Swift_Message_Attachment(new Swift_File($a['file']), $a['filename']));
        
      }
      
      if($newLetter)
      {
        $res = $this->swift->batchSend($message, $recipients, new Swift_Address($this->mail_from, $this->from_name));
      }
      else
      {
        $res = $this->swift->send($message, $recipients, new Swift_Address($this->mail_from, $this->from_name));
      }
    
      $this->swift->disconnect();
    
      return $res;
    }
    catch(Swift_Message_MimeException  $e)
    {
      return false;
    }
    
  }


  public function sendTextMail($newLetter = false)
  {
    try
    {
      $this->openConnection();
    
      $message = new Swift_Message($this->object, '');
      $message->attach(new Swift_Message_Part($this->body));

      $recipients = new Swift_RecipientList();
      foreach($this->addRecipient as $r)
      {
        if($r['name'] != '')
          $recipients->addTo($r['mail'], $r['name']);
        else
          $recipients->addTo($r['mail']);
      }
      
      foreach( $this->attachment as $a)
      {
        if($a['type'] != NULL)
          $message->attach(new Swift_Message_Attachment(new Swift_File($a['file']), $a['filename'], $a['type']));
        else
          $message->attach(new Swift_Message_Attachment(new Swift_File($a['file']), $a['filename']));
        
      }    
      
      if($newLetter)
      $res = $this->swift->batchSend($message, $recipients, new Swift_Address($this->mail_from, $this->from_name));
      else
      $res = $this->swift->send($message, $recipients, new Swift_Address($this->mail_from, $this->from_name));
        
      $this->swift->disconnect();
    }
    catch(Swift_Message_MimeException  $e)
    {
      return false;
    }
    
  }
  
  public function setBody($body)
  {
    $this->body = $body;
  }
  
  public function setObject($object)
  {
    $this->object = utf8_decode($object);
  }
  
  public function addRecipient($recipient, $name='')
  {
    $this->addRecipient[] = array('mail'=>$recipient, 'name'=>$name);
  }
  
  public function addAttachment($file, $fileName, $type='')
  {
    $this->attachment[] = array('file'=>$file, 'filename'=>$fileName, 'type'=>$type);
  }
}


?>