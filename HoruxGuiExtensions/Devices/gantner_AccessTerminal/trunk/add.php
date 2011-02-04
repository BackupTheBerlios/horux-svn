<?php

Prado::using('horux.pages.hardware.device.gantner_AccessTerminal.sql');

class add extends AddDevicePage {
    protected $lastId;
    public function onLoad($param) {
        $this->deviceName = "gantner_AccessTerminal";

        parent::onLoad($param);
    }

    public function saveData() {
        parent::saveData();

        if($this->lastId !== false) {
            $cmd = $this->db->createCommand( SQL::SQL_ADD_GANTNERTERMINAL );

            $cmd->bindValue(":id_device",$this->lastId,PDO::PARAM_STR);
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


            $cmd->Execute();

            $horuxService = new THoruxService();
            $horuxService->onStopDevice($id);
            $horuxService->onStartDevice($id);
            $sa = new TStandAlone();
            $sa->addStandalone("add", $this->lastId, 'gantnerAccessReinit');

            return true;
        } else {
            return false;
        }
    }

}
