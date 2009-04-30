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

  const SQL_GET_ALARMS = "SELECT * FROM hr_alarms  ORDER BY datetime_ DESC";
  const SQL_GET_ALARMS_BY_DATE = "SELECT * FROM hr_alarms WHERE datetime_>=:from AND datetime_<=:until ORDER BY datetime_ DESC";
  const SQL_GET_ALARMS_BY_DATE_FROM = "SELECT * FROM hr_alarms WHERE datetime_>=:from ORDER BY datetime_ DESC";
  const SQL_GET_ALARMS_BY_DATE_UNTIL = "SELECT * FROM hr_alarms WHERE datetime_<=:until ORDER BY datetime_ DESC";

  const SQL_GET_ALL_NOTIFICATION = "SELECT * FROM hr_notification";

  const SQL_GET_SU = "SELECT id AS Value, name AS Text FROM hr_superusers WHERE email<>''";

  const SQL_GET_PERSON = "SELECT id AS Value, CONCAT(name, ' ', firstname) AS Text FROM hr_user WHERE name<>'??'";
  const SQL_GET_PERSON_SQLITE = "SELECT id AS Value, name || ' ' || firstname AS Text FROM hr_user WHERE name<>'??'";

  const SQL_REMOVE_NOTIFICATION = "DELETE FROM hr_notification WHERE id=:id";
  const SQL_REMOVE_NOTIFICATION_CODE = "DELETE FROM hr_notification_code WHERE id_notification=:id";
  const SQL_REMOVE_NOTIFICATION_SU = "DELETE FROM hr_notification_su WHERE id_notification=:id";

  const SQL_NOTIFICATION = "INSERT INTO hr_notification (
                        `name` ,
                        `emails`,
                        `description`
                  )
                  VALUES (
                        :name,
                        :emails,
                        :description
                  )";

  const SQL_NOTIFICATION_SU = "INSERT INTO hr_notification_su (
                        `id_notification` ,
                        `id_superuser`
                  )
                  VALUES (
                        :id_notification,
                        :id_superuser
                  )";

  const SQL_NOTIFICATION_CODE = "INSERT INTO hr_notification_code (
                        `id_notification` ,
                        `type`,
                        `code`,
                        `param`
                  )
                  VALUES (
                        :id_notification,
                        :type,
                        :code,
                        :param
                  )";

  const SQL_GET_NOTIFICATION = "SELECT * FROM hr_notification WHERE id=:id";

  const SQL_GET_NOTIFICATION_SU = "SELECT COUNT(*) AS n FROM hr_notification_su WHERE id_notification=:id_notification AND id_superuser=:id_superuser";
  const SQL_GET_NOTIFICATION_CODE_USER = "SELECT COUNT(*) AS n FROM hr_notification_code WHERE id_notification=:id_notification AND type='ACCESS' AND code='0' AND param=:param";
  const SQL_GET_NOTIFICATION_CODE = "SELECT COUNT(*) AS n FROM hr_notification_code WHERE id_notification=:id_notification AND type=:type AND code=:code";

  const SQL_NOTIFICATION_UPDATE = "UPDATE hr_notification SET `name`=:name, `emails`=:emails, `description`=:description  WHERE id=:id";
}
