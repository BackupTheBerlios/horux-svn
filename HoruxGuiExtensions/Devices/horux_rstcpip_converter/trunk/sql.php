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

	const SQL_ADD_DEVICE = "INSERT INTO hr_horux_rstcpip_converter (
                        `ip` , 
                        `port` , 
                        `id_device`
                  )
                  VALUES (
                        :ip,
                        :port,
                        :id_device
                  )";


                      
	const SQL_UPDATE_DEVICE =  "UPDATE hr_horux_rstcpip_converter SET
                        `ip`=:ip, 
                        `port`=:port
                        WHERE id_device=:id"
                      ;                      
}

?>
