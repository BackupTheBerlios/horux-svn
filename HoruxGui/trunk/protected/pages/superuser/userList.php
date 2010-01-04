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

Prado::using('horux.pages.superuser.sql');

class UserList extends PageList
{
    public function onInit ($param)
    {
        $superAdmin = $this->Application->getUser()->getSuperAdmin();

        if(!$superAdmin)
        {
            $this->tbb->add->setVisible(false);
            $this->tbb->delete->setVisible(false);
            $this->tbb->print->setVisible(false);
        }
        
    }

    protected function getData()
    {

        $superAdmin = $this->Application->getUser()->getSuperAdmin();

        if($superAdmin)
        {
            $param = $this->Application->getParameters();
            $groupId = $this->Application->getUser()->getGroupID();

            if( ($param['appMode'] == 'saas' && $groupId == 1) || $param['appMode'] != 'saas' )
            {
                $cmd=$this->db->createCommand(SQL::SQL_GET_ALL_USER);
            }
            else
            {
                $cmd=$this->db->createCommand(SQL::SQL_GET_ALL_USER_SAAS);
            }

            
            $dataReader=$cmd->query();

        }
        else
        {
            $cmd=$this->db->createCommand(SQL::SQL_GET_ID_USER);
            $cmd->bindParameter(":id",$this->Application->getUser()->getUserId(), PDO::PARAM_INT);
            $dataReader=$cmd->query();

            $this->tbb->setAddVisible(false);
            $this->tbb->setDelVisible(false);
            $this->tbb->setPrintVisible(false);
        }

        $connection->Active=false;

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
        $this->pdf->AddPage();

        $param = $this->Application->getParameters();
        $groupId = $this->Application->getUser()->getGroupID();

        if( ($param['appMode'] == 'saas' && $groupId == 1) || $param['appMode'] != 'saas' )
        {
            $cmd = $this->db->createCommand( SQL::SQL_GET_ALL_USER_2 );
        }
        else
        {
            $cmd = $this->db->createCommand( SQL::SQL_GET_ALL_USER_2_SAAS );
        }

        $data =  $cmd->query();
        $data = $data->readAll();

        $this->pdf->SetFont('Arial','',11);
        $this->pdf->Cell(0,10,utf8_decode(Prado::localize('List of the super users')),0,0,'L');
        $this->pdf->Ln(10);

        $this->pdf->setDefaultFont();

        $header = array(utf8_decode(Prado::localize("Name")),
            utf8_decode(Prado::localize("Firstname")),
            utf8_decode(Prado::localize("Username")),
            utf8_decode(Prado::localize("Email")),
            utf8_decode(Prado::localize("Phone")),
            utf8_decode(Prado::localize("Group"))
        );

        //Couleurs, �paisseur du trait et police grasse
        $this->pdf->SetFillColor(124,124,124);
        $this->pdf->SetTextColor(255);
        $this->pdf->SetDrawColor(255);
        $this->pdf->SetLineWidth(.3);
        $this->pdf->SetFont('','B');
        //En-t�te
        $w=array(30,30,35,35,30,30);
        for($i=0;$i<count($header);$i++)
        $this->pdf->Cell($w[$i],7,$header[$i],1,0,'C',1);
        $this->pdf->Ln();
        //Restauration des couleurs et de la police
        $this->pdf->SetFillColor(215,215,215);
        $this->pdf->SetTextColor(0);
        $this->pdf->SetFont('');
        //Donn�es
        $fill=false;


        foreach($data as $d)
        {
            $name= utf8_decode($d['name']);
            $firstname = utf8_decode($d['firstname']);
            $email = utf8_decode($d['email2']);
            $phone = utf8_decode($d['phone2']);
            $username = utf8_decode($d['username']);
            $group = utf8_decode($d['groupName']);

            $this->pdf->Cell($w[0],6,$name,'LR',0,'L',$fill);
            $this->pdf->Cell($w[1],6,$firstname,'LR',0,'L',$fill);
            $this->pdf->Cell($w[2],6,$username,'LR',0,'C',$fill);
            $this->pdf->Cell($w[3],6,$email,'LR',0,'C',$fill);
            $this->pdf->Cell($w[4],6,$phone,'LR',0,'C',$fill);
            $this->pdf->Cell($w[5],6,$group,'LR',0,'C',$fill);
            $this->pdf->Ln();
            $fill=!$fill;

        }

        $this->pdf->Cell(array_sum($w),0,'','T');

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
                    $id_user = $cb->Value;

                    if($id_user>1)
                    {
                        $cmd=$this->db->createCommand(SQL::SQL_GET_USER_BY_ID);
                        $cmd->bindParameter(":id",$id_user);
                        $res = $cmd->query();
                        $res = $res->read();
                        $this->log("Delete the super user: ".$res['name']);

                        //remove the person
                        $cmd=$this->db->createCommand(SQL::SQL_DELETE_USER);
                        $cmd->bindParameter(":id",$id_user);
                        $cmd->execute();
                        $nDelete++;
                    }
                }
            }
        }

        if($koMsg !== '')
        $pBack = array('koMsg'=>$koMsg);
        else
        $pBack = array('okMsg'=>Prado::localize('{n} user(s) was deleted',array('n'=>$nDelete)));
        $this->Response->redirect($this->Service->constructUrl('superuser.userList',$pBack));
    }


    public function onEdit($sender,$param)
    {

        if(count($this->DataGrid->DataKeys) === 0)
        {
            $pBack = array('koMsg'=>Prado::localize('Select one item'));
            $this->Response->redirect($this->Service->constructUrl('superuser.userList',$pBack));

        }

        $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];

        if(is_numeric($id))
        {
            $pBack = array('id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('superuser.userMod',$pBack));
        }

        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            {
                $pBack = array('id'=>$cb->Value);
                $this->Response->redirect($this->Service->constructUrl('superuser.userMod',$pBack));
            }
        }

        $pBack = array('koMsg'=>Prado::localize('Select one item'));
        $this->Response->redirect($this->Service->constructUrl('superuser.userList',$pBack));
    }
}
?>
