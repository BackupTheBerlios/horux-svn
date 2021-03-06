<?php


$param = Prado::getApplication()->getParameters();
$computation = $param['computation'];


Prado::using('horux.pages.components.timuxuser.'.$computation);


class closemonth extends PageList
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
            $cmd=$this->db->createCommand("SELECT t.startDate FROM hr_timux_workingtime AS t ORDER BY t.startDate LIMIT 0,1");
            $data = $cmd->query();
            $data = $data->readAll();

            $year = date("Y");
            if(count($data)>0)
            {
                $year = explode("-",$data[0]['startDate']);
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

            $FilterDepartment = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterDepartment'];

            if($FilterMonth == 0)
            {
                $FilterMonth = 12;
                $FilterYear -= 1;

            }

            $this->FilterDepartment->DataSource=$this->DepartmentList;
            $this->FilterDepartment->dataBind();

                
            if($FilterYear)
                $this->FilterYear->setSelectedValue($FilterYear);
            else
                $this->FilterYear->setSelectedIndex(0);


            if($FilterMonth)
            {
                $this->FilterMonth->setSelectedValue($FilterMonth);
            }

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
        $department = $this->FilterDepartment->getSelectedValue();

        if($department>0)
            $department = " AND u.department=".$department;
        else
            $department = "";

        $year = $this->FilterYear->getSelectedValue();
        $month = $this->FilterMonth->getSelectedValue();

        $date = $year."-".$month."-".date('t', mktime(0,0,0,$month,1,$year));

        $cmd=$this->db->createCommand("SELECT w.user_id FROM hr_timux_workingtime AS w LEFT JOIN hr_user AS u ON u.id=w.user_id WHERE w.endOfActivity=0 AND w.startDate<='$date' $department GROUP BY w.user_id ORDER BY u.name,u.firstname");

        $data = $cmd->query();
        $data = $data->readAll();

        $result = array();

        foreach($data as $d)
        {
            $employee = new employee( $d['user_id'] );

            $count = 0;
            if($this->FilterYear->getSelectedValue() >= date('Y') && $this->FilterMonth->getSelectedValue() >= date('n') )
            {
                $count = -1;
            }
            else
            {
                $cmd=$this->db->createCommand("SELECT* FROM hr_timux_activity_counter WHERE user_id=".$d['user_id']." AND year=$year AND month=$month AND isClosedMonth=1");
                $data = $cmd->query();
                $data = $data->readAll();

                // if we have data, this means that the month is already closed
                if(count($data)>0) {
                    $count = -2;
                } else {
                    //check if the last month is closed
                    $m = $month;
                    $y = $year;

                    // check if the last month is closed
                    if($m == 1)
                    {
                        $m = 12;
                        $y--;
                    }
                    else
                    {
                        $m--;
                    }

                    $cmd=$this->db->createCommand("SELECT* FROM hr_timux_activity_counter WHERE user_id=".$d['user_id']." AND year=$y AND month=$m AND isClosedMonth=1");
                    $data = $cmd->query();
                    $data = $data->readAll();

                    if(count($data)>0) {
                        $isError = $employee->getError( $this->FilterYear->getSelectedValue(),$this->FilterMonth->getSelectedValue());
                        $count = count($isError);
                    } else {
                        $count = -3;
                    }
                }                
            }

            if($count != -4) {
                $result[] = array(
                                'user_id' => $d['user_id'],
                                'employee'=> $employee->getFullName(),
                                'canBeClosed' => $count
                               );
            }
        }

        return $result;
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.panel'));
    }

    public function itemCreated($sender, $param)
    {
        $item=$param->Item;

        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' )
        {
           if($item->DataItem['canBeClosed']>0) {
            $item->ccb->item->setDisplay('None');
            $item->cclose->close->Text = Prado::localize("No ( number of error {n} )", array('n'=>$item->DataItem['canBeClosed']));
           } elseif($item->DataItem['canBeClosed']==-1) {
            $item->ccb->item->setDisplay('None');
            $item->cclose->close->Text = Prado::localize("Cannot close the current and the next months");
           } elseif($item->DataItem['canBeClosed']==-2) {
            $item->ccb->item->setDisplay('None');
            $item->cclose->close->Text = Prado::localize("This month is already closed");
           } elseif($item->DataItem['canBeClosed']==-3) {
            $item->ccb->item->setDisplay('None');
            $item->cclose->close->Text = Prado::localize("This last month must be closed");
           } else {
            $item->cclose->close->Text = Prado::localize("Yes");
           }

           $item->mmonth->month->Text = $this->FilterMonth->getSelectedItem()->getText();
        }
    }

    public function selectionChangedYear($sender, $param)
    {
        $this->onRefresh($sender, $param);
    }

    public function selectionChangedMonth($sender, $param)
    {
        $this->onRefresh($sender, $param);
    }

    public function selectionChangedDepartment($sender, $param)
    {
        $this->onRefresh($sender, $param);
    }


    public function onRefresh($sender, $param)
    {
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterYear'] = $this->FilterYear->getSelectedValue();
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterMonth'] = $this->FilterMonth->getSelectedValue();
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterDepartment'] = $this->FilterDepartment->getSelectedValue();


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
            if($cb->getDisplay() == 'Dynamic')
                $cb->setChecked($isChecked);
            else
                $cb->setChecked(false);
        }

    }

    public function onCloseMonth($sender, $param)
    {
        if(count($this->DataGrid->DataKeys) === 0)
        {
            $pBack = array('koMsg'=>Prado::localize('Select one item'));
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.closemonth.closemonth',$pBack));

        }

        $cbs = $this->findControlsByType("TActiveCheckBox");
        if(count($cbs))
        {
            $nDelete = 0;
            foreach($cbs as $cb)
            {
                if( (bool)$cb->getChecked() && $cb->Value != "0")
                {
                    $employee = new employee( $cb->Value );

                    $employee->closeMonth($this->FilterMonth->getSelectedValue(),$this->FilterYear->getSelectedValue());

                    $param = Prado::getApplication()->getParameters();
                    $computation2 = $param['computation2'];

                    if($computation2 != '') {
                        Prado::using('horux.pages.components.timuxuser.'.$computation2);

                        if(class_exists($computation2)) {
                            $extendCloseMonth = new $computation2();

                            if($extendCloseMonth)
                                $extendCloseMonth->closeMonth($this->FilterMonth->getSelectedValue(),$this->FilterYear->getSelectedValue(), $employee);
                        }
                    }

                }
            }

            $pBack = array('okMsg'=>Prado::localize('The month was closed'));
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.closemonth.closemonth',$pBack));

        }
        else
        {
            $pBack = array('koMsg'=>Prado::localize('Select one item'));
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.closemonth.closemonth',$pBack));
        }
    }


     public function onRestoreMonth($sender, $param)  {

        $data = $this->getData();

        foreach($data as $d)
        {
            if($d['canBeClosed'] == -2) { // month already closed
                $employee = new employee( $d['user_id'] );

                $employee->restoreMonth($this->FilterMonth->getSelectedValue(),$this->FilterYear->getSelectedValue());

                $param = Prado::getApplication()->getParameters();
                $computation2 = $param['computation2'];

                if($computation2 != '') {
                    Prado::using('horux.pages.components.timuxuser.'.$computation2);

                    if(class_exists($computation2)) {
                        $extendCloseMonth = new $computation2();

                        if($extendCloseMonth)
                            $extendCloseMonth->restoreMonth($this->FilterMonth->getSelectedValue(),$this->FilterYear->getSelectedValue(), $employee);
                    }
                }
            }
        }

        $pBack = array('okMsg'=>Prado::localize('The month was restore'));
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.closemonth.closemonth',$pBack));

     }

}
?>
