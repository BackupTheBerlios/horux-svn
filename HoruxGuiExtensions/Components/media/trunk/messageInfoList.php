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

class messageInfoList extends PageList
{
    protected function getData()
    {

                
        $sql = "SELECT * FROM  hr_horux_infoDisplay_message WHERE type='INFO'";

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
            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();
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
                $this->addStandalone('sub',$cb->Value);

                $cmd=$this->db->createCommand("DELETE FROM hr_horux_infoDisplay_message WHERE id=:id");
                $cmd->bindParameter(":id",$cb->Value);
                if($cmd->execute())
                  $nDelete++;
            }
         }
        }
        
        if($koMsg !== '')
          $pBack = array('koMsg'=>$koMsg);
        else
          $pBack = array('okMsg'=>Prado::localize('{n} info message was deleted',array('n'=>$nDelete)));
        $this->Response->redirect($this->Service->constructUrl('components.infoDisplay.messageInfoList',$pBack));
    }


    public function onEdit($sender,$param)
    {
        if(count($this->DataGrid->DataKeys) === 0)
        {
                $pBack = array('koMsg'=>Prado::localize('Select one item'));
                $this->Response->redirect($this->Service->constructUrl('components.infoDisplay.messageInfoList',$pBack));

        }

        $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];
        if(is_numeric($id)) 
        {
              $pBack = array('id'=>$id);
              $this->Response->redirect($this->Service->constructUrl('components.infoDisplay.modMessageInfo',$pBack));
        }

        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            {
              $pBack = array('id'=>$cb->Value);
              $this->Response->redirect($this->Service->constructUrl('components.infoDisplay.modMessageInfo',$pBack));
            }
        }

        $pBack = array('koMsg'=>Prado::localize('Select one item'));
        $this->Response->redirect($this->Service->constructUrl('components.infoDisplay.messageInfoList',$pBack));
    }
 }
?>
