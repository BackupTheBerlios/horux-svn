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

	const SQL_GET_USER_LOGGED = "SELECT us.id, us.name AS userName, gr.name, us.lastConnection FROM hr_superusers AS us LEFT JOIN hr_superuser_group AS gr ON gr.id=us.group_id WHERE us.isLogged=1";

    const SQL_GET_LAST_TRACK = "SELECT CONCAT(pe.name,' ', firstname) AS name, date, time, en.name AS entry, tr.id AS id FROM hr_tracking AS tr LEFT JOIN hr_user AS pe ON pe.id=tr.id_user LEFT JOIN hr_device AS en ON en.id=tr.id_entry WHERE is_access=1 ORDER BY tr.id DESC LIMIT 0,5";
	const SQL_GET_LAST_TRACK_SQLITE = "SELECT pe.name || ' ' || firstname AS name, date, time, en.name AS entry FROM hr_tracking AS tr LEFT JOIN hr_user AS pe ON pe.id=tr.id_user LEFT JOIN hr_device AS en ON en.id=tr.id_entry WHERE is_access=1 ORDER BY tr.id DESC LIMIT 0,5";

    const SQL_GET_LAST_ALARMS = "SELECT type, datetime_, id_object, id FROM hr_alarms ORDER BY id DESC LIMIT 0,5";

}

?>
