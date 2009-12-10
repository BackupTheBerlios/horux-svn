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

Prado::using('horux.pages.hardware.device.gantner_TimeTerminal.sql');

class add extends Page
{
    protected $lastId;
    public function onLoad($param)
    {
        parent::onLoad($param);
        
        if(!$this->IsPostBack)
        {
            $this->brightness->setDataValueField('value');
            $this->brightness->setDataTextField('text');
            $this->brightness->DataSource=$this->Brightness;
            $this->brightness->dataBind();
            $this->brightness->setSelectedValue(50);


            $param = $this->Application->getParameters();
            $superAdmin = $this->Application->getUser()->getSuperAdmin();

            if($param['appMode'] == 'demo' && $superAdmin == 0)
            {
                    $this->Save->setEnabled(false);
                    $this->Apply->setEnabled(false);
            }           
        }
    }

    protected function getBrightness()
    {
        $v = array();
        for($i=0; $i<=100; $i++)
        {
            $v[] = array('value'=>$i, 'text'=>$i);
        }

        return $v;
    }

    public function onApply($sender, $param)
    {
      if($this->Page->IsValid)
      {
        if($this->saveData())
        {
          $id = $this->lastId;

          $horuxService = new THoruxService();
          $horuxService->onStopDevice($id);
          $horuxService->onStartDevice($id);
          $sa = new TStandAlone();
          $sa->addStandalone("add", $this->lastId, 'timuxReinit');

          $pBack = array('okMsg'=>Prado::localize('The device was added successfully'), 'id'=>$id);
          $this->Response->redirect($this->Service->constructUrl('hardware.device.gantner_TimeTerminal.mod', $pBack));
        }
        else
        {
              $pBack = array('koMsg'=>Prado::localize('The device was not added'));
              $this->Response->redirect($this->Service->constructUrl('hardware.device.gantner_TimeTerminal.add',$pBack));
        }
      }		
    }

