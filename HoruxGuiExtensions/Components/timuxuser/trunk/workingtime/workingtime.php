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

class workingtime extends PageList
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->IsPostBack)
        {
            $FilterEmployee = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterEmployee'];

            if($FilterEmployee)
                $this->FilterEmployee->Text = $FilterEmployee;

            $FilterDepartment = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterDepartment'];


            $this->FilterDepartment->DataSource=$this->DepartmentList;
            $this->FilterDepartment->dataBind();

            if($FilterDepartment)
                $this->FilterDepartment->setSelectedValue($FilterDepartment);
            else
                $this->FilterDepartment->setSelectedIndex(0);


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
        $employee = $this->FilterEmployee->SafeText;

        if($employee != '')
        {
            $employee = "u.name LIKE '%$employee%' OR u.firstname LIKE '%$employee%' OR CONCAT(u.name, ' ', u.firstname) LIKE '%$employee%' OR CONCAT(u.firstname, ' ', u.name) LIKE '%$employee%' AND ";
        }
        else
            $employee = '';

        $department = $this->FilterDepartment->getSelectedValue();
        if($department != 0)
        {
            $department = ' u.department='.$department.' AND ';
        }
        else
            $department = '';

        $cmd=$this->db->createCommand("SELECT tw.*, CONCAT(u.name,' ',u.firstname) AS employee, d.name AS department, tw.role FROM hr_timux_workingtime AS tw LEFT JOIN hr_user AS u ON u.id=tw.user_id LEFT JOIN hr_department AS d ON d.id=u.department WHERE $employee $department 1=1 ORDER BY employee, startDate");
        $data = $cmd->query();
        $data = $data->readAll();

        return $data;
    }

    public function getDepartmentList()
    {
        $cmd = $this->db->createCommand( "SELECT name AS Text, id AS Value FROM hr_department ORDER BY name");
        $data = $cmd->query();
        $data = $data->readAll();

        $dataAll[] = array("Value"=>0, "Text"=>Prado::localize("--- All ---"));

        $data = array_merge($dataAll, $data);

        return $data;

    }

    public function itemCreated($sender, $param)
    {
        $item=$param->Item;


        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' )
        {

            $item->ddate->date->Text = $this->dateFromSql($item->DataItem['startDate']);

            $item->hhoursByWeek2->hoursByWeek2->Text = $item->DataItem['workingPercent'] * $item->DataItem['hoursByWeek'] / 100;

            switch($item->DataItem['role'])
            {
                case 'employee':
                    $item->rrole->role->Text = Prado::localize("Employee");
                    break;
                case 'manager':
                    $item->rrole->role->Text = Prado::localize("Manager");
                    break;
                case 'rh':
                    $item->rrole->role->Text = Prado::localize("Human ressources / Direction");
                    break;
            }
        }
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.panel'));
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
                    $cmd=$this->db->createCommand("DELETE FROM hr_timux_workingtime WHERE id=:id");
                    $cmd->bindParameter(":id",$cb->Value);
                    if($cmd->execute())
                        $nDelete++;

                    //$this->log("Delete the key: ".$data['serialNumber']);

                }
            }
        }

        if($koMsg !== '')
            $pBack = array('koMsg'=>$koMsg);
        else
            $pBack = array('okMsg'=>Prado::localize('{n} working time was deleted',array('n'=>$nDelete)));
            
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.workingtime.workingtime',$pBack));
    }

    public function selectionChangedDepartment($sender, $param)
    {
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterDepartment'] = $this->FilterDepartment->getSelectedValue();

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }

    public function selectionChangedEmployee($sender,$param)
    {
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterEmployee'] = $this->FilterEmployee->SafeText;

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }

    public function onEdit($sender,$param)
    {
        if(count($this->DataGrid->DataKeys) === 0)
        {
            $pBack = array('koMsg'=>Prado::localize('Select one item'));
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.workingtime.workingtime',$pBack));

        }

        $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];
        if(is_numeric($id))
        {
            $pBack = array('id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.workingtime.mod',$pBack));
        }

        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            {
                $pBack = array('id'=>$cb->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.workingtime.mod',$pBack));
            }
        }

        $pBack = array('koMsg'=>Prado::localize('Select one item'));
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.workingtime.workingtime',$pBack));
    }
}

?>