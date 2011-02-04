<?php

Prado::using('horux.pages.hardware.device.gantner_AccessTerminal.sql');

class mod extends ModDevicePage {
    public function onLoad($param) {
        $this->deviceName = "gantner_AccessTerminal";
        parent::onLoad($param);
    }
    

    public function setData() {

        parent::setData();

        $this->ipOrDhcp->Text = $this->data['ipOrDhcp'];
        $this->checkBooking->Text = $this->data['checkBooking'];


        $this->userMemory->setSelectedValue($this->data['userMemory']) ;
        $this->accessMemory->setSelectedValue($this->data['accessMemory']) ;
        $this->subscriberNumber->Text = $this->data['subscriberNumber'];
        $this->plantNumber->Text = $this->data['plantNumber'];
        $this->mainCompIdCard->Text = $this->data['mainCompIdCard'];
        $this->bookingCodeSumWinSwitchOver->Text = $this->data['bookingCodeSumWinSwitchOver'];
        $this->switchOverLeap->Text = $this->data['switchOverLeap'];
        $this->waitingTimeInput->Text = $this->data['waitingTimeInput'];
        $this->monitoringTime->Text = $this->data['monitoringTime'];
        $this->monitorinChangingTime->Text = $this->data['monitorinChangingTime'];
        $this->cardReaderType->setSelectedValue($this->data['cardReaderType']) ;
        $this->maxDoorOpenTime->Text = $this->data['maxDoorOpenTime'];
        $this->warningTimeDoorOpenTime->Text = $this->data['warningTimeDoorOpenTime'];
        $this->unlockingTime->Text = $this->data['unlockingTime'];
        $this->relay1->setSelectedValue($this->data['relay1']) ;
        $this->timeRelay1->Text = $this->data['timeRelay1'];
        $this->relay2->setSelectedValue($this->data['relay2']);
        $this->timeRelay2->Text = $this->data['timeRelay2'];
        $this->relay3->setSelectedValue($this->data['relay3']) ;
        $this->timeRelay3->Text = $this->data['timeRelay3'];
        $this->relay3->setSelectedValue($this->data['relay3']);
        $this->timeRelay3->Text = $this->data['timeRelay3'];
        $this->opto1->setSelectedValue($this->data['opto1']);
        $this->opto2->setSelectedValue($this->data['opto2']);
        $this->opto3->setSelectedValue($this->data['opto3']);
        $this->opto4->setSelectedValue($this->data['opto4']);
        $this->enterExitInfo->setSelectedValue($this->data['enterExitInfo']);

        $f = $this->autoUnlocking->setChecked($this->data['autoUnlocking']);
        $f = $this->lockUnlockCommand->setChecked($this->data['lockUnlockCommand']);
        $this->holdUpPINCode->Text = $this->data['holdUpPINCode'];
        $f = $this->twoPersonAccess->setChecked($this->data['twoPersonAccess']);
        $this->barriereRepeatedAccess->Text = $this->data['barriereRepeatedAccess'];
        $f = $this->antiPassActive->setChecked($this->data['antiPassActive']);

        $this->relayExpanderControl->setSelectedValue($this->data['relayExpanderControl']);
        $this->doorOpenTimeUnit->setSelectedValue($this->data['doorOpenTimeUnit']);


        $this->optionalCompanyID1->Text = $this->data['optionalCompanyID1'];
        $this->optionalCompanyID2->Text = $this->data['optionalCompanyID2'];
        $this->optionalCompanyID3->Text = $this->data['optionalCompanyID3'];
        $this->optionalCompanyID4->Text = $this->data['optionalCompanyID4'];
        $this->optionalCompanyID5->Text = $this->data['optionalCompanyID5'];
        $this->optionalCompanyID6->Text = $this->data['optionalCompanyID6'];
        $this->optionalCompanyID7->Text = $this->data['optionalCompanyID7'];
        $this->optionalCompanyID8->Text = $this->data['optionalCompanyID8'];
        $this->optionalCompanyID9->Text = $this->data['optionalCompanyID9'];
        $this->optionalCompanyID10->Text = $this->data['optionalCompanyID10'];

        $this->optionalCardStructur->Text = $this->data['optionalCardStructur'];
        $this->optionalGantnerNationalCode->Text = $this->data['optionalGantnerNationalCode'];
        $this->optionalGantnerCustomerCode1->Text = $this->data['optionalGantnerCustomerCode1'];
        $this->optionalGantnerCustomerCode2->Text = $this->data['optionalGantnerCustomerCode2'];
        $this->optionalGantnerCustomerCode3->Text = $this->data['optionalGantnerCustomerCode3'];
        $this->optionalGantnerCustomerCode4->Text = $this->data['optionalGantnerCustomerCode4'];
        $this->optionalGantnerCustomerCode5->Text = $this->data['optionalGantnerCustomerCode5'];
        $this->optionalReaderInitialisation->Text = $this->data['optionalReaderInitialisation'];
        $this->optionalTableCardType->Text = $this->data['optionalTableCardType'];
    }


