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

    const SQL_GET_ALL_OPEN_TIME = "SELECT * FROM hr_openTime";
    const SQL_GET_ALL_OPEN_TIME2 = "SELECT id, name FROM hr_openTime WHERE id NOT IN ( SELECT id_openTime AS id FROM hr_openTime_attribution WHERE id_device=:id ) ";
    const SQL_GET_ATTRIBUTION = "SELECT oa.id, o.name FROM hr_openTime_attribution AS oa LEFT JOIN hr_openTime AS o ON o.id=oa.id_openTime WHERE oa.id_device=:id";

    const SQL_GET_OPEN_TIME_ID = "SELECT * FROM hr_openTime WHERE id=:id";
    const SQL_GET_OPEN_TIME_TIME_ID = "SELECT * FROM hr_openTime_time WHERE id_openTime=:id";
    const SQL_IS_OPEN_TIME_NAME_EXIST = "SELECT * FROM hr_openTime WHERE name=:name";
    const SQL_IS_OPEN_TIME_NAME_EXIST_EXCEPT_ID = "SELECT * FROM hr_openTime WHERE name=:name AND id!=:id";


    const SQL_ATTRIBUTE_OPEN_TIME = "INSERT INTO hr_openTime_attribution (id_device, id_openTime) VALUES (:id_device, :id_openTime)";
    const SQL_DELETE_OPEN_TIME_ATTRIBUTION = "DELETE FROM hr_openTime_attribution WHERE id=:id";
    const SQL_DELETE_OPEN_TIME_ATTRIBUTION_2 = "DELETE FROM hr_openTime_attribution WHERE id_openTime=:id";

    const SQL_ADD_OPEN_TIME = "INSERT INTO hr_openTime (
                        `name` ,
                        `non_working_day`,
                        `week_end`,
                        `monday_default`,
                        `comment`
                  )
                  VALUES (
                        :name,
                        :non_working_day,
                        :week_end,
                        :monday_default,
                        :comment
                  )";

    const SQL_MOD_OPEN_TIME = "UPDATE hr_openTime SET
                        `name`=:name,
                        `non_working_day`=:non_working_day,
                        `week_end`=:week_end,
                        `monday_default`=:monday_default,
                        `comment`=:comment
                        WHERE id=:id"
    ;


    const SQL_ADD_OPEN_TIME_TIME = "INSERT INTO hr_openTime_time (
                        `id_openTime` ,
                        `day`,
                        `from`,
                        `until`,
                        `unlocking`,
                        `supOpenTooLongAlarm`,
                        `supWithoutPermAlarm`,
                        `checkOnlyCompanyID`,
                        `specialRelayPlan`
                  )
                  VALUES (
                        :id_openTime,
                        :day,
                        :from,
                        :until,
                        :unlocking,
                        :supOpenTooLongAlarm,
                        :supWithoutPermAlarm,
                        :checkOnlyCompanyID,
                        :specialRelayPlan
                  )";

    const SQL_REMOVE_OPEN_TIME = "DELETE FROM hr_openTime WHERE id=:id";
    const SQL_REMOVE_OPEN_TIME_TIME = "DELETE FROM hr_openTime_time WHERE id_openTime=:id";
}
