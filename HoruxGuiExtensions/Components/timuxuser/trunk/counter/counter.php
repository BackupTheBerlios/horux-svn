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

Prado::using('horux.pages.components.timuxuser.employee');

class counter extends PageList
{
    protected $userId = 0;
    protected $employee = null;

    public function onLoad($param)
    {
        parent::onLoad($param);

        $app = $this->getApplication();
        $usedId = $app->getUser()->getUserID() == null ? 0 : $app->getUser()->getUserID();

        $cmd=$this->db->createCommand("SELECT user_id FROM hr_superusers WHERE id=$usedId");
        $data = $cmd->query();
        $dataUser = $data->read();
        $this->userId = $dataUser['user_id'];

        $this->employee = new employee($this->userId );

        if(!$this->IsPostBack)
        {
            $FilterEmployee = "";
            if(isset($this->Request['id']))
            {
                $cmd=$this->db->createCommand("SELECT CONCAT(name, ' ' , firstname) AS employee FROM hr_user  WHERE id=:id");
                $id = $this->Request['id'];
                $cmd->bindParameter(":id",$id,PDO::PARAM_STR);

                $data = $cmd->query();
                $data = $data->read();
                $FilterEmployee = $data['employee'];
            }
            else
                $FilterEmployee = $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterEmployee', false);

            $FilterTimecode = $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterTimecode', false);
            $FilterDepartment = $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterDepartment', false);


            $this->FilterTimecode->DataSource=$this->TimeCode;
            $this->FilterTimecode->dataBind();

            $this->FilterDepartment->DataSource=$this->DepartmentList;
            $this->FilterDepartment->dataBind();


            if($FilterEmployee)
                $this->FilterEmployee->Text = $FilterEmployee;

            if($FilterTimecode)
                 $this->FilterTimecode->setSelectedValue($FilterTimecode);
            else
                $this->FilterTimecode->setSelectedValue(0);

            if($FilterDepartment)
                $this->FilterDepartment->setSelectedValue($FilterDepartment);
            else
                $this->FilterTimecode->setSelectedValue(0);

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

    public function getDepartmentList()
    {
        $role = $this->employee->getRole();
        $department = $this->employee->getDepartmentId();
        $cmd = NULL;
        if($role == 'manager')
            $cmd = $this->db->createCommand( "SELECT name AS Text, id AS Value FROM hr_department WHERE id=$department");
        else
            $cmd = $this->db->createCommand( "SELECT name AS Text, id AS Value FROM hr_department ORDER BY name");
        $data = $cmd->query();
        $data = $data->readAll();

        if($role != 'manager')
        {
            $dataAll[] = array("Value"=>0, "Text"=>Prado::localize("--- All ---"));

            $data = array_merge($dataAll, $data);
        }

        return $data;

    }
    
    protected function getTimeCode()
    {

        $cmd = $this->db->createCommand( "SELECT CONCAT('[',abbreviation,'] - ', name) AS Text, id AS Value FROM hr_timux_timecode");
        $data = $cmd->query();
        $data = $data->readAll();

        $dataAll[] = array("Value"=>0, "Text"=>Prado::localize("--- All ---"));

        $data = array_merge($dataAll, $data);

        return $data;

    }

    public function getData()
    {

        $employee = $this->FilterEmployee->SafeText;

        if($employee != '')
        {
            $employee = " (u.name LIKE '%$employee%' OR u.firstname LIKE '%$employee%' OR CONCAT(u.name, ' ', u.firstname) LIKE '%$employee%' OR CONCAT(u.firstname, ' ', u.name) LIKE '%$employee%') AND ";
        }

        $timecode = $this->FilterTimecode->getSelectedValue();

        if($timecode != 0)
        {
            $timecode = " ac.timecode_id=".$timecode." AND ";
        }
        else
            $timecode = "";

        $department = $this->FilterDepartment->getSelectedValue();
        if($department != 0)
        {
            $department = ' u.department='.$department.' AND ';
        }
        else
            $department = '';

        $cmd=$this->db->createCommand("SELECT CONCAT(u.name, ' ' , u.firstname) AS employee, ac.nbre, CONCAT('[',tt.abbreviation,'] - ', tt.name) AS timecode, tt.formatDisplay,ac.id, d.name AS department, tt.useMinMax, tt.minHour, tt.maxHour FROM hr_timux_activity_counter AS ac LEFT JOIN hr_user AS u ON u.id=ac.user_id LEFT JOIN hr_timux_timecode AS tt ON tt.id=ac.timecode_id LEFT JOIN hr_department AS d ON d.id=u.department WHERE $employee $timecode $department  ac.year=0 AND ac.month=0 ORDER BY u.name,u.firstname,tt.abbreviation");

        $data = $cmd->query();
        $data = $data->readAll();

        return $data;
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.panel'));
    }


    public function selectionChangedEmployee($sender, $param)
    {
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterEmployee', $this->FilterEmployee->SafeText);

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }


    public function selectionChangedDepartment($sender, $param)
    {
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterDepartment', $this->FilterDepartment->getSelectedValue());

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }

    public function selectionChangedTimeCode($sender, $param)
    {
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterTimecode', $this->FilterTimecode->getSelectedValue());

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }

    public function itemCreated($sender, $param)
    {
        $item=$param->Item;

        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' )
        {
            if( $item->DataItem['formatDisplay'] == 'hour' )
            {
                if($item->DataItem['nbre'] > 0)
                {
                    $item->nnbre->nbre->Text = sprintf("+%.2f ",$item->DataItem['nbre']).Prado::localize('hours');
                    if($item->DataItem['useMinMax'] )
                    {
                        if($item->DataItem['nbre']>$item->DataItem['maxHour'])
                            $item->nnbre->nbre->ForeColor = "red";
                    }

                }

                if($item->DataItem['nbre'] < 0)
                {
                    $item->nnbre->nbre->Text = sprintf("%.2f ",$item->DataItem['nbre']).Prado::localize('hours');

                    if($item->DataItem['useMinMax'] )
                    {
                        if($item->DataItem['nbre']<$item->DataItem['minHour'])
                            $item->nnbre->nbre->ForeColor = "red";
                    }
                    else
                        $item->nnbre->nbre->ForeColor = "red";
                }
                if($item->DataItem['nbre'] == 0)
                    $item->nnbre->nbre->Text = sprintf("%.2f ",$item->DataItem['nbre']).Prado::localize('hours');
                    
            }

            if( $item->DataItem['formatDisplay'] == 'day' )
            {
                if($item->DataItem['nbre'] > 0)
                {
                    $item->nnbre->nbre->Text = sprintf("+%.2f ",$item->DataItem['nbre']).Prado::localize('days');
                    if($item->DataItem['useMinMax'] )
                    {
                        if($item->DataItem['nbre']>$item->DataItem['maxHour'])
                            $item->nnbre->nbre->ForeColor = "red";
                    }

                }

                if($item->DataItem['nbre'] < 0)
                {
                    $item->nnbre->nbre->Text = sprintf("%.2f ",$item->DataItem['nbre']).Prado::localize('days');
                    if($item->DataItem['useMinMax'] )
                    {
                        if($item->DataItem['nbre']<$item->DataItem['minHour'])
                            $item->nnbre->nbre->ForeColor = "red";
                    }
                    else
                        $item->nnbre->nbre->ForeColor = "red";
                }

                if($item->DataItem['nbre'] == 0)
                    $item->nnbre->nbre->Text = sprintf("%.2f ",$item->DataItem['nbre']).Prado::localize('days');
            }
        }
    }


    public function onEdit($sender,$param)
    {
        if(count($this->DataGrid->DataKeys) === 0)
        {
            $pBack = array('koMsg'=>Prado::localize('Select one item'));
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.counter.counter',$pBack));

        }

        $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];
        if(is_numeric($id))
        {
            $pBack = array('id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.counter.mod',$pBack));
        }

        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            {
                $pBack = array('id'=>$cb->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.counter.mod',$pBack));
            }
        }

        $pBack = array('koMsg'=>Prado::localize('Select one item'));
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.counter.counter',$pBack));
    }
}
?>
