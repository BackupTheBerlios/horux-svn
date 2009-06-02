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

class mediaList extends PageList
{
    protected function getData()
    {
        $deviceId = $this->display->getSelectedvalue();
    
        $sql = "SELECT * FROM  hr_horux_media_media WHERE id_device=".$deviceId." ORDER BY `order`";

        $cmd=$this->db->createCommand($sql);
        $dataKey = $cmd->query();
        $dataKey = $dataKey->readAll(); 

        return $dataKey;
    }
    
    protected function getDisplay()
    {
        $sql = "SELECT id AS Value, name AS Text FROM  hr_device WHERE `type`='horux_media'";

        $cmd=$this->db->createCommand($sql);
        $dataKey = $cmd->query();
        $dataKey = $dataKey->readAll(); 

        return $dataKey;    
    }
    
    
    public function onLoad($param)
    {
        parent::onLoad($param); 

        if(!$this->IsPostBack)
        {
            $this->display->DataTextField='Text';
            $this->display->DataValueField='Value';
            $this->display->DataSource=$this->Display;
            $this->display->dataBind();
            if(count($this->Display)>0)
            {
              $this->display->setSelectedIndex(0);
              $this->DataGrid->DataSource=$this->Data;
              $this->DataGrid->dataBind();
            }

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

    public function onNew($sender, $param)
    {
      $pBack = array("deviceId"=>$this->display->getSelectedValue());
      $this->Response->redirect($this->Service->constructUrl('components.media.addMedia',$pBack));
    }

    public function onReloadDisplay($sender, $param)
    {
      require_once( 'XML/RPC.php' );
      $client = new XML_RPC_Client("RPC2", "localhost", 7000);
      
      $msg = new XML_RPC_Message("horuxInfoDisplay.reload");
      @$response = $client->send($msg);
    }

    public function actionPublished($sender, $param)
    {
        if($param instanceof TCommandEventParameter)
        {
            if($param->CommandName == 'publish')
            {
                $sql = "UPDATE 
                            `hr_horux_media_media` 
                        SET
                            published=abs(published-1)
                        WHERE
                            id={$param->CommandParameter}";
                $cmd=$this->db->createCommand($sql);
                $cmd->Execute();

                $asset = $this->Application->getAssetManager();
                $urlTick = $asset->publishFilePath('./protected/pages/components/media/assets/tick.gif');    
                $asset = $this->Application->getAssetManager();
                $urlPublish = $asset->publishFilePath('./protected/pages/components/media/assets/publish_x.gif');

                if($sender->ImageUrl == $urlTick)
                {
                  $sender->ImageUrl = $urlPublish;
                }
                else
                {
                  $sender->ImageUrl = $urlTick;
              }                
            }
        }
    }
    
    public function actionOrder($sender, $param)
    {
        $db=$this->db;
        $deviceId = $this->display->getSelectedvalue();

        if($param instanceof TCommandEventParameter)
        {
            if($param->CommandName == 'down')
            {

                $sql = "SELECT 
                            `order`,
                            id 
                        FROM 
                            hr_horux_media_media 
                        WHERE 
                            id={$param->CommandParameter}";
                            

                $cmd=$this->db->createCommand($sql);
                $dataKey = $cmd->query();
                $dataKey = $dataKey->read();

                $sql = "SELECT 
                            `order`,
                            id 
                        FROM 
                            hr_horux_media_media 
                        WHERE 
                            `order`>={$dataKey['order']}
                        AND
                            id_device=$deviceId
                        ORDER BY 
                            `order`
                        LIMIT 0,2";
                        
                $cmd=$this->db->createCommand($sql);
                $dataKey = $cmd->query();
                $dataKey = $dataKey->readAll();

                $publishOrder1 = $dataKey[0]['order'];
                $publishOrder2 = $dataKey[1]['order'];
                $id= $dataKey[1]['id'];

                $sql = "UPDATE 
                            `hr_horux_media_media` 
                        SET
                            `order`=$publishOrder2
                        WHERE
                            id={$param->CommandParameter}";
                
                $cmd=$this->db->createCommand($sql);
                $dataKey = $cmd->execute();

                $sql = "UPDATE 
                            `hr_horux_media_media` 
                        SET
                            `order`=$publishOrder1
                        WHERE
                            id={$id}";
                $cmd=$this->db->createCommand($sql);
                $dataKey = $cmd->execute();
                

            }
            if($param->CommandName == 'up')
            {
                $sql = "SELECT 
                            `order`,
                            id 
                        FROM 
                            hr_horux_media_media 
                        WHERE 
                            id={$param->CommandParameter}";

                $cmd=$this->db->createCommand($sql);
                $dataKey = $cmd->query();
                $dataKey = $dataKey->read();

                $sql = "SELECT 
                            `order`,
                            id 
                        FROM 
                            hr_horux_media_media 
                        WHERE 
                            `order`<={$dataKey['order']}
                        AND
                            id_device=$deviceId
                        ORDER BY 
                            `order` DESC
                        LIMIT 0,2";

                $cmd=$this->db->createCommand($sql);
                $dataKey = $cmd->query();
                $dataKey = $dataKey->readAll();

                $publishOrder1 = $dataKey[0]['order'];
                $publishOrder2 = $dataKey[1]['order'];
                $id= $dataKey[1]['id'];

                $sql = "UPDATE 
                            `hr_horux_media_media` 
                        SET
                            `order`=$publishOrder2
                        WHERE
                            id={$param->CommandParameter}";
                $cmd=$this->db->createCommand($sql);
                $dataKey = $cmd->execute();

                $sql = "UPDATE 
                            `hr_horux_media_media` 
                        SET
                            `order`=$publishOrder1
                        WHERE
                            id={$id}";
                $cmd=$this->db->createCommand($sql);
                $dataKey = $cmd->execute();
            }
        }

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);

    }    

    public function selectionChanged($sender,$param)
    {
      $this->DataGrid->DataSource=$this->Data;
      $this->DataGrid->dataBind();
      $this->Page->CallbackClient->update('list', $this->DataGrid);
    }

    public function itemCreated($sender,$param)
    {
        $item=$param->Item;

        if($item->ItemType==='Item' || 
           $item->ItemType==='AlternatingItem' || 
           $item->ItemType==='EditItem')
        {

            $asset = $this->Application->getAssetManager();
            $urlTick = $asset->publishFilePath('./protected/pages/components/media/assets/tick.gif');    
            $asset = $this->Application->getAssetManager();
            $urlPublish = $asset->publishFilePath('./protected/pages/components/media/assets/publish_x.gif');    

            if($item->DataItem['published'])
                $item->publish->publishImg->setImageUrl($urlTick);
            else
                $item->publish->publishImg->setImageUrl($urlPublish);

            $deviceId = $this->display->getSelectedvalue();

    
            $sql = "SELECT 
                        COUNT(id) AS nb 
                    FROM 
                        hr_horux_media_media
                    WHERE
                        id_device=$deviceId";

            $cmd=$this->db->createCommand($sql);
            $dataKey = $cmd->query();
            $dataKey = $dataKey->read(); 
            
            if($dataKey['nb'] > 1)
            {
                if(($item->ItemIndex + ($this->DataGrid->CurrentPageIndex * $this->DataGrid->getPageSize())) ==
                    $dataKey['nb']-1)
                    $item->order->down->Visible=false;
        
                if($item->ItemIndex == 0 && $this->DataGrid->CurrentPageIndex==0)
                    $item->order->up->Visible=false;
            }
            else
            {
                $item->order->down->Visible=false;
                $item->order->up->Visible=false;
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

    public function onDelete($sender,$param)
    {
        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
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
                $cmd=$this->db->createCommand("DELETE FROM hr_horux_media_media WHERE id=:id");
                $cmd->bindParameter(":id",$cb->Value);
                if($cmd->execute())
                  $nDelete++;
            }
         }
        }
        
        if($koMsg !== '')
          $pBack = array('koMsg'=>$koMsg);
        else
          $pBack = array('okMsg'=>Prado::localize('{n} media was deleted',array('n'=>$nDelete)));
        $this->Response->redirect($this->Service->constructUrl('components.media.mediaList',$pBack));
    }


    public function onEdit($sender,$param)
    {
        if(count($this->DataGrid->DataKeys) === 0)
        {
                $pBack = array('koMsg'=>Prado::localize('Select one item'));
                $this->Response->redirect($this->Service->constructUrl('components.media.mediaList',$pBack));

        }

        $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];
        if(is_numeric($id)) 
        {
              $pBack = array('id'=>$id);
              $this->Response->redirect($this->Service->constructUrl('components.media.modMedia',$pBack));
        }

        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            {
              $pBack = array('id'=>$cb->Value);
              $this->Response->redirect($this->Service->constructUrl('components.media.modMedia',$pBack));
            }
        }

        $pBack = array('koMsg'=>Prado::localize('Select one item'));
        $this->Response->redirect($this->Service->constructUrl('components.media.mediaList',$pBack));
    }
 }
?>
