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
            $this->id->Value = $this->Request['id'];
            $this->setData();
        }

      $param = $this->Application->getParameters();
      $superAdmin = $this->Application->getUser()->getSuperAdmin();

      if($param['appMode'] == 'demo' && $superAdmin == 0)
      {
              $this->tbb->Save->setEnabled(false);
              $this->tbb->apply->setEnabled(false);
      }

    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_department WHERE id=:id" );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            $this->id->Value = $data['id'];
            $this->name->Text = $data['name'];
            $this->comment->Text = $data['description'];
        }
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The department was modified successfully'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('site.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The department was not modified'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('site.mod',$pBack));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The department was added successfully'));
            }
            else
            $pBack = array('koMsg'=>Prado::localize('The department was not added'));

            $this->Response->redirect($this->Service->constructUrl('site.department',$pBack));
        }
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('site.department'));
    }

    protected function saveData()
    {
        $cmd = $this->db->createCommand( "UPDATE hr_department SET name=:name, description=:description WHERE id=:id" );
        $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":description",$this->comment->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);

        if(!$cmd->execute()) return false;


        $this->log("Modifed the department: ".$this->name->SafeText);

        return true;
    }
}
