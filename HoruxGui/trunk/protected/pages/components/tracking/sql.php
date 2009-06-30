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

    const SQL_GET_TRACKING = "SELECT t.id, u.name, u.firstName, t.date, t.time, d.name AS device, t.id_comment, k.identificator, t.is_access FROM hr_tracking AS t LEFT JOIN hr_user AS u ON u.id = t.id_user LEFT JOIN hr_device AS d ON d.id=t.id_entry LEFT JOIN hr_keys AS k ON k.serialNumber=t.key ORDER BY t.id DESC LIMIT 0,1000";
    const SQL_GET_ACCESS_POINT = "SELECT id AS Value, name AS Text FROM hr_device WHERE accessPoint=1";
}
