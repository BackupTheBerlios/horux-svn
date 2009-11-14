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

class mod extends Page
{
    protected $listBox = array();

    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->IsPostBack)
        {
            $this->departmentId->DataSource = $this->DepartmentList;
            $this->departmentId->dataBind();

            $this->validator1->DataTextField='name';
            $this->validator1->DataValueField='id';
            $this->validator1->DataSource=$this->DataPerson;
            $this->validator1->dataBind();

            $this->validator11->DataTextField='name';
            $this->validator11->DataValueField='id';
            $this->validator11->DataSource=$this->DataPerson;
            $this->validator11->dataBind();

            $this->validator12->DataTextField='name';
            $this->validator12->DataValueField='id';
            $this->validator12->DataSource=$this->DataPerson;
            $this->validator12->dataBind();

            $this->validator2->DataTextField='name';
            $this->validator2->DataValueField='id';
            $this->validator2->DataSource=$this->DataPerson;
            $this->validator2->dataBind();

            $this->validator21->DataTextField='name';
            $this->validator21->DataValueField='id';
            $this->validator21->DataSource=$this->DataPerson;
            $this->validator21->dataBind();

            $this->validator22->DataTextField='name';
            $this->validator22->DataValueField='id';
            $this->validator22->DataSource=$this->DataPerson;
            $this->validator22->dataBind();

            $this->validator3->DataTextField='name';
            $this->validator3->DataValueField='id';
            $this->validator3->DataSource=$this->DataPerson;
            $this->validator3->dataBind();

            $this->validator31->DataTextField='name';
            $this->validator31->DataValueField='id';
            $this->validator31->DataSource=$this->DataPerson;
            $this->validator31->dataBind();

            $this->validator32->DataTextField='name';
            $this->validator32->DataValueField='id';
            $this->validator32->DataSource=$this->DataPerson;
            $this->validator32->dataBind();


            $this->id->Value = $this->Request['id'];
            $this->setData();

        }

    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_workflow WHERE id=:id");
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();

        if($query)
        {
            $data = $query->read();
            $this->name->Text = $data['name'];
            $this->comment->Text = $data['description'];

            $this->type->setSelectedValue($data['type']);
            $this->departmentId->setSelectedValue($data['departmentId']);
            $this->validator1->setSelectedValue($data['validator1']);
            $this->validator11->setSelectedValue($data['validator11']);
            $this->validator12->setSelectedValue($data['validator12']);
            $this->validator2->setSelectedValue($data['validator2']);
            $this->validator21->setSelectedValue($data['validator21']);
            $this->validator22->setSelectedValue($data['validator22']);
            $this->validator3->setSelectedValue($data['validator3']);
            $this->validator31->setSelectedValue($data['validator31']);
            $this->validator32->setSelectedValue($data['validator32']);
        }
    }

    protected function getDepartmentList()
    {
       $cmd = $this->db->createCommand( "SELECT name, id AS value FROM hr_department ORDER BY name");
       $data =  $cmd->query();
       $data = $data->readAll();
       $d[0]['value'] = '0';
       $d[0]['name'] = Prado::localize('---- Apply to all ----');
       $data = array_merge($d, $data);
       return $data;
    }

    protected function getDataPerson()
    {
        $cmd=$this->db->createCommand("SELECT id, CONCAT(name, ' ', firstname) AS name FROM hr_user WHERE name<>'??' ORDER BY name, firstname");

        $data=$cmd->query();

        $data = $data->readAll();
        $data1[] = array('id'=>0, 'name'=>'--- None ---');

        $data = array_merge($data1, $data);


        return $data;
    }


    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The workflow was modified successfully'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.workflow.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The workflow was not modified'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.workflow.mod',$pBack));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The workflow was modified successfully'));
            }
            else
            $pBack = array('koMsg'=>Prado::localize('The workflow was not modified'));

            $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.workflow.workflow',$pBack));
        }
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.workflow.workflow'));
    }

    protected function saveData()
    {
        $cmd = $this->db->createCommand( "UPDATE hr_timux_workflow SET
                                            name=:name,
                                            description=:description,
                                            type=:type,
                                            departmentId=:departmentId,
                                            validator1=:validator1,
                                            validator11=:validator11,
                                            validator12=:validator12,
                                            validator2=:validator2,
                                            validator21=:validator21,
                                            validator22=:validator22,
                                            validator3=:validator3,
                                            validator31=:validator31,
                                            validator32=:validator32
                                           WHERE id=:id
                                          " );

        $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":description",$this->comment->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":type",$this->type->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindParameter(":departmentId",$this->departmentId->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindParameter(":validator1",$this->validator1->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindParameter(":validator11",$this->validator11->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindParameter(":validator12",$this->validator12->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindParameter(":validator2",$this->validator2->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindParameter(":validator21",$this->validator21->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindParameter(":validator22",$this->validator22->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindParameter(":validator3",$this->validator3->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindParameter(":validator31",$this->validator31->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindParameter(":validator32",$this->validator32->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);

        if(!$cmd->execute()) return false;


        //$this->log("Add the department: ".$this->name->SafeText);

        return true;
    }
}
