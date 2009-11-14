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

class timecode extends PageList
{
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

    public function getData()
    {
        $cmd=$this->db->createCommand("SELECT * FROM hr_timux_timecode ORDER BY id");
        $data = $cmd->query();
        $data = $data->readAll();

        return $data;
    }

    public function itemCreated($sender, $param)
    {
        $item=$param->Item;


        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' )
        {
            $item->ttype->type->Text = $item->DataItem['type'] == 'leave' ? Prado::localize('Leave') : Prado::localize('Absence');
        }
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.panel'));
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
                    $cmd=$this->db->createCommand("DELETE FROM hr_timux_timecode WHERE id=:id");
                    $cmd->bindParameter(":id",$cb->Value);
                    if($cmd->execute())
                    {
                        $nDelete++;
                    }
                    //$this->log("Delete the key: ".$data['serialNumber']);

                }
            }
        }

        if($koMsg !== '')
            $pBack = array('koMsg'=>$koMsg);
        else
            $pBack = array('okMsg'=>Prado::localize('{n} time code was deleted',array('n'=>$nDelete)));

        $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timecode.timecode',$pBack));
    }

    public function onEdit($sender,$param)
    {
        if(count($this->DataGrid->DataKeys) === 0)
        {
            $pBack = array('koMsg'=>Prado::localize('Select one item'));
            $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timecode.timecode',$pBack));

        }

        $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];
        if(is_numeric($id))
        {
            $pBack = array('id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timecode.mod',$pBack));
        }

        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            {
                $pBack = array('id'=>$cb->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timecode.mod',$pBack));
            }
        }

        $pBack = array('koMsg'=>Prado::localize('Select one item'));
        $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timecode.timecode',$pBack));
    }
}

?>