    public function saveData() {

        parent::saveData();

        $cmd = $this->db->createCommand( SQL::SQL_UPDATE_GANTNERTERMINAL );

        $cmd->bindValue(":ipOrDhcp",$this->ipOrDhcp->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":checkBooking",$this->checkBooking->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":userMemory",$this->userMemory->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":accessMemory",$this->accessMemory->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":subscriberNumber",$this->subscriberNumber->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":plantNumber",$this->plantNumber->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":mainCompIdCard",$this->mainCompIdCard->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":bookingCodeSumWinSwitchOver",$this->bookingCodeSumWinSwitchOver->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":switchOverLeap",$this->switchOverLeap->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":waitingTimeInput",$this->waitingTimeInput->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":monitoringTime",$this->monitoringTime->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":monitorinChangingTime",$this->monitorinChangingTime->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":cardReaderType",$this->cardReaderType->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":maxDoorOpenTime",$this->maxDoorOpenTime->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":warningTimeDoorOpenTime",$this->warningTimeDoorOpenTime->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":unlockingTime",$this->unlockingTime->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":relay1",$this->relay1->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":timeRelay1",$this->timeRelay1->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":relay2",$this->relay2->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":timeRelay2",$this->timeRelay2->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":relay3",$this->relay3->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":timeRelay3",$this->timeRelay3->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":relay4",$this->relay3->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":timeRelay4",$this->timeRelay3->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":opto1",$this->opto1->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":opto2",$this->opto2->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":opto3",$this->opto3->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":opto4",$this->opto4->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":enterExitInfo",$this->enterExitInfo->getSelectedValue(),PDO::PARAM_STR);

        $f1 = $this->autoUnlocking->getChecked() ? 1 : 0;
        $cmd->bindValue(":autoUnlocking",$f1,PDO::PARAM_STR);

        $f2 = $this->lockUnlockCommand->getChecked() ? 1 : 0;
        $cmd->bindValue(":lockUnlockCommand",$f2,PDO::PARAM_STR);

        $cmd->bindValue(":holdUpPINCode",$this->holdUpPINCode->SafeText,PDO::PARAM_STR);

        $f3 = $this->twoPersonAccess->getChecked() ? 1 : 0;
        $cmd->bindValue(":twoPersonAccess",$f3,PDO::PARAM_STR);

        $cmd->bindValue(":barriereRepeatedAccess",$this->barriereRepeatedAccess->SafeText,PDO::PARAM_STR);

        $f4 = $this->antiPassActive->getChecked() ? 1 : 0;
        $cmd->bindValue(":antiPassActive",$f4,PDO::PARAM_STR);

        $cmd->bindValue(":relayExpanderControl",$this->relayExpanderControl->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":doorOpenTimeUnit",$this->doorOpenTimeUnit->getSelectedValue(),PDO::PARAM_STR);


        $cmd->bindValue(":optionalCompanyID1",$this->optionalCompanyID1->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":optionalCompanyID2",$this->optionalCompanyID2->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":optionalCompanyID3",$this->optionalCompanyID3->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":optionalCompanyID4",$this->optionalCompanyID4->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":optionalCompanyID5",$this->optionalCompanyID5->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":optionalCompanyID6",$this->optionalCompanyID6->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":optionalCompanyID7",$this->optionalCompanyID7->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":optionalCompanyID8",$this->optionalCompanyID8->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":optionalCompanyID9",$this->optionalCompanyID9->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":optionalCompanyID10",$this->optionalCompanyID10->SafeText,PDO::PARAM_STR);

        $cmd->bindValue(":optionalCardStructur",$this->optionalCardStructur->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":optionalGantnerNationalCode",$this->optionalGantnerNationalCode->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":optionalGantnerCustomerCode1",$this->optionalGantnerCustomerCode1->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":optionalGantnerCustomerCode2",$this->optionalGantnerCustomerCode2->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":optionalGantnerCustomerCode3",$this->optionalGantnerCustomerCode3->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":optionalGantnerCustomerCode4",$this->optionalGantnerCustomerCode4->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":optionalGantnerCustomerCode5",$this->optionalGantnerCustomerCode5->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":optionalReaderInitialisation",$this->optionalReaderInitialisation->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":optionalTableCardType",$this->optionalTableCardType->SafeText,PDO::PARAM_STR);

        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);

        $cmd->Execute();

        $horuxService = new THoruxService();
        $horuxService->onStopDevice($id);
        $horuxService->onStartDevice($id);

        $sa = new TStandAlone();
        $sa->addStandalone("add", $this->id->Value, 'reinit');

        return true;
    }
}