	public function onSave($sender, $param)
	{
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The device was added successfully'));
            $horuxService = new THoruxService();
            $horuxService->onStopDevice($this->lastId);
            $horuxService->onStartDevice($this->lastId);

            $sa = new TStandAlone();
            $sa->addStandalone("add", $this->lastId, 'timuxReinit');

          }
          else
          {
           	$pBack = array('koMsg'=>Prado::localize('The device was not saved'));
          }
          $this->Response->redirect($this->Service->constructUrl('hardware.HardwareList',$pBack));
        }		
	}

	public function saveData()
	{
      $cmd = $this->db->createCommand( SQL::SQL_ADD_DEVICE );
	  $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":description",$this->comment->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":isLog",$this->isLog->getChecked(),PDO::PARAM_STR);
	  $cmd->Execute();

	  $this->lastId = $this->db->getLastInsertID();


      $cmd = $this->db->createCommand( SQL::SQL_ADD_GANTNERTERMINAL );

      $cmd->bindParameter(":id_device",$this->lastId,PDO::PARAM_STR);
	  $cmd->bindParameter(":ipOrDhcp",$this->ipOrDhcp->SafeText,PDO::PARAM_STR);

      $isAutoRestart = $this->isAutoRestart->getChecked();
	  $cmd->bindParameter(":isAutoRestart",$isAutoRestart,PDO::PARAM_STR);

      $autoRestart = $this->autoRestartHour->SafeText.":".$this->autoRestartMinute->SafeText.":00";
      $cmd->bindParameter(":autoRestart",$autoRestart,PDO::PARAM_STR);

	  $cmd->bindParameter(":displayTimeout",$this->displayTimeout->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":inputTimeout",$this->inputTimeout->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":brightness",$this->brightness->getSelectedValue(),PDO::PARAM_STR);

      $udpServer = $this->udpServer->getChecked();
	  $cmd->bindParameter(":udpServer",$udpServer,PDO::PARAM_STR);

	  $cmd->bindParameter(":checkBooking",$this->checkBooking->SafeText,PDO::PARAM_STR);

      $language = array();

      if($this->ar->getChecked())
        $language[] = 'ar';
      if($this->de->getChecked())
          $language[] = 'de';
      if($this->en->getChecked())
          $language[] = 'en';
      if($this->fr->getChecked())
          $language[] =  'fr';
      if($this->it->getChecked())
          $language[] = 'it';
      if($this->fa->getChecked())
          $language[] = 'fa';
      if($this->pl->getChecked())
          $language[] = 'pl';
      if($this->ro->getChecked())
          $language[] = 'ro';
      if($this->es->getChecked())
          $language[] = 'es';
      if($this->cs->getChecked())
          $language[] = 'cs';

      $language = implode(',' ,$language);

	  $cmd->bindParameter(":language",$language,PDO::PARAM_STR);


	  $cmd->Execute();


      $type ='fixed';
      $key = 1;
      if($this->leftFixed->SafeText != '' && $this->leftFixedDlg->getSelectedValue() != '')
      {
          $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
          $cmd->bindParameter(":id",$this->lastId,PDO::PARAM_STR);
          $cmd->bindParameter(":type",$type,PDO::PARAM_STR);
          $cmd->bindParameter(":key",$key,PDO::PARAM_STR);
          $cmd->bindParameter(":text",$this->leftFixed->SafeText,PDO::PARAM_STR);
          $cmd->bindParameter(":dialog",$this->leftFixedDlg->getSelectedValue(),PDO::PARAM_STR);
          $cmd->Execute();
      }
      $key = 2;
      if($this->leftMiddleFixed->SafeText != '' && $this->leftMiddleFixedDlg->getSelectedValue() != '')
      {
          $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
          $cmd->bindParameter(":id",$this->lastId,PDO::PARAM_STR);
          $cmd->bindParameter(":type",$type,PDO::PARAM_STR);
          $cmd->bindParameter(":key",$key,PDO::PARAM_STR);
          $cmd->bindParameter(":text",$this->leftMiddleFixed->SafeText,PDO::PARAM_STR);
          $cmd->bindParameter(":dialog",$this->leftMiddleFixedDlg->getSelectedValue(),PDO::PARAM_STR);
          $cmd->Execute();
      }
      $key = 3;
      if($this->rightMiddleFixed->SafeText != '' && $this->rightMiddleFixedDlg->getSelectedValue() != '')
      {
          $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
          $cmd->bindParameter(":id",$this->lastId,PDO::PARAM_STR);
          $cmd->bindParameter(":type",$type,PDO::PARAM_STR);
          $cmd->bindParameter(":key",$key,PDO::PARAM_STR);
          $cmd->bindParameter(":text",$this->rightMiddleFixed->SafeText,PDO::PARAM_STR);
          $cmd->bindParameter(":dialog",$this->rightMiddleFixedDlg->getSelectedValue(),PDO::PARAM_STR);
          $cmd->Execute();

      }
      $key = 4;
      if($this->rightFixed->SafeText != '' && $this->rightFixedDlg->getSelectedValue() != '')
      {
          $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
          $cmd->bindParameter(":id",$this->lastId,PDO::PARAM_STR);
          $cmd->bindParameter(":type",$type,PDO::PARAM_STR);
          $cmd->bindParameter(":key",$key,PDO::PARAM_STR);
          $cmd->bindParameter(":text",$this->rightFixed->SafeText,PDO::PARAM_STR);
          $cmd->bindParameter(":dialog",$this->rightFixedDlg->getSelectedValue(),PDO::PARAM_STR);
          $cmd->Execute();
      }


      $type ='soft';
      $key = 1;
      if($this->leftTopSoft->SafeText != '' && $this->leftTopSoftDlg->getSelectedValue() != '')
      {
          $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
          $cmd->bindParameter(":id",$this->lastId,PDO::PARAM_STR);
          $cmd->bindParameter(":type",$type,PDO::PARAM_STR);
          $cmd->bindParameter(":key",$key,PDO::PARAM_STR);
          $cmd->bindParameter(":text",$this->leftTopSoft->SafeText,PDO::PARAM_STR);
          $cmd->bindParameter(":dialog",$this->leftTopSoftDlg->getSelectedValue(),PDO::PARAM_STR);
          $cmd->Execute();

      }
      $key = 2;
      if($this->leftMiddleSoft->SafeText != '' && $this->leftMiddleSoftDlg->getSelectedValue() != '')
      {
          $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
          $cmd->bindParameter(":id",$this->lastId,PDO::PARAM_STR);
          $cmd->bindParameter(":type",$type,PDO::PARAM_STR);
          $cmd->bindParameter(":key",$key,PDO::PARAM_STR);
          $cmd->bindParameter(":text",$this->leftMiddleSoft->SafeText,PDO::PARAM_STR);
          $cmd->bindParameter(":dialog",$this->leftMiddleSoftDlg->getSelectedValue(),PDO::PARAM_STR);
          $cmd->Execute();

      }
      $key = 3;
      if($this->leftBottomSoft->SafeText != '' && $this->leftBottomSoftDlg->getSelectedValue() != '')
      {
          $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
          $cmd->bindParameter(":id",$this->lastId,PDO::PARAM_STR);
          $cmd->bindParameter(":type",$type,PDO::PARAM_STR);
          $cmd->bindParameter(":key",$key,PDO::PARAM_STR);
          $cmd->bindParameter(":text",$this->leftBottomSoft->SafeText,PDO::PARAM_STR);
          $cmd->bindParameter(":dialog",$this->leftBottomSoftDlg->getSelectedValue(),PDO::PARAM_STR);
          $cmd->Execute();

      }
      $key = 4;
      if($this->rightTopSoft->SafeText != '' && $this->rightTopSoftDlg->getSelectedValue() != '')
      {
          $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
          $cmd->bindParameter(":id",$this->lastId,PDO::PARAM_STR);
          $cmd->bindParameter(":type",$type,PDO::PARAM_STR);
          $cmd->bindParameter(":key",$key,PDO::PARAM_STR);
          $cmd->bindParameter(":text",$this->rightTopSoft->SafeText,PDO::PARAM_STR);
          $cmd->bindParameter(":dialog",$this->rightTopSoftDlg->getSelectedValue(),PDO::PARAM_STR);
          $cmd->Execute();

      }
      $key = 5;
      if($this->rightMiddleSoft->SafeText != '' && $this->rightMiddleSoftDlg->getSelectedValue() != '')
      {
          $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
          $cmd->bindParameter(":id",$this->lastId,PDO::PARAM_STR);
          $cmd->bindParameter(":type",$type,PDO::PARAM_STR);
          $cmd->bindParameter(":key",$key,PDO::PARAM_STR);
          $cmd->bindParameter(":text",$this->rightMiddleSoft->SafeText,PDO::PARAM_STR);
          $cmd->bindParameter(":dialog",$this->rightMiddleSoftDlg->getSelectedValue(),PDO::PARAM_STR);
          $cmd->Execute();

      }
      $key = 6;
      if($this->rightBottomSoft->SafeText != '' && $this->rightBottomSoftDlg->getSelectedValue() != '')
      {
          $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
          $cmd->bindParameter(":id",$this->lastId,PDO::PARAM_STR);
          $cmd->bindParameter(":type",$type,PDO::PARAM_STR);
          $cmd->bindParameter(":key",$key,PDO::PARAM_STR);
          $cmd->bindParameter(":text",$this->rightBottomSoft->SafeText,PDO::PARAM_STR);
          $cmd->bindParameter(":dialog",$this->rightBottomSoftDlg->getSelectedValue(),PDO::PARAM_STR);
          $cmd->Execute();

      }

      return true;
	}     
	
    public function serverValidateName($sender, $param)
    {
      $cmd = $this->db->createCommand( SQL::SQL_IS_NAME_EXIST);
      $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
      $array = $cmd->query()->readAll();

      if(count($array) > 0)
        $param->IsValid=false;
      else 
        $param->IsValid=true;
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('hardware.HardwareList'));
    }



}
