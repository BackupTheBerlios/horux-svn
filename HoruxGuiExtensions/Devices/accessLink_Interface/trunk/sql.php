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


class SQL 
{
	const SQL_ADD_INTERFACE =  "INSERT INTO hr_accessLink_Interface (
			            `id_device`,
                        `ip` , 
                        `mask` , 
                        `gateway` , 
                        `data_port` , 
                        `server1` , 
                        `server2` , 
                        `server3` , 
                        `password` , 
                        `time_zone` , 
                        `temp_max`
                  )
                  VALUES (
                  		:id_device,
                        :ip,
                        :mask,
                        :gateway,
                        :data_port,
                        :server1,
                        :server2,
                        :server3,
                        'oel',
                        '0',
                        :temp_max
                  )";

  	const SQL_IS_NAME_EXIST = "SELECT name FROM 
  								hr_device 
  								WHERE type='accessLink_Interface' AND name=:name";

  	const SQL_IS_IP_EXIST =  "SELECT ip FROM 
  								hr_accessLink_Interface 
  							  WHERE ip=:ip";


	const SQL_ADD_DEVICE = "INSERT INTO hr_device (
                        `name` , 
                        `accessPoint` , 
                        `type` , 
                        `parent_id` , 
                        `isLog` , 
                        `locked` , 
                        `description` 
                  )
                  VALUES (
                        :name,
                        0,
                        'accessLink_Interface',
                        0,
                        :isLog,
                        0,
                        :description
                  )";





	const SQL_GET_INTERFACE = "SELECT * FROM hr_accessLink_Interface AS ai LEFT JOIN hr_device AS d ON d.id=ai.id_device  WHERE ai.id_device=:id";


  	const SQL_IS_NAME_EXIST_EXCEPT_ID = "SELECT name FROM 
  											hr_device 
  										WHERE type='accessLink_Interface' AND name=:name AND id!=:id";

  	const SQL_IS_IP_EXIST_EXCEPT_ID =  "SELECT ip FROM 
  											hr_accessLink_Interface 
  										WHERE ip=:ip AND id_device!=:id";



	const SQL_MOD_INTERFACE =  "UPDATE hr_accessLink_Interface SET
                        `mask`=:mask, 
                        `ip`=:ip, 
                        `gateway`=:gateway, 
                        `data_port`=:data_port, 
                        `server1`=:server1, 
                        `server2`=:server2, 
                        `server3`=:server3, 
                        `temp_max`=:temp_max
                        WHERE id_device=:id"
                      ;

	const SQL_MOD_DEVICE =  "UPDATE hr_device SET
			            `name`=:name,
                        `isLog`=:isLog, 
                        `description`=:description 
                        WHERE id=:id"
                      ;
	
	
}

?>
