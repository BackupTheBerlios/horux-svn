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

class booking extends PageList
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
            $FilterEmployee = $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterEmployee', false);
            $FilterStatus = $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterStatus', false);
            $FilterFrom = $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterFrom', false);
            $FilterUntil = $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterUntil', false);
            $FilterDepartment = $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterDepartment', false);

            $this->FilterDepartment->DataSource=$this->DepartmentList;
            $this->FilterDepartment->dataBind();

            if($FilterEmployee)
                $this->FilterEmployee->Text = $FilterEmployee;
                
            if($FilterStatus)
                $this->FilterStatus->setSelectedValue($FilterStatus);
            else
                $this->FilterStatus->setSelectedValue('all');

            if($FilterFrom)
                $this->from->Text = $FilterFrom;
            if($FilterUntil)
                $this->until->Text = $FilterUntil;

            if($FilterDepartment)
                $this->FilterDepartment->setSelectedValue($FilterDepartment);
            else
                $this->FilterDepartment->setSelectedValue(0);

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

    public function getData()
    {

        $employee = $this->FilterEmployee->SafeText;

        if($employee != '')
        {
            $employee = " (u.name LIKE '%$employee%' OR u.firstname LIKE '%$employee%' OR CONCAT(u.name, ' ', u.firstname) LIKE '%$employee%' OR CONCAT(u.firstname, ' ', u.name) LIKE '%$employee%') AND ";
        }

        $department = $this->FilterDepartment->getSelectedValue();
        if($department != 0)
        {
            $department = ' u.department='.$department.' AND ';
        }
        else
            $department = '';

        $status = $this->FilterStatus->getSelectedValue();

        switch($status)
        {
            case '1':
                $status = ' (tb.action=255 OR tb.action=500 OR tb.action=502 ) AND ';
                break;
            case '0':
                $status = ' (tb.action=254 OR tb.action=501 OR tb.action=503  ) AND ';
                break;
            default:
                $status = '';

         }

        $from = "";
        $until = "";

        $from = $this->dateToSql( $this->from->SafeText );
        $until = $this->dateToSql( $this->until->SafeText );

        $date = "";

        if($from == "" && $until == "")
        {
            //take the current month

            $date = getdate();
            $from = $date['year'].'-'.$date['mon'].'-1';
            $until = $date['year'].'-'.$date['mon'].'-'.date("t");

            $this->from->Text = "1-".$date['mon']."-".$date['year'];
            $this->until->Text = date("t")."-".$date['mon']."-".$date['year'];

            $date = " t.date>='$from' AND t.date<='$until' AND ";
        }
        else
        {
            if($from != "" && $until != "")
            {
                $date = " t.date>='$from' AND t.date<='$until' AND ";
            }
            if($from != "" && $until == "")
            {
                $date = " t.date>='$from' AND ";
            }
            if($from == "" && $until != "")
            {
                $date = " t.date<='$until' AND ";
            }

        }

        $cmd=$this->db->createCommand("SELECT CONCAT(u.name, ' ' , u.firstname) AS employee, t.id, t.date, tb.roundBooking AS time, tb.action, tb.actionReason, d.name AS department FROM hr_tracking AS t LEFT JOIN hr_timux_booking AS tb ON tb.tracking_id=t.id LEFT JOIN hr_user AS u ON u.id=t.id_user LEFT JOIN hr_department AS d ON d.id=u.department WHERE $date $status $employee $department 1=1 AND tb.action!='NULL' ORDER BY t.date DESC, t.time DESC  LIMIT 0,1000");

        $data = $cmd->query();
        $data = $data->readAll();

        return $data;
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.panel'));
    }


    public function selectionChangedStatus($sender, $param)
    {
        $this->onRefresh($sender, $param);
    }

    public function selectionChangedDepartment($sender, $param)
    {
        $this->onRefresh($sender, $param);
    }

    public function selectionChangedEmployee($sender, $param)
    {
        $this->onRefresh($sender, $param);
    }

    public function onRefresh($sender, $param)
    {
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterEmployee', $this->FilterEmployee->SafeText);
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterStatus', $this->FilterStatus->getSelectedValue());
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterFrom', $this->from->Text);
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterUntil', $this->until->Text);
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterDepartment', $this->FilterDepartment->getSelectedValue());

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }

    public function itemCreated($sender, $param)
    {
        $item=$param->Item;

        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' )
        {

            $item->ddate->date->Text = $this->dateFromSql($item->DataItem['date']);

            if($item->DataItem['action'] == 255)
                $item->aaction->action->Text = Prado::localize("Sign in");
            if($item->DataItem['action'] == 254)
                $item->aaction->action->Text = Prado::localize("Sign out");

            if($item->DataItem['action'] == 100)
            {
                $actionReason = explode("_",$item->DataItem['actionReason']);

                if($actionReason[0] > 0)
                {
                    $cmd=$this->db->createCommand("SELECT name, signtype FROM hr_timux_timecode WHERE id=".$actionReason[0]);
                    $data = $cmd->query();
                    $data = $data->read();

                    if(isset($actionReason[1]))
                    {
                        if($actionReason[1] == "IN")
                            $item->aaction->action->Text = Prado::localize("Sign in");
                        if($actionReason[1] == "OUT")
                            $item->aaction->action->Text = Prado::localize("Sign out");
                    }
                    else
                    {
                        if($data['signtype'] == "in")
                            $item->aaction->action->Text = Prado::localize("Sign in");
                        if($data['signtype'] == "out")
                            $item->aaction->action->Text = Prado::localize("Sign out");

                    }


                    $item->aactionr->actionr->Text = $data['name'];
                }
            }

            if($item->DataItem['action'] == 500)
            {
                $item->aaction->action->Text = Prado::localize("Sign in");
                $item->aactionr->actionr->Text =Prado::localize("Added manually");
            }

            if($item->DataItem['action'] == 501)
            {
                $item->aaction->action->Text = Prado::localize("Sign out");
                $item->aactionr->actionr->Text = Prado::localize("Added manually");
            }

            if($item->DataItem['action'] == 502)
            {
                $item->aaction->action->Text = Prado::localize("Sign in");
                $item->aactionr->actionr->Text =Prado::localize("Modified manually");
            }

            if($item->DataItem['action'] == 503)
            {
                $item->aaction->action->Text = Prado::localize("Sign out");
                $item->aactionr->actionr->Text = Prado::localize("Modified manually");
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

                    $cmd=$this->db->createCommand("DELETE FROM hr_timux_booking WHERE tracking_id =:id");
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
            $pBack = array('okMsg'=>Prado::localize('{n} booking was deleted',array('n'=>$nDelete)));

        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.booking',$pBack));
    }

    public function onEdit($sender,$param)
    {
        if(count($this->DataGrid->DataKeys) === 0)
        {
            $pBack = array('koMsg'=>Prado::localize('Select one item'));
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.booking',$pBack));

        }

        $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];
        if(is_numeric($id))
        {
            $pBack = array('id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.mod',$pBack));
        }

        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            {
                $pBack = array('id'=>$cb->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.mod',$pBack));
            }
        }

        $pBack = array('koMsg'=>Prado::localize('Select one item'));
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.booking',$pBack));
    }
}
?>
