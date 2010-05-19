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

class mod extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        
        if(!$this->isPostBack)
        {

          $userId=$this->Application->getUser()->getUserId();
    	  $this->blockRecord('hr_device', $this->Request['id'], $userId);                            
            
          $param = $this->Application->getParameters();
          $superAdmin = $this->Application->getUser()->getSuperAdmin();
          
          if($param['appMode'] == 'demo' && $superAdmin == 0)
          {
                  $this->tbb->Save->setEnabled(false);
                  $this->tbb->apply->setEnabled(false);
          }  

          $this->id->Value = $this->Request['id'];
          $this->setData();
          
        }        
    }


    protected function setData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_GET_GANTNERTERMINAL );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();

        if($query)
        {
          $data = $query->read();
          $this->name->Text = $data['name'];
          $this->comment->Text = $data['description'];
          $this->ipOrDhcp->Text = $data['ipOrDhcp'];
          $this->checkBooking->Text = $data['checkBooking'];

 
          $this->userMemory->setSelectedValue($data['userMemory']) ;
          $this->accessMemory->setSelectedValue($data['accessMemory']) ;
          $this->subscriberNumber->Text = $data['subscriberNumber'];
          $this->plantNumber->Text = $data['plantNumber'];
          $this->mainCompIdCard->Text = $data['mainCompIdCard'];
          $this->bookingCodeSumWinSwitchOver->Text = $data['bookingCodeSumWinSwitchOver'];
          $this->switchOverLeap->Text = $data['switchOverLeap'];
          $this->waitingTimeInput->Text = $data['waitingTimeInput'];
          $this->monitoringTime->Text = $data['monitoringTime'];
          $this->monitorinChangingTime->Text = $data['monitorinChangingTime'];
          $this->cardReaderType->setSelectedValue($data['cardReaderType']) ;
          $this->maxDoorOpenTime->Text = $data['maxDoorOpenTime'];
          $this->warningTimeDoorOpenTime->Text = $data['warningTimeDoorOpenTime'];
          $this->unlockingTime->Text = $data['unlockingTime'];
          $this->relay1->setSelectedValue($data['relay1']) ;
          $this->timeRelay1->Text = $data['timeRelay1'];
          $this->relay2->setSelectedValue($data['relay2']);
          $this->timeRelay2->Text = $data['timeRelay2'];
          $this->relay3->setSelectedValue($data['relay3']) ;
          $this->timeRelay3->Text = $data['timeRelay3'];
          $this->relay3->setSelectedValue($data['relay3']);
          $this->timeRelay3->Text = $data['timeRelay3'];
          $this->opto1->setSelectedValue($data['opto1']);
          $this->opto2->setSelectedValue($data['opto2']);
          $this->opto3->setSelectedValue($data['opto3']);
          $this->opto4->setSelectedValue($data['opto4']);
          $this->enterExitInfo->setSelectedValue($data['enterExitInfo']);

          $f = $this->autoUnlocking->setChecked($data['autoUnlocking']);
          $f = $this->lockUnlockCommand->setChecked($data['lockUnlockCommand']);
          $this->holdUpPINCode->Text = $data['holdUpPINCode'];
          $f = $this->twoPersonAccess->setChecked($data['twoPersonAccess']);
          $this->barriereRepeatedAccess->Text = $data['barriereRepeatedAccess'];
          $f = $this->antiPassActive->setChecked($data['antiPassActive']);

          $this->relayExpanderControl->getSelectedValue($data['relayExpanderControl']);
          $this->doorOpenTimeUnit->getSelectedValue($data['doorOpenTimeUnit']);


          $this->optionalCompanyID1->Text = $data['optionalCompanyID1'];
          $this->optionalCompanyID2->Text = $data['optionalCompanyID2'];
          $this->optionalCompanyID3->Text = $data['optionalCompanyID3'];
          $this->optionalCompanyID4->Text = $data['optionalCompanyID4'];
          $this->optionalCompanyID5->Text = $data['optionalCompanyID5'];
          $this->optionalCompanyID6->Text = $data['optionalCompanyID6'];
          $this->optionalCompanyID7->Text = $data['optionalCompanyID7'];
          $this->optionalCompanyID8->Text = $data['optionalCompanyID8'];
          $this->optionalCompanyID9->Text = $data['optionalCompanyID9'];
          $this->optionalCompanyID10->Text = $data['optionalCompanyID10'];

          $this->optionalCardStructur->Text = $data['optionalCardStructur'];
          $this->optionalGantnerNationalCode->Text = $data['optionalGantnerNationalCode'];
          $this->optionalGantnerCustomerCode1->Text = $data['optionalGantnerCustomerCode1'];
          $this->optionalGantnerCustomerCode2->Text = $data['optionalGantnerCustomerCode2'];
          $this->optionalGantnerCustomerCode3->Text = $data['optionalGantnerCustomerCode3'];
          $this->optionalGantnerCustomerCode4->Text = $data['optionalGantnerCustomerCode4'];
          $this->optionalGantnerCustomerCode5->Text = $data['optionalGantnerCustomerCode5'];
          $this->optionalReaderInitialisation->Text = $data['optionalReaderInitialisation'];
          $this->optionalTableCardType->Text = $data['optionalTableCardType'];

        }

    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $id = $this->id->Value;

            $horuxService = new THoruxService();
            $horuxService->onStopDevice($id);
            $horuxService->onStartDevice($id);

            $sa = new TStandAlone();
            $sa->addStandalone("add", $this->id->Value, 'reinit');


            $pBack = array('okMsg'=>Prado::localize('The device was modified successfully'), 'id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('hardware.device.gantner_AccessTerminal.mod', $pBack));
          }
          else
          {
            $pBack = array('koMsg'=>Prado::localize('The device was not modified'));
          }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The device was modified successfully'));
            $horuxService = new THoruxService();
            $horuxService->onStopDevice($this->id->Value);
            $horuxService->onStartDevice($this->id->Value);

            $sa = new TStandAlone();
            $sa->addStandalone("add", $this->id->Value, 'reinit');


          }
          else
            $pBack = array('koMsg'=>Prado::localize('The device was not modified'));
          
          $this->blockRecord('hr_device', $this->id->Value, 0);
          $this->Response->redirect($this->Service->constructUrl('hardware.HardwareList',$pBack));
        }
    }

	public function onCancel($sender, $param)
	{
	     $this->blockRecord('hr_device', $this->id->Value, 0);	
            $this->Response->redirect($this->Service->constructUrl('hardware.HardwareList'));	
	}    

    protected function saveData()
    {
      $cmd = $this->db->createCommand( SQL::SQL_MOD_DEVICE );
	  $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":description",$this->comment->SafeText,PDO::PARAM_STR);
	  $cmd->bindParameter(":isLog",$this->isLog->getChecked(),PDO::PARAM_STR);
	  $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);
	  $cmd->Execute();


      $cmd = $this->db->createCommand( SQL::SQL_UPDATE_GANTNERTERMINAL );
  
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

	  $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);

      $cmd->Execute();

	  return true;
    }

    public function serverValidateName($sender, $param)
    {
      $cmd = $this->db->createCommand( SQL::SQL_IS_READER_NAME_EXIST2);
      $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);
      $array = $cmd->query()->readAll();

      if(count($array) > 0)
        $param->IsValid=false;
      else 
        $param->IsValid=true;
	}


}
