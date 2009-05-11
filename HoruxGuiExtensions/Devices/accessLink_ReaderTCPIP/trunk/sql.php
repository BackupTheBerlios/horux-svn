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
	
	const SQL_ADD_TCPIPREADER = "INSERT INTO hr_accessLink_ReaderTCPIP (
                        `ip` , 
                        `port` , 
                        `id_device` , 
                        `outputTime1`, 
                        `outputTime2`, 
                        `outputTime3`,  
                        `outputTime4`,  
                        `antipassback`,
                        `open_mode`,
                        `open_mode_timeout`,
                        `open_mode_input`
                  )
                  VALUES (
                        :ip,
                        :port,
                        :id_device,
                        :outputTime1,
                        :outputTime2,
                        :outputTime3,
                        :outputTime4,
                        :antipassback,
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
                        'accessLink_ReaderTCPIP',
                        :isLog,
                        0,
                        0,
                        :description,
			:accessPlugin
                  )";
                  
  	const SQL_IS_NAME_EXIST = "SELECT name FROM 
  								hr_device 
  								WHERE type='hr_accessLink_ReaderTCPIP' AND name=:name";

	const SQL_GET_TCPIP= "SELECT * FROM hr_accessLink_ReaderTCPIP AS ai LEFT JOIN hr_device AS d ON d.id=ai.id_device  WHERE ai.id_device=:id";
	
	const SQL_MOD_DEVICE =  "UPDATE hr_device SET
			            `name`=:name,
                        `isLog`=:isLog, 
                        `description`=:description, 
                        `accessPlugin`=:accessPlugin 
                        WHERE id=:id"
                      ;
                      
	const SQL_UPDATE_TCPIPREADER =  "UPDATE hr_accessLink_ReaderTCPIP SET
                        `ip`=:ip, 
                        `port`=:port, 
                        `outputTime1`=:outputTime1, 
                        `outputTime2`=:outputTime2,
                        `outputTime3`=:outputTime3,
                        `outputTime4`=:outputTime4,
                        `antipassback`=:antipassback,
                        `open_mode`=:open_mode,
                        `open_mode_timeout`=:open_mode_timeout,
                        `open_mode_input`=:open_mode_input
                        WHERE id_device=:id"
                      ;
                      
   	const SQL_IS_READER_NAME_EXIST2 = "SELECT name FROM 
  								hr_device
  								WHERE type='hr_accessLink_ReaderTCPIP' AND name=:name AND id<>:id";
}

?>
