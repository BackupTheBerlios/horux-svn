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

        const SQL_GET_DEVICES = "SELECT * FROM hr_device";

        const SQL_GET_CONTROLLER = "SELECT * FROM hr_horux_controller";

	const SQL_ADD_DEVICE2 = "INSERT INTO hr_a3m_lgm (
                        `address` ,
                        `id_device`,
                        `serialNumberFormat`
                  )
                  VALUES (
                        :address,
                        :id_device,
                        :serialNumberFormat
                  )";


	const SQL_ADD_DEVICE = "INSERT INTO hr_device (
                        `name` , 
                        `accessPoint` , 
                        `type` , 
                        `isLog` , 
                        `locked` , 
                        `parent_id` , 
                        `description`,
                        `accessPlugin`,
			`horuxControllerId`
                  )
                  VALUES (
                        :name,
                        1,
                        'a3m_lgm',
                        :isLog,
                        0,
                        :parent_id,
                        :description,
			:accessPlugin,
			:horuxControllerId
                  )";
                  
  	const SQL_IS_NAME_EXIST = "SELECT name FROM 
  								hr_device 
  								WHERE type='a3m_lgm' AND name=:name";

	const SQL_GET_TCPIP= "SELECT * FROM hr_a3m_lgm AS ai LEFT JOIN hr_device AS d ON d.id=ai.id_device  WHERE ai.id_device=:id";
	
	const SQL_MOD_DEVICE =  "UPDATE hr_device SET
			            `name`=:name,
                        `isLog`=:isLog, 
                        `description`=:description, 
                        `accessPlugin`=:accessPlugin,
			`horuxControllerId`=:horuxControllerId,
                        `parent_id` = :parent_id
                        WHERE id=:id"
                      ;
                      
	const SQL_UPDATE_DEVICE =  "UPDATE hr_a3m_lgm SET
                        `address`=:address,
                        `serialNumberFormat`=:serialNumberFormat
                        WHERE id_device=:id"
                      ;
                      
   	const SQL_IS_READER_NAME_EXIST2 = "SELECT name FROM 
  								hr_device
  								WHERE type='a3m_lgm' AND name=:name AND id<>:id";
}

?>
