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


class SQL {
	
      const SQL_GET_ACCESSPOINT = "SELECT id, name FROM hr_device WHERE accessPoint=1";




	const SQL_ADD_GANTNERTERMINAL = "INSERT INTO `hr_gantner_AccessTerminal` (
                                        `id_device`,
                                        `ipOrDhcp`,
                                        `checkBooking`,
                                        `userMemory`,
                                        `accessMemory`,
                                        `subscriberNumber`,
                                        `plantNumber`,
                                        `mainCompIdCard`,
                                        `bookingCodeSumWinSwitchOver`,
                                        `switchOverLeap`,
                                        `waitingTimeInput`,
                                        `monitoringTime`,
                                        `monitorinChangingTime`,
                                        `cardReaderType`,
                                        `normalRelayPlan`,
                                        `specialRelayPlan`,
                                        `maxDoorOpenTime`,
                                        `warningTimeDoorOpenTime`,
                                        `unlockingTime`,
                                        `relay1`,
                                        `timeRelay1`,
                                        `relay2`,
                                        `timeRelay2`,
                                        `relay3`,
                                        `timeRelay3`,
                                        `relay4`,
                                        `timeRelay4`,
                                        `opto1`,
                                        `opto2`,
                                        `opto3`,
                                        `opto4`,
                                        `enterExitInfo`,
                                        `autoUnlocking`,
                                        `lockUnlockCommand`,
                                        `holdUpPINCode`,
                                        `twoPersonAccess`,
                                        `barriereRepeatedAccess`,
                                        `timeBookingControl`,
                                        `antiPassActive`,
                                        `relayExpanderControl`,
                                        `terminalType`,
                                        `doorOpenTimeUnit`,
                                        `readerTimeout`,
                                        `readerFiu`,
                                        `readerEntryExit`,
                                        `optionalCompanyID1`,
                                        `optionalCompanyID2`,
                                        `optionalCompanyID3`,
                                        `optionalCompanyID4`,
                                        `optionalCompanyID5`,
                                        `optionalCompanyID6`,
                                        `optionalCompanyID7`,
                                        `optionalCompanyID8`,
                                        `optionalCompanyID9`,
                                        `optionalCompanyID10`,
                                        `optionalCardStructur`,
                                        `optionalGantnerNationalCode`,
                                        `optionalGantnerCustomerCode1`,
                                        `optionalGantnerCustomerCode2`,
                                        `optionalGantnerCustomerCode3`,
                                        `optionalGantnerCustomerCode4`,
                                        `optionalGantnerCustomerCode5`,
                                        `optionalReaderInitialisation`,
                                        `optionalTableCardType`
                                        )
                                        VALUES (
                                        :id_device,
                                        :ipOrDhcp,
                                        :checkBooking,
                                        :userMemory,
                                        :accessMemory,
                                        :subscriberNumber,
                                        :plantNumber,
                                        :mainCompIdCard,
                                        :bookingCodeSumWinSwitchOver,
                                        :switchOverLeap,
                                        :waitingTimeInput,
                                        :monitoringTime,
                                        :monitorinChangingTime,
                                        :cardReaderType,
                                        0,
                                        0,
                                        :maxDoorOpenTime,
                                        :warningTimeDoorOpenTime,
                                        :unlockingTime,
                                        :relay1,
                                        :timeRelay1,
                                        :relay2,
                                        :timeRelay2,
                                        :relay3,
                                        :timeRelay3,
                                        :relay4,
                                        :timeRelay4,
                                        :opto1,
                                        :opto2,
                                        :opto3,
                                        :opto4,
                                        :enterExitInfo,
                                        :autoUnlocking,
                                        :lockUnlockCommand,
                                        :holdUpPINCode,
                                        :twoPersonAccess,
                                        :barriereRepeatedAccess,
                                        0,
                                        :antiPassActive,
                                        :relayExpanderControl,
                                        0,
                                        :doorOpenTimeUnit,
                                        0,
                                        0,
                                        0,
                                        :optionalCompanyID1,
                                        :optionalCompanyID2,
                                        :optionalCompanyID3,
                                        :optionalCompanyID4,
                                        :optionalCompanyID5,
                                        :optionalCompanyID6,
                                        :optionalCompanyID7,
                                        :optionalCompanyID8,
                                        :optionalCompanyID9,
                                        :optionalCompanyID10,
                                        :optionalCardStructur,
                                        :optionalGantnerNationalCode,
                                        :optionalGantnerCustomerCode1,
                                        :optionalGantnerCustomerCode2,
                                        :optionalGantnerCustomerCode3,
                                        :optionalGantnerCustomerCode4,
                                        :optionalGantnerCustomerCode5,
                                        :optionalReaderInitialisation,
                                        :optionalTableCardType
                                        )";



	const SQL_ADD_DEVICE = "INSERT INTO hr_device (
                        `name` , 
                        `accessPoint` , 
                        `type` , 
                        `isLog` , 
                        `locked` , 
                        `parent_id` , 
                        `description`,
                        `accessPlugin`
                  )
                  VALUES (
                        :name,
                        1,
                        'gantner_AccessTerminal',
                        :isLog,
                        0,
                        0,
                        :description,
                        ''
                  )";
                  
  	const SQL_IS_NAME_EXIST = "SELECT name FROM 
  								hr_device 
  								WHERE type='hr_gantner_AccessTerminal' AND name=:name";

	const SQL_GET_GANTNERTERMINAL = "SELECT * FROM hr_gantner_AccessTerminal AS ai LEFT JOIN hr_device AS d ON d.id=ai.id_device  WHERE ai.id_device=:id";
	
	const SQL_MOD_DEVICE =  "UPDATE hr_device SET
			            `name`=:name,
                                `isLog`=:isLog, 
                                `description`=:description
                                  WHERE id=:id"
                      ;
                      
	const SQL_UPDATE_GANTNERTERMINAL  =  "UPDATE hr_gantner_AccessTerminal SET
                        `ipOrDhcp`=:ipOrDhcp,
                        `checkBooking`=:checkBooking,
                        `userMemory`=:userMemory,
                        `accessMemory`=:accessMemory,
                        `subscriberNumber`=:subscriberNumber,
                        `plantNumber`=:plantNumber,
                        `mainCompIdCard`=:mainCompIdCard,
                        `bookingCodeSumWinSwitchOver`=:bookingCodeSumWinSwitchOver,
                        `switchOverLeap`=:switchOverLeap,
                        `waitingTimeInput`=:waitingTimeInput,
                        `monitoringTime`=:monitoringTime,
                        `monitorinChangingTime`=:monitorinChangingTime,
                        `cardReaderType`=:cardReaderType,
                        `maxDoorOpenTime`=:maxDoorOpenTime,
                        `warningTimeDoorOpenTime`=:warningTimeDoorOpenTime,
                        `unlockingTime`=:unlockingTime,
                        `relay1`=:relay1,
                        `timeRelay1`=:timeRelay1,
                        `relay2`=:relay2,
                        `timeRelay2`=:timeRelay2,
                        `relay3`=:relay3,
                        `timeRelay3`=:timeRelay3,
                        `relay4`=:relay4,
                        `timeRelay4`=:timeRelay4,
                        `opto1`=:opto1,
                        `opto2`=:opto2,
                        `opto3`=:opto3,
                        `opto4`=:opto4,
                        `enterExitInfo`=:enterExitInfo,
                        `autoUnlocking`=:autoUnlocking,
                        `lockUnlockCommand`=:lockUnlockCommand,
                        `holdUpPINCode`=:holdUpPINCode,
                        `twoPersonAccess`=:twoPersonAccess,
                        `barriereRepeatedAccess`=:barriereRepeatedAccess,
                        `antiPassActive`=:antiPassActive,
                        `relayExpanderControl`=:relayExpanderControl,
                        `doorOpenTimeUnit`=:doorOpenTimeUnit,
                        `optionalCompanyID1`=:optionalCompanyID1,
                        `optionalCompanyID2`=:optionalCompanyID2,
                        `optionalCompanyID3`=:optionalCompanyID3,
                        `optionalCompanyID4`=:optionalCompanyID4,
                        `optionalCompanyID5`=:optionalCompanyID5,
                        `optionalCompanyID6`=:optionalCompanyID6,
                        `optionalCompanyID7`=:optionalCompanyID7,
                        `optionalCompanyID8`=:optionalCompanyID8,
                        `optionalCompanyID9`=:optionalCompanyID9,
                        `optionalCompanyID10`=:optionalCompanyID10,
                        `optionalCardStructur`=:optionalCardStructur,
                        `optionalGantnerNationalCode`=:optionalGantnerNationalCode,
                        `optionalGantnerCustomerCode1`=:optionalGantnerCustomerCode1,
                        `optionalGantnerCustomerCode2`=:optionalGantnerCustomerCode2,
                        `optionalGantnerCustomerCode3`=:optionalGantnerCustomerCode3,
                        `optionalGantnerCustomerCode4`=:optionalGantnerCustomerCode4,
                        `optionalGantnerCustomerCode5`=:optionalGantnerCustomerCode5,
                        `optionalReaderInitialisation`=:optionalReaderInitialisation,
                        `optionalTableCardType`=:optionalTableCardType
                        WHERE id_device=:id"
                      ;
                      
   	const SQL_IS_READER_NAME_EXIST2 = "SELECT name FROM 
  								hr_device
  								WHERE type='hr_gantner_AccessTerminal' AND name=:name AND id<>:id";

}

?>
