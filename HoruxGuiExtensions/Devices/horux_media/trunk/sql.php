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

	const SQL_ADD_INFODISPLAY = "INSERT INTO hr_horux_InfoDisplay (
                        `id_device` , 
                        `ip` , 
                        `port` , 
                        `id_action_device`
                  )
                  VALUES (
                        :id_device,
                        :ip,
                        :port,
                        :id_action_device
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
                        'horux_InfoDisplay',
                        :isLog,
                        0,
                        0,
                        :description,
			''
                  )";
                  
  	const SQL_IS_NAME_EXIST = "SELECT name FROM 
  								hr_device 
  								WHERE type='horux_InfoDisplay' AND name=:name";

	const SQL_GET_INFODISPLAY = "SELECT * FROM hr_horux_InfoDisplay AS ai LEFT JOIN hr_device AS d ON d.id=ai.id_device  WHERE ai.id_device=:id";
	
	const SQL_MOD_DEVICE =  "UPDATE hr_device SET
			            `name`=:name,
                                `isLog`=:isLog, 
                                `description`=:description
                                  WHERE id=:id"
                      ;
                      
	const SQL_UPDATE_INFODISPLAY =  "UPDATE hr_horux_InfoDisplay SET
                        `ip`=:ip, 
                        `port`=:port, 
                        `id_action_device`=:id_action_device
                        WHERE id_device=:id"
                      ;
                      
   	const SQL_IS_READER_NAME_EXIST2 = "SELECT name FROM 
  								hr_device
  								WHERE type='horux_InfoDisplay' AND name=:name AND id<>:id";
}

?>
