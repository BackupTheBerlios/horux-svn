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
            $cmd=$this->db->createCommand("SELECT t.date FROM hr_tracking AS t ORDER BY t.date LIMIT 0,1");
            $data = $cmd->query();
            $data = $data->readAll();

            $year = date("Y");
            if(count($data)>0)
            {
                $year = explode("-",$data[0]['date']);
                $year = $year[0];
            }
            $currentYear = date("Y");

            $yearList = array();
            for($i=$year; $i<= $currentYear;$i++ )
            {
                $yearList[] = array('Value'=>$i, 'Text'=>$i);
            }

            $this->FilterYear->DataSource=$yearList;
            $this->FilterYear->dataBind();

            if(Prado::getApplication()->getSession()->contains($this->getApplication()->getService()->getRequestedPagePath().'FilterYear'))
            {
                $FilterYear= $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterYear'];
                $FilterMonth = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterMonth'];
            }
            else
            {
                $FilterYear= date('Y');
                $FilterMonth = date('n');
            }

            $FilterEmployee = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterEmployee'];
            $FilterDepartment = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterDepartment'];
            $FilterStatus = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterStatus'];


            $this->FilterDepartment->DataSource=$this->DepartmentList;
            $this->FilterDepartment->dataBind();

            if($FilterEmployee)
                $this->FilterEmployee->Text = $FilterEmployee;
                
            if($FilterStatus)
                $this->FilterStatus->setSelectedValue($FilterStatus);
            else
                $this->FilterStatus->setSelectedValue('all');

            if($FilterYear)
                $this->FilterYear->setSelectedValue($FilterYear);
                
            if($FilterMonth)
                $this->FilterMonth->setSelectedValue($FilterMonth);

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
                $status = ' (tb.action=255  OR  tb.action=100 ) AND';
                break;
            case '0':
                $status = ' (tb.action=254  OR  tb.action=100 ) AND ';
                break;
            default:
                $status = '';

         }

        $from =  $this->FilterYear->getSelectedValue()."-".$this->FilterMonth->getSelectedValue()."-1";
        $day = date("t", mktime(0,0,0,(int)$this->FilterMonth->getSelectedValue(),1,(int)$this->FilterYear->getSelectedValue()));
        $until = $this->FilterYear->getSelectedValue()."-".$this->FilterMonth->getSelectedValue()."-".$day;

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

        $cmd=$this->db->createCommand("SELECT CONCAT(u.name, ' ' , u.firstname) AS employee, t.id, t.date, tb.roundBooking AS time, tb.action, tb.actionReason, d.name AS department, tb.internet FROM hr_tracking AS t LEFT JOIN hr_timux_booking AS tb ON tb.tracking_id=t.id LEFT JOIN hr_user AS u ON u.id=t.id_user LEFT JOIN hr_department AS d ON d.id=u.department WHERE $date $status $employee $department 1=1 AND tb.action!='NULL' ORDER BY t.date DESC, t.time DESC");

        $data = $cmd->query();
        $data = $data->readAll();

        $dataTmp = array();
        foreach($data as $d)
        {
            if($d['action'] == '255' || $d['action'] == '254')
            {
               $dataTmp[] = $d;
            }

            if($d['action'] == '100')
            {
                $ar = explode("_",$d['actionReason']);

                if(count($ar)>1)
                {
                    if($ar[1] == 'IN' && $this->FilterStatus->getSelectedValue() == 1)
                    {
                        $dataTmp[] = $d;
                    }
                    else
                    {
                        if($ar[1] == 'OUT' && $this->FilterStatus->getSelectedValue() == 0)
                        {
                            $dataTmp[] = $d;
                        }
                        else
                            if($this->FilterStatus->getSelectedValue() == 'all')
                                $dataTmp[] = $d;
                    }
                }
                else
                {
                    $cmd=$this->db->createCommand("SELECT *  FROM hr_timux_timecode WHERE id=".$ar[0]);

                    $data = $cmd->query();
                    $data = $data->read();

                    if($data['signtype'] == 'in' && $this->FilterStatus->getSelectedValue() == 1)
                    {
                        $dataTmp[] = $d;
                    }
                    else
                    {
                        if($data['signtype'] == 'out' && $this->FilterStatus->getSelectedValue() == 0)
                        {
                           $dataTmp[] = $d;
                        }
                        else
                            if($this->FilterStatus->getSelectedValue() == 'all')
                                $dataTmp[] = $d;
                    }
                    
                }
            }
        }

        return $dataTmp;
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

    public function selectionChangedYear($sender, $param)
    {
        $this->onRefresh($sender, $param);
    }

    public function selectionChangedMonth($sender, $param)
    {
        $this->onRefresh($sender, $param);
    }

    public function onRefresh($sender, $param)
    {
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterEmployee'] = $this->FilterEmployee->SafeText;
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterStatus'] = $this->FilterStatus->getSelectedValue();
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterYear'] = $this->FilterYear->getSelectedValue();
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterMonth'] =  $this->FilterMonth->getSelectedValue();
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterDepartment'] =  $this->FilterDepartment->getSelectedValue();

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }

    public function itemCreated($sender, $param)
    {
        $item=$param->Item;

        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' )
        {

            $signInText =  Prado::localize("Sign in");
            $signOutText =  Prado::localize("Sign out");

            if($item->DataItem['internet'])
            {
                $signInText = "* ".$signInText;
                $signOutText = "* ".$signOutText;
                $item->aaction->action->ForeColor = "green";
            }

            $item->ddate->date->Text = $this->dateFromSql($item->DataItem['date']);

            if($item->DataItem['action'] == 255)
                $item->aaction->action->Text = $signInText;
            if($item->DataItem['action'] == 254)
                $item->aaction->action->Text = $signOutText;

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
                            $item->aaction->action->Text = $signInText;
                        if($actionReason[1] == "OUT")
                            $item->aaction->action->Text = $signOutText;
                    }
                    else
                    {
                        if($data['signtype'] == "in")
                            $item->aaction->action->Text = $signInText;
                        if($data['signtype'] == "out")
                            $item->aaction->action->Text = $signOutText;

                    }


                    $item->aactionr->actionr->Text = $data['name'];
                }
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

                    $cmd=$this->db->createCommand("SELECT * FROM hr_timux_booking WHERE tracking_id =:id");
                    $cmd->bindParameter(":id",$cb->Value);
                    $query = $cmd->query();
                    $data = $query->read();

                    if($data['closed'] == '0')
                    {
                        $cmd=$this->db->createCommand("DELETE FROM hr_tracking WHERE id =:id");
                        $cmd->bindParameter(":id",$cb->Value);
                        $cmd->execute();


                        $cmd=$this->db->createCommand("DELETE FROM hr_timux_booking WHERE tracking_id =:id");
                        $cmd->bindParameter(":id",$cb->Value);
                        if($cmd->execute())
                        {
                            $nDelete++;
                        }
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
