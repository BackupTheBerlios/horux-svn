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
    const SQL_ADD_NONWORKINGDAY = "INSERT INTO hr_non_working_day (
                        `name` ,
                        `from` ,
                        `until`,
                        `color`,
                        `comment`,
                        `timeStart`,
                        `timeEnd`
                  )
                  VALUES (
                        :name,
                        :from,
                        :until,
                        :color,
                        :comment,
                        :timeStart,
                        :timeEnd
                  )";

    const SQL_GET_NONWORKINGDAY = "SELECT * FROM hr_non_working_day WHERE id=:id";

    const SQL_UPDATE_NONWORKINGDAY = "UPDATE hr_non_working_day SET `name`=:name, `until`=:until, `from`=:from, `color`=:color, `comment`=:comment, `timeStart`=:timeStart, `timeEnd`=:timeEnd  WHERE id=:id";

    const SQL_DELETE_NONWORKINGDAY = "DELETE FROM hr_non_working_day WHERE id=:id";
}
