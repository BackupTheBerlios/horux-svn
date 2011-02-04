<?php

class SQL {
	
	const SQL_GET_INTERFACE = "SELECT id, name FROM hr_device WHERE type='accessLink_Interface'";

	const SQL_ADD_RS485READER = "INSERT INTO hr_accessLink_ReaderRS485 (
                        `address` , 
                        `id_device` , 
                        `memory` , 
                        `rtc` , 
                        `lcd` , 
                        `keyboard` , 
                        `eeprom` , 
                        `defaultText`, 
                        `outputTime1`, 
                        `outputTime2`, 
                        `outputTime3`,  
                        `outputTime4`,  
                        `antipassback`,
                        `standalone`,
                        `open_mode`,
                        `open_mode_timeout`,
                        `open_mode_input`
                  )
                  VALUES (
                        :address,
                        :id_device,
                        :memory,
                        :rtc,
                        :lcd,
                        :keyboard,
                        :eeprom,
                        :defaultText,
                        :outputTime1,
                        :outputTime2,
                        :outputTime3,
                        :outputTime4,
                        :antipassback,
                        :standalone,
                        :open_mode,
                        :open_mode_timeout,
                        :open_mode_input
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
                        'accessLink_ReaderRS485',
                        :isLog,
                        0,
                        :id_interface,
                        :description,
			:accessPlugin
                  )";
                  
  	const SQL_IS_NAME_EXIST = "SELECT name FROM 
  								hr_device 
  								WHERE type='accessLink_ReaderRS485' AND name=:name";

	const SQL_GET_RS485 = "SELECT * FROM hr_accessLink_ReaderRS485 AS ai LEFT JOIN hr_device AS d ON d.id=ai.id_device  WHERE ai.id_device=:id";
	
	const SQL_MOD_DEVICE =  "UPDATE hr_device SET
			            `name`=:name,
                        `isLog`=:isLog, 
                        `parent_id`=:id_interface, 
                        `description`=:description, 
                        `accessPlugin`=:accessPlugin 
                        WHERE id=:id"
                      ;
                      
	const SQL_UPDATE_RS485READER =  "UPDATE hr_accessLink_ReaderRS485 SET
                        `address`=:address, 
                        `memory`=:memory, 
                        `rtc`=:rtc, 
                        `lcd`=:lcd, 
                        `keyboard`=:keyboard, 
                        `eeprom`=:eeprom ,
                        `defaultText`=:defaultText,
                        `outputTime1`=:outputTime1, 
                        `outputTime2`=:outputTime2,
                        `outputTime3`=:outputTime3,
                        `outputTime4`=:outputTime4,
                        `antipassback`=:antipassback,
                        `standalone`=:standalone,
                        `open_mode`=:open_mode,
                        `open_mode_timeout`=:open_mode_timeout,
                        `open_mode_input`=:open_mode_input
                        WHERE id_device=:id"
                      ;
                      
   	const SQL_IS_READER_NAME_EXIST2 = "SELECT name FROM 
  								hr_device
  								WHERE type='accessLink_ReaderRS485' AND name=:name AND id<>:id";
}

?>
