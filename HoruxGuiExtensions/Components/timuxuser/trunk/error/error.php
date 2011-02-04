<?php


$param = Prado::getApplication()->getParameters();
$computation = $param['computation'];

Prado::using('horux.pages.components.timuxuser.'.$computation);

class error extends PageList
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

            $FilterEmployee = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterEmployee'];
            $FilterDepartment = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterDepartment'];

            if($FilterEmployee)
                $this->employee = new employee($FilterEmployee );

            $this->FilterDepartment->DataSource=$this->DepartmentList;
            $this->FilterDepartment->dataBind();

            if($FilterDepartment)
                $this->FilterDepartment->setSelectedValue($FilterDepartment);
            else
                $this->FilterDepartment->setSelectedIndex(0);

            $this->FilterEmployee->DataSource=$this->EmployeeList;
            $this->FilterEmployee->dataBind();


            if($FilterEmployee)
                $this->FilterEmployee->setSelectedValue($FilterEmployee);
            else
                $this->FilterEmployee->setSelectedValue($this->userId);
                
            if($FilterYear)
                $this->FilterYear->setSelectedValue($FilterYear);

            if($FilterMonth)
                $this->FilterMonth->setSelectedValue($FilterMonth);

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

    public function getEmployeeList()
    {
        $employee = new employee($this->userId);

        $role = $employee->getRole();

        $id = '';
        if($role == 'employee')
        {
            $id = 'id='.$this->userId.' AND ';
        }


        $department = $this->FilterDepartment->getSelectedValue();

        if($department>0)
            $cmd = $this->db->createCommand( "SELECT CONCAT(name, ' ', firstname) AS Text, id AS Value FROM hr_user WHERE $id department=$department AND name!='??' ORDER BY name, firstname");
        else
            $cmd = $this->db->createCommand( "SELECT CONCAT(name, ' ', firstname) AS Text, id AS Value FROM hr_user WHERE $id name!='??' ORDER BY name, firstname");

        $data = $cmd->query();
        $data = $data->readAll();

        if($role == 'rh' || $role == 'manager') {
            $dataAll[] = array("Value"=>0, "Text"=>Prado::localize("--- All ---"));

            $data = array_merge($dataAll, $data);
        }


        return $data;

    }

    public function getDepartmentList()
    {
        $employee = new employee($this->userId);

        $role = $employee->getRole();
        $department = $employee->getDepartmentId();
        $cmd = NULL;
        if($role == 'manager' || $role == 'employee')
            $cmd = $this->db->createCommand( "SELECT name AS Text, id AS Value FROM hr_department WHERE id=$department");
        else
            $cmd = $this->db->createCommand( "SELECT name AS Text, id AS Value FROM hr_department ORDER BY name");
        $data = $cmd->query();
        $data = $data->readAll();

        if($role == 'rh')
        {
            $dataAll[] = array("Value"=>0, "Text"=>Prado::localize("--- All ---"));

            $data = array_merge($dataAll, $data);
        }

        return $data;

    }

    public function getData()
    {
        if($this->FilterEmployee->getSelectedValue() == 0) {

            $items = $this->FilterEmployee->getItems();

            $data = array();

            for($i=0; $i< $items->count(); $i++) {
                $item = $items->itemAt ($i);

                $employee = new employee($item->Value);
                $errors = $employee->getError($this->FilterYear->getSelectedValue(),$this->FilterMonth->getSelectedValue());

                $data = array_merge($data, $errors);
            }

            return $data;

        } else {

            $this->employee = new employee($this->FilterEmployee->getSelectedValue() );

            return $this->employee->getError($this->FilterYear->getSelectedValue(),$this->FilterMonth->getSelectedValue());
        }
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
            $item->ddate->date->Value = $item->DataItem['date'];
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

    public function selectionChangedEmployee($sender, $param)
    {
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterEmployee'] = $this->FilterEmployee->getSelectedValue();
        $this->employee = new employee($this->FilterEmployee->getSelectedValue() );

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);

    }

    public function selectionChangedDepartment($sender, $param)
    {
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterDepartment'] = $this->FilterDepartment->getSelectedValue();


        $this->FilterEmployee->DataSource=$this->EmployeeList;
        $this->FilterEmployee->dataBind();

        if(count($this->EmployeeList)>0)
            $this->FilterEmployee->setSelectedIndex(0);

        $this->employee = new employee($this->FilterEmployee->getSelectedValue() );

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }


    public function onRefresh($sender, $param)
    {
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterYear'] = $this->FilterYear->getSelectedValue();
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterMonth'] = $this->FilterMonth->getSelectedValue();

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }



}
?>
