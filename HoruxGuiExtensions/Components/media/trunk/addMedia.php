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

class addMedia extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        
       if(!$this->isPostBack)
        {

            $deviceId = $this->Request["deviceId"];
            
            $cmd = $this->db->createCommand( "SELECT * FROM `hr_horux_media` WHERE id_device=:id" );
            $cmd->bindParameter(":id",$deviceId,PDO::PARAM_STR);
            $row = $cmd->query();
            $row = $row->read();

            require_once( 'XML/RPC.php' );
            $client = new XML_RPC_Client("RPC2", $row["ip"], $row["port"]);
            
            $msg = new XML_RPC_Message("horuxInfoDisplay.getMediaList");
            @$response = $client->send($msg);
            
            if($response)
            {
              if (!$response->faultCode()) 
              {
                  $v = $response->value();

                  $result = explode(",", html_entity_decode( $v->scalarval() ));
                  
                  $media = array();
                  foreach($result as $k=>$v)
                  {
                    $media[] = array("Value"=>$v, "Text"=>$v);
                  }

                  $this->media->DataTextField='Text';
                  $this->media->DataValueField='Value';
                  $this->media->DataSource=$media;
                  $this->media->dataBind();
                  if(count($media)>0)
                      $this->media->setSelectedIndex(0);

              } 
              else 
              {
                  $content_error = "ERROR - ";
                  $content_error .= "Code: " . $response->faultCode() . " Reason '" . $response->faultString() . "'<br/>";
              };
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
            $pBack = array('okMsg'=>Prado::localize('The media was added successfully'), 'id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('components.media.modMedia', $pBack));
          }
          else
          {
            $pBack = array('koMsg'=>Prado::localize('The media was not added'));
          }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The media was added successfully'));
          }
          else
            $pBack = array('koMsg'=>Prado::localize('The info message was not added'));
          $this->Response->redirect($this->Service->constructUrl('components.media.mediaList',$pBack));
        }
    }

    public function onCancel($sender, $param)
    {
      $this->Response->redirect($this->Service->constructUrl('components.media.mediaList'));  
    }


    protected function saveData()
    {
        $deviceId = $this->Request["deviceId"];

        $cmd = $this->db->createCommand("SELECT * FROM `hr_horux_media_media` WHERE id_device=:id ORDER BY `order` DESC ");
        $cmd->bindParameter(":id",$deviceId, PDO::PARAM_STR);
        $row = $cmd->query();
        $row = $row->read();
    
        $cmd = $this->db->createCommand( "INSERT INTO `hr_horux_media_media` (`name` ,`type`,`path`,`time`, `id_device`, `order`  ) VALUES (:name, :type, :path, :time,:id_device, :order)" );

        $type = "IMAGE";
        if($this->movie->getChecked())
           $type = "MOVIE";
        
        $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":type",$type, PDO::PARAM_STR);
        $cmd->bindParameter(":path",$this->media->getSelectedValue(), PDO::PARAM_STR);
        $cmd->bindParameter(":time",$this->during->SafeText, PDO::PARAM_STR);
        $cmd->bindParameter(":id_device",$deviceId, PDO::PARAM_STR);
        $order = $row["order"]+1;
        $cmd->bindParameter(":order",$order, PDO::PARAM_STR);


        $cmd->execute();

        $lastId = $this->db->LastInsertID;

        return $lastId;
    }
}
