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

class add extends Page
{
    protected $listBox = array();

    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->IsPostBack)
        {
            $this->departmentId->DataSource = $this->DepartmentList;
            $this->departmentId->dataBind();
            $this->departmentId->setSelectedValue(0);

            $this->validator1->DataTextField='name';
            $this->validator1->DataValueField='id';
            $this->validator1->DataSource=$this->DataPerson;
            $this->validator1->dataBind();
            $this->validator1->setSelectedValue(0);

            $this->validator11->DataTextField='name';
            $this->validator11->DataValueField='id';
            $this->validator11->DataSource=$this->DataPerson;
            $this->validator11->dataBind();
            $this->validator11->setSelectedValue(0);

            $this->validator12->DataTextField='name';
            $this->validator12->DataValueField='id';
            $this->validator12->DataSource=$this->DataPerson;
            $this->validator12->dataBind();
            $this->validator12->setSelectedValue(0);

            $this->validator2->DataTextField='name';
            $this->validator2->DataValueField='id';
            $this->validator2->DataSource=$this->DataPerson;
            $this->validator2->dataBind();
            $this->validator2->setSelectedValue(0);

            $this->validator21->DataTextField='name';
            $this->validator21->DataValueField='id';
            $this->validator21->DataSource=$this->DataPerson;
            $this->validator21->dataBind();
            $this->validator21->setSelectedValue(0);

            $this->validator22->DataTextField='name';
            $this->validator22->DataValueField='id';
            $this->validator22->DataSource=$this->DataPerson;
            $this->validator22->dataBind();
            $this->validator22->setSelectedValue(0);

            $this->validator3->DataTextField='name';
            $this->validator3->DataValueField='id';
            $this->validator3->DataSource=$this->DataPerson;
            $this->validator3->dataBind();
            $this->validator3->setSelectedValue(0);

            $this->validator31->DataTextField='name';
            $this->validator31->DataValueField='id';
            $this->validator31->DataSource=$this->DataPerson;
            $this->validator31->dataBind();
            $this->validator31->setSelectedValue(0);

            $this->validator32->DataTextField='name';
            $this->validator32->DataValueField='id';
            $this->validator32->DataSource=$this->DataPerson;
            $this->validator32->dataBind();
            $this->validator32->setSelectedValue(0);

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
            if($lastId = $this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The workflow was added successfully'), 'id'=>$lastId);
                $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.workflow.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The workflow was not added'));
                $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.workflow.add',$pBack));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The workflow was added successfully'));
            }
            else
            $pBack = array('koMsg'=>Prado::localize('The workflow was not added'));

            $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.workflow.workflow',$pBack));
        }
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.workflow.workflow'));
    }

    protected function saveData()
    {
        $cmd = $this->db->createCommand( "INSERT hr_timux_workflow SET
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

        if(!$cmd->execute()) return false;

        $lastId = $this->db->getLastInsertID();


        //$this->log("Add the department: ".$this->name->SafeText);

        return $lastId;
    }
}
