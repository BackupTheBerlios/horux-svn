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

class UserGroupList extends PageList
{
    protected function getData()
    {
        $cmd=$this->db->createCommand(SQL::SQL_GET_ALL_GROUP);
        $dataReader=$cmd->query();

        $connection->Active=false;

        return $dataReader;
    }

    public function onLoad($param)
    {
        parent::onLoad($param);

        $superAdmin = $this->Application->getUser()->getSuperAdmin();
        $param = $this->Application->getParameters();

        if($param['appMode'] == 'demo' && $superAdmin == 0)
        {
            $this->delete->setEnabled(false);
            $this->newUserGroup->setEnabled(false);
        }

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

        $nCell = 0;
        $accessRight = array();

        $cellHeaderWidth = 6;
        $cellHeaderHeight = 40;

        $this->pdf->AddPage('L');

        $this->pdf->SetFont('Arial','',11);
        $this->pdf->Cell(0,10,utf8_decode(Prado::localize('List of the super users groups')),0,0,'L');
        $this->pdf->Ln(10);

        $this->pdf->setDefaultFont();

        //! put a marge
        $this->pdf->Cell(30);

        $this->pdf->SetFillColor(124,124,124);
        $this->pdf->SetTextColor(255);
        $this->pdf->SetDrawColor(255);
        $this->pdf->SetLineWidth(.3);

        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('Control Panel')),1,0,'D', true);
        $accessRight[] = "controlPanel.ControlPanel";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('Super User')),1,0,'D', true);
        $accessRight[] = "superuser.userList";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('Super User Group')),1,0,'D', true);
        $accessRight[] = "superuser.userGroupList";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('Configuration')),1,0,'D', true);
        $accessRight[] = "configuration.config";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('Site')),1,0,'D', true);
        $accessRight[] = "site.Site";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('Hardware')),1,0,'D', true);
        $accessRight[] = "hardware.HardwareList";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('Opent time')),1,0,'D', true);
        $accessRight[] = "openTime.openTimeList";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('Alarms')),1,0,'D', true);
        $accessRight[] = "system.Alarms";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('Horux Service')),1,0,'D', true);
        $accessRight[] = "system.Service";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('Horux Status')),1,0,'D', true);
        $accessRight[] = "system.Status";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('User')),1,0,'D', true);
        $accessRight[] = "user.UserList";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('User Group')),1,0,'D', true);
        $accessRight[] = "userGroup.UserGroupList";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('User Wizard')),1,0,'D', true);
        $accessRight[] = "user.UserWizzard";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('Key')),1,0,'D', true);
        $accessRight[] = "key.KeyList";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('Access Level')),1,0,'D', true);
        $accessRight[] = "accessLevel.accessLevelList";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('Non Working Day')),1,0,'D', true);
        $accessRight[] = "nonWorkingDay.nonWorkingDay";

        $cmd = $this->db->createCommand( "SELECT * FROM hr_install WHERE type='component'" );
        $data = $cmd->query();
        $data = $data->readAll();

        $this->pdf->SetFillColor(176,176,176);

        foreach($data as $d)
        {
            $nCell++;
            $doc=new TXmlDocument();
            $doc->loadFromFile('./protected/pages/components/'.$d['name'].'/install.xml');
            $name = $doc->getElementByTagName('name');
            $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize($name->getValue())),1,0,'D', true);
            $accessRight[] = "components.".$d['name'];

        }

        $this->pdf->SetFillColor(124,124,124);

        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('Install/Uninstall')),1,0,'D', true);
        $accessRight[] = "installation.extensions";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('Devices Manager')),1,0,'D', true);
        $accessRight[] = "installation.devices";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('Component Manager')),1,0,'D', true);
        $accessRight[] = "installation.components";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('Template Manager')),1,0,'D', true);
        $accessRight[] = "installation.template";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('Language Manager')),1,0,'D', true);
        $accessRight[] = "installation.language";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('Global Checkin')),1,0,'D', true);
        $accessRight[] = "tool.GlobalCheckin";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('Horux Gui log')),1,0,'D', true);
        $accessRight[] = "tool.GuiLog";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('System Info')),1,0,'D', true);
        $accessRight[] = "help.SystemInfo";
        $this->pdf->VCell($cellHeaderWidth,$cellHeaderHeight,utf8_decode(Prado::localize('About')),1,1,'D', true);
        $accessRight[] = "help.About";

        $nCell += 25;

        $groups = $this->getData();

        foreach($groups as $g)
        {
            if($g['superAdmin'] == 1)
            $this->pdf->SetFillColor(255,76,76);
            else
            $this->pdf->SetFillColor(124,124,124);
            $this->pdf->Cell(30,$cellHeaderWidth,$g['name'],1,0,'L', true);

            for($i=0; $i<$nCell;$i++)
            {
                $cmd = $this->db->createCommand( "SELECT * FROM hr_gui_permissions  WHERE page LIKE '".$accessRight[$i]."%' AND value=".$g['id'] );
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

        $this->pdf->SetTextColor(0);
        $this->pdf->SetDrawColor(0);

        $groups = $this->getData();

        foreach($groups as $g)
        {
            $this->pdf->Cell(50,$cellHeaderWidth,utf8_decode( Prado::Localize("Super users in \"{g}\" group", array("g"=>$g['name'])) ),'B',1);

            $cmd = $this->db->createCommand( "SELECT su.name AS username, u.name, u.firstname FROM hr_superusers AS su LEFT JOIN hr_user AS u ON u.id=su.user_id WHERE su.group_id=".$g['id'] );
            $data = $cmd->query();
            $users = $data->readAll();

            $userList = array();
            foreach($users as $user)
            {
                $u = $user['username'];
                if(strlen($user['name']) > 0 || strlen($user['firstname']) > 0)
                $u .= "(".$user['name']." ".$user['firstname'].")";
                $userList[] = $u;
            }
            $userList = join(', ', $userList);
            $this->pdf->Cell(0,8,$userList,0,1);

        }

        $this->pdf->render();
    }

    public function selectionChangedName($sender,$param)
    {
        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }

    public function selectionChangedFirstName($sender,$param)
    {
        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }

    public function selectionChangedStatus($sender,$param)
    {
        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
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
                    $id_group = $cb->Value;

                    if($id_group != 1)
                    {

                        //! check if super user exist for this group
                        $cmd=$this->db->createCommand("SELECT COUNT(*) AS n FROM hr_superusers WHERE group_id=".$id_group);
                        $query = $cmd->query();
                        $data = $query->read();

                        if($data['n'] == 0)
                        {
                            $cmd=$this->db->createCommand("SELECT * FROM hr_superuser_group WHERE id=".$id_group);
                            $query = $cmd->query();
                            $data = $query->read();

                            $this->log("Delete the super user group :".$data['name']);

                            //remove the group
                            $cmd=$this->db->createCommand(SQL::SQL_DELETE_GROUP);
                            $cmd->bindParameter(":id",$id_group);
                            $cmd->execute();

                            // remove the permision of the group
                            $cmd=$this->db->createCommand(SQL::SQL_DELETE_GROUP_PERM);
                            $cmd->bindParameter(":id",$id_group);
                            $cmd->execute();
                            $nDelete++;
                        }
                        else
                        {
                            $koMsg = Prado::localize('One or more groups cannot be deleted. The group musst not contains super user to be deleted.');
                        }
                    }
                    else
                    $koMsg = Prado::localize('The main super user group cannot be deleted');

                }
            }
        }

        if($koMsg !== '')
        $pBack = array('koMsg'=>$koMsg);
        else
        $pBack = array('okMsg'=>Prado::localize('{n} group(s) was deleted',array('n'=>$nDelete)));
        $this->Response->redirect($this->Service->constructUrl('superuser.userGroupList',$pBack));
    }


    public function onEdit($sender,$param)
    {
        if(count($this->DataGrid->DataKeys) === 0)
        {
            $pBack = array('koMsg'=>Prado::localize('Select one item'));
            $this->Response->redirect($this->Service->constructUrl('superuser.userGroupList',$pBack));

        }

        $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];

        if(is_numeric($id))
        {
            $pBack = array('id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('superuser.userGroupMod',$pBack));
        }

        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            {
                $pBack = array('id'=>$cb->Value);
                $this->Response->redirect($this->Service->constructUrl('superuser.userGroupMod',$pBack));
            }
        }

        $pBack = array('koMsg'=>Prado::localize('Select one item'));
        $this->Response->redirect($this->Service->constructUrl('superuser.userGroupList',$pBack));
    }

}
?>
