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

Prado::using('horux.pages.userGroup.sql');

class UserGroupList extends PageList
{
    protected function getData()
    {
        $command=$this->db->createCommand(SQL::SQL_SELECT_ALL_GROUP);
        $dataReader=$command->query();

        $connection->Active=false;  // connection is established

        return $dataReader;
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

    protected function onPrint()
    {
        parent::onPrint();

        $cellHeaderWidth = 6;
        $cellHeaderHeight = 20;

        $this->pdf->AddPage();

        $this->pdf->SetFont('Arial','',11);
        $this->pdf->Cell(0,10,utf8_decode(Prado::localize('List of the users groups')),0,0,'L');
        $this->pdf->Ln(10);
        $this->pdf->setDefaultFont();

        $groups = $this->getData();

        foreach($groups as $ot)
        {
            $this->pdf->SetTextColor(0);
            $this->pdf->SetDrawColor(0);
            $this->pdf->SetFont('Arial','B',11);
            $this->pdf->SetLineWidth(0.4);
            $this->pdf->Cell(0,6,utf8_decode($ot['name']),'B',1,'L');
            $this->pdf->setDefaultFont();
            $this->pdf->SetLineWidth(0.1);
            $this->pdf->SetDrawColor(127);

            $this->pdf->Cell(60,6,utf8_decode(Prado::localize('Access plugin')),'B',0,'L');
            $this->pdf->Cell(0,6,utf8_decode($ot['accessPlugin']),'B',1,'L');

            $this->pdf->Cell(60,6,utf8_decode(Prado::localize('Description')),'B',0,'L');
            $this->pdf->Cell(0,6,utf8_decode($ot['comment']),'B',1,'L');

            $command=$this->db->createCommand("SELECT * FROM hr_device WHERE accessPoint>0");
            $dataReader=$command->query();

            $this->pdf->Ln(5);

            $this->pdf->setDefaultFont();

            //! put a marge
            $this->pdf->Cell(30);

            $this->pdf->SetFillColor(124,124,124);
            $this->pdf->SetTextColor(255);
            $this->pdf->SetDrawColor(255);
            $this->pdf->SetLineWidth(.3);

            $nEntry = array();
            foreach($dataReader as $device)
            {
                $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode($device['name']),1,0,'D', true);
                $nEntry[] = $device['id'];
            }

            $this->pdf->Ln($cellHeaderHeight);


            $command=$this->db->createCommand("SELECT * FROM hr_access_level");
            $dataAccess=$command->query();

            foreach($dataAccess as $access)
            {
                $this->pdf->Cell(30,$cellHeaderWidth,utf8_decode($access['name']),1,0,'L', true);

                for($i=0; $i<count($nEntry); $i++)
                {
                    $cmd = $this->db->createCommand( "SELECT * FROM hr_user_group_access WHERE id_access_level=".$access['id']." AND id_device=".$nEntry[$i]." AND id_group=".$ot['id'] );
                    $data = $cmd->query();
                    $data = $data->readAll();
                    if($data)
                        $this->pdf->Image("./fpdf/ok.png", $this->pdf->GetX() + ($i*6) + 1.5 , $this->pdf->GetY()+1.5 , 3, 3);
                    else
                        $this->pdf->Image("./fpdf/ko.png", $this->pdf->GetX() + ($i*6) + 1.5 , $this->pdf->GetY()+1.5 , 3, 3);
                }

                $this->pdf->Ln(6);
            }

            $this->pdf->Ln(10);
        }

        $this->pdf->render();
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
                    $cmd=$this->db->createCommand(SQL::SQL_HAS_CHILDREN);
                    $cmd->bindParameter(":id",$cb->Value);
                    $query = $cmd->query();
                    $hasChildren = false;
                    if($query)
                    {
                        $data = $query->read();
                        if($data['n'] > 0 ) $hasChildren = true;
                    }
                    if(!$hasChildren)
                    {
                        $cmd=$this->db->createCommand(SQL::SQL_GET_GROUP);
                        $cmd->bindParameter(":id",$cb->Value);
                        $cmd = $cmd->query();
                        $data = $cmd->read();
                        $this->log("Delete the user group: ".$data['name']);

                        $cmd=$this->db->createCommand(SQL::SQL_REMOVE_ACCESS_GROUP);
                        $cmd->bindParameter(":id",$cb->Value);
                        $cmd->execute();
                        $cmd=$this->db->createCommand(SQL::SQL_REMOVE_GROUP);
                        $cmd->bindParameter(":id",$cb->Value);
                        if($cmd->execute())
                        {
                            $nDelete++;
                        }
                    }
                    else
                    {
                        $cmd = $this->db->createCommand( SQL::SQL_GET_GROUP );
                        $cmd->bindParameter(":id",$cb->Value, PDO::PARAM_INT);
                        $query = $cmd->query();
                        if($query)
                        {
                            $data = $query->read();
                            $koMsg = Prado::localize('Cannot delete the group {name}, it contains one or more person', array('name' =>$data['name']));
                        }
                    }
                }
            }
        }

        if($koMsg !== '')
        $pBack = array('koMsg'=>$koMsg);
        else
        $pBack = array('okMsg'=>Prado::localize('{n} group was deleted',array('n'=>$nDelete)));
        $this->Response->redirect($this->Service->constructUrl('userGroup.UserGroupList',$pBack));
    }


    public function onEdit($sender,$param)
    {
        if(count($this->DataGrid->DataKeys) === 0)
        {
            $pBack = array('koMsg'=>Prado::localize('Select one item'));
            $this->Response->redirect($this->Service->constructUrl('userGroup.UserGroupList',$pBack));

        }

        $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];

        if(is_numeric($id))
        {
            $pBack = array('id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('userGroup.mod',$pBack));
        }

        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            {
                $pBack = array('id'=>$cb->Value);
                $this->Response->redirect($this->Service->constructUrl('userGroup.mod',$pBack));
            }
        }

        $pBack = array('koMsg'=>Prado::localize('Select one item'));
        $this->Response->redirect($this->Service->constructUrl('userGroup.UserGroupList',$pBack));
    }
}
?>
