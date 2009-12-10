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

	const SQL_ADD_GANTNERTERMINAL = "INSERT INTO hr_gantner_TimeTerminal (
                        `id_device` ,
                        `ipOrDhcp` ,
                        `isAutoRestart` ,
                        `autoRestart` ,
                        `displayTimeout`,
                        `inputTimeout`,
                        `brightness`,
                        `udpServer`,
                        `checkBooking`,
                        `language`
                  )
                  VALUES (
                        :id_device ,
                        :ipOrDhcp,
                        :isAutoRestart,
                        :autoRestart,
                        :displayTimeout,
                        :inputTimeout,
                        :brightness,
                        :udpServer,
                        :checkBooking,
                        :language
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
                        'gantner_TimeTerminal',
                        :isLog,
                        0,
                        0,
                        :description,
                        ''
                  )";
                  
  	const SQL_IS_NAME_EXIST = "SELECT name FROM 
  								hr_device 
  								WHERE type='hr_gantner_TimeTerminal' AND name=:name";

	const SQL_GET_GANTNERTERMINAL = "SELECT * FROM hr_gantner_TimeTerminal AS ai LEFT JOIN hr_device AS d ON d.id=ai.id_device  WHERE ai.id_device=:id";
	
	const SQL_MOD_DEVICE =  "UPDATE hr_device SET
			            `name`=:name,
                                `isLog`=:isLog, 
                                `description`=:description
                                  WHERE id=:id"
                      ;
                      
	const SQL_UPDATE_GANTNERTERMINAL  =  "UPDATE hr_gantner_TimeTerminal SET
                        `ipOrDhcp`=:ipOrDhcp ,
                        `isAutoRestart`=:isAutoRestart ,
                        `autoRestart`=:autoRestart ,
                        `displayTimeout`=:displayTimeout,
                        `inputTimeout`=:inputTimeout,
                        `brightness`=:brightness,
                        `udpServer`=:udpServer,
                        `checkBooking`=:checkBooking,
                        `language`=:language
                        WHERE id_device=:id"
                      ;
                      
   	const SQL_IS_READER_NAME_EXIST2 = "SELECT name FROM 
  								hr_device
  								WHERE type='hr_gantner_TimeTerminal' AND name=:name AND id<>:id";

    const SQL_GET_KEY = "SELECT * FROM hr_gantner_TimeTerminal_key WHERE device_id=:id";

    const SQL_REMOVE_KEY = "DELETE FROM hr_gantner_TimeTerminal_key WHERE device_id=:id";

	const SQL_ADD_KEY = "INSERT INTO hr_gantner_TimeTerminal_key (
                        `device_id` ,
                        `type` ,
                        `key` ,
                        `text` ,
                        `dialog`
                  )
                  VALUES (
                        :id,
                        :type,
                        :key,
                        :text,
                        :dialog
                  )";
}

?>
