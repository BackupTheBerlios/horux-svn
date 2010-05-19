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

Prado::using('horux.pages.hardware.device.gantner_AccessTerminal.sql');

class add extends Page
{
    protected $lastId;
    public function onLoad($param)
    {
        parent::onLoad($param);
        
        if(!$this->IsPostBack)
        {
            $param = $this->Application->getParameters();
            $superAdmin = $this->Application->getUser()->getSuperAdmin();

            if($param['appMode'] == 'demo' && $superAdmin == 0)
            {
                  $this->tbb->Save->setEnabled(false);
                  $this->tbb->apply->setEnabled(false);
            }           
        }
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
          $sa->addStandalone("add", $this->lastId, 'gantnerAccessReinit');

          $pBack = array('okMsg'=>Prado::localize('The device was added successfully'), 'id'=>$id);
          $this->Response->redirect($this->Service->constructUrl('hardware.device.gantner_AccessTerminal.mod', $pBack));
        }
        else
        {
              $pBack = array('koMsg'=>Prado::localize('The device was not added'));
              $this->Response->redirect($this->Service->constructUrl('hardware.device.gantner_AccessTerminal.add',$pBack));
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
            $sa->addStandalone("add", $this->lastId, 'gantnerAccessReinit');

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
	  $cmd->bindParameter(":checkBooking",$this->checkBooking->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":userMemory",$this->userMemory->getSelectedValue(),PDO::PARAM_STR);
	  $cmd->bindParameter(":accessMemory",$this->accessMemory->getSelectedValue(),PDO::PARAM_STR);
	  $cmd->bindParameter(":subscriberNumber",$this->subscriberNumber->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":plantNumber",$this->plantNumber->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":mainCompIdCard",$this->mainCompIdCard->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":bookingCodeSumWinSwitchOver",$this->bookingCodeSumWinSwitchOver->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":switchOverLeap",$this->switchOverLeap->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":waitingTimeInput",$this->waitingTimeInput->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":monitoringTime",$this->monitoringTime->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":monitorinChangingTime",$this->monitorinChangingTime->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":cardReaderType",$this->cardReaderType->getSelectedValue(),PDO::PARAM_STR);
	  $cmd->bindParameter(":maxDoorOpenTime",$this->maxDoorOpenTime->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":warningTimeDoorOpenTime",$this->warningTimeDoorOpenTime->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":unlockingTime",$this->unlockingTime->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":relay1",$this->relay1->getSelectedValue(),PDO::PARAM_STR);
	  $cmd->bindParameter(":timeRelay1",$this->timeRelay1->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":relay2",$this->relay2->getSelectedValue(),PDO::PARAM_STR);
	  $cmd->bindParameter(":timeRelay2",$this->timeRelay2->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":relay3",$this->relay3->getSelectedValue(),PDO::PARAM_STR);
	  $cmd->bindParameter(":timeRelay3",$this->timeRelay3->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":relay4",$this->relay3->getSelectedValue(),PDO::PARAM_STR);
	  $cmd->bindParameter(":timeRelay4",$this->timeRelay3->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":opto1",$this->opto1->getSelectedValue(),PDO::PARAM_STR);
	  $cmd->bindParameter(":opto2",$this->opto2->getSelectedValue(),PDO::PARAM_STR);
	  $cmd->bindParameter(":opto3",$this->opto3->getSelectedValue(),PDO::PARAM_STR);
	  $cmd->bindParameter(":opto4",$this->opto4->getSelectedValue(),PDO::PARAM_STR);
	  $cmd->bindParameter(":enterExitInfo",$this->enterExitInfo->getSelectedValue(),PDO::PARAM_STR);

      $f1 = $this->autoUnlocking->getChecked() ? 1 : 0;
	  $cmd->bindParameter(":autoUnlocking",$f1,PDO::PARAM_STR);

      $f2 = $this->lockUnlockCommand->getChecked() ? 1 : 0;
	  $cmd->bindParameter(":lockUnlockCommand",$f2,PDO::PARAM_STR);

	  $cmd->bindParameter(":holdUpPINCode",$this->holdUpPINCode->SafeText,PDO::PARAM_STR);

      $f3 = $this->twoPersonAccess->getChecked() ? 1 : 0;
	  $cmd->bindParameter(":twoPersonAccess",$f3,PDO::PARAM_STR);

	  $cmd->bindParameter(":barriereRepeatedAccess",$this->barriereRepeatedAccess->SafeText,PDO::PARAM_STR);

      $f4 = $this->antiPassActive->getChecked() ? 1 : 0;
	  $cmd->bindParameter(":antiPassActive",$f4,PDO::PARAM_STR);

	  $cmd->bindParameter(":relayExpanderControl",$this->relayExpanderControl->getSelectedValue(),PDO::PARAM_STR);
	  $cmd->bindParameter(":doorOpenTimeUnit",$this->doorOpenTimeUnit->getSelectedValue(),PDO::PARAM_STR);


	  $cmd->bindParameter(":optionalCompanyID1",$this->optionalCompanyID1->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":optionalCompanyID2",$this->optionalCompanyID2->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":optionalCompanyID3",$this->optionalCompanyID3->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":optionalCompanyID4",$this->optionalCompanyID4->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":optionalCompanyID5",$this->optionalCompanyID5->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":optionalCompanyID6",$this->optionalCompanyID6->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":optionalCompanyID7",$this->optionalCompanyID7->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":optionalCompanyID8",$this->optionalCompanyID8->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":optionalCompanyID9",$this->optionalCompanyID9->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":optionalCompanyID10",$this->optionalCompanyID10->SafeText,PDO::PARAM_STR);

	  $cmd->bindParameter(":optionalCardStructur",$this->optionalCardStructur->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":optionalGantnerNationalCode",$this->optionalGantnerNationalCode->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":optionalGantnerCustomerCode1",$this->optionalGantnerCustomerCode1->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":optionalGantnerCustomerCode2",$this->optionalGantnerCustomerCode2->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":optionalGantnerCustomerCode3",$this->optionalGantnerCustomerCode3->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":optionalGantnerCustomerCode4",$this->optionalGantnerCustomerCode4->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":optionalGantnerCustomerCode5",$this->optionalGantnerCustomerCode5->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":optionalReaderInitialisation",$this->optionalReaderInitialisation->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":optionalTableCardType",$this->optionalTableCardType->SafeText,PDO::PARAM_STR);


      $cmd->Execute();

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
