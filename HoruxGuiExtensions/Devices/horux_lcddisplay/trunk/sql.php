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

	const SQL_ADD_LCDDISPLAY = "INSERT INTO hr_horux_lcddisplay (
                        `ip` , 
                        `port` , 
                        `id_device`,
                        `messageTimerDisplay`,
                        `defaultMessage`
                  )
                  VALUES (
                        :ip,
                        :port,
                        :id_device,
                        :messageTimerDisplay,
                        :defaultMessage
                  )";


	const SQL_UPDATE_LCDDISPLAY =  "UPDATE hr_horux_lcddisplay SET
                        `ip`=:ip, 
                        `port`=:port,
                        `messageTimerDisplay`=:messageTimerDisplay,
                        `defaultMessage`=:defaultMessage
                        WHERE id_device=:id"
                      ;
                      
}

?>
