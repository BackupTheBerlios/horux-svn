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

    const SQL_GET_ALL_ACCESS_LEVEL = "SELECT * FROM hr_access_level";
    const SQL_GET_ACCESS_LEVEL_ID = "SELECT * FROM hr_access_level WHERE id=:id";
    const SQL_GET_ACCESS_TIME_ID = "SELECT * FROM hr_access_time WHERE id_access_level=:id";
    const SQL_IS_ACCESS_LEVEL_NAME_EXIST = "SELECT * FROM hr_access_level WHERE name=:name";
    const SQL_IS_ACCESS_LEVEL_NAME_EXIST_EXCEPT_ID = "SELECT * FROM hr_access_level WHERE name=:name AND id!=:id";


    const SQL_ADD_ACCESS_LEVEL = "INSERT INTO hr_access_level (
                        `name` ,
                        `full_access`,
                        `non_working_day`,
                        `week_end`,
                        `monday_default`,
                        `validity_date`,
                        `validity_date_to`,
                        `comment`
                  )
                  VALUES (
                        :name,
                        :full_access,
                        :non_working_day,
                        :week_end,
                        :monday_default,
                        :from,
                        :until,
                        :comment
                  )";

    const SQL_MOD_ACCESS_LEVEL = "UPDATE hr_access_level SET
                        `name`=:name,
                        `full_access`=:full_access,
                        `non_working_day`=:non_working_day,
                        `week_end`=:week_end,
                        `monday_default`=:monday_default,
                        `validity_date`=:from,
                        `validity_date_to`=:until,
                        `comment`=:comment
                        WHERE id=:id"
    ;


    const SQL_ADD_ACCESS_LEVEL_TIME = "INSERT INTO hr_access_time (
                        `id_access_level` ,
                        `day`,
                        `from`,
                        `until`
                  )
                  VALUES (
                        :id_access_level,
                        :day,
                        :from,
                        :until
                  )";

    const SQL_REMOVE_ACCESS_LEVEL = "DELETE FROM hr_access_level WHERE id=:id";
    const SQL_REMOVE_ACCESS_TIME = "DELETE FROM hr_access_time WHERE id_access_level=:id";
}
