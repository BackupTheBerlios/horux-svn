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

    const SQL_DELETE_USER = "DELETE FROM hr_superusers WHERE id=:id AND id>1";
    const SQL_DELETE_GROUP = "DELETE FROM hr_superuser_group WHERE id=:id AND id>1";
    const SQL_DELETE_GROUP_PERM = "DELETE FROM hr_gui_permissions WHERE value=:id";

    const SQL_GET_ALL_USER = "SELECT u.id, u.name, g.name AS groupName, u.locked FROM hr_superusers AS u LEFT JOIN hr_superuser_group AS g ON g.id = u.group_id";
    const SQL_GET_ALL_USER_SAAS = "SELECT u.id, u.name, g.name AS groupName, u.locked FROM hr_superusers AS u LEFT JOIN hr_superuser_group AS g ON g.id = u.group_id WHERE u.id>1";
    const SQL_GET_ALL_USER_2 = "SELECT su.id, su.name AS username, g.name AS groupName, u.name, u.firstname, u.email2, u.phone2, department FROM hr_superusers AS su LEFT JOIN hr_superuser_group AS g ON g.id = su.group_id LEFT JOIN hr_user AS u ON u.id=su.user_id";
    const SQL_GET_ALL_USER_2_SAAS = "SELECT su.id, su.name AS username, g.name AS groupName, u.name, u.firstname, u.email2, u.phone2, department FROM hr_superusers AS su LEFT JOIN hr_superuser_group AS g ON g.id = su.group_id LEFT JOIN hr_user AS u ON u.id=su.user_id WHERE su.id>1";


    const SQL_GET_ID_USER = "SELECT u.id, u.name, g.name AS groupName, u.locked FROM hr_superusers AS u LEFT JOIN hr_superuser_group AS g ON g.id = u.group_id WHERE u.id=:id";

    const SQL_GET_USER_BY_ID = "SELECT * FROM hr_superusers WHERE id=:id";
    const SQL_GET_ALL_GROUP = "SELECT * FROM hr_superuser_group";
    const SQL_GET_ALL_GROUP_SAAS = "SELECT * FROM hr_superuser_group WHERE id>1";
    const SQL_GET_ALL_PERSON = "SELECT id, CONCAT(name, ' ', firstname) AS name FROM hr_user WHERE name<>'??' ORDER BY name, firstname ";
    const SQL_GET_ALL_PERSON_SQLITE = "SELECT id, name || ' ' || firstname AS name FROM hr_user WHERE name<>'??' ORDER BY name, firstname ";

    const SQL_GET_PERM = "SELECT * FROM hr_gui_permissions WHERE value=:id";

    const SQL_IS_NAME_EXIST = "SELECT COUNT(*) AS nb FROM hr_superusers WHERE name=:name";
    const SQL_IS_NAME_EXIST2 = "SELECT COUNT(*) AS nb FROM hr_superusers WHERE name=:name AND id<>:id";

    const SQL_IS_GROUP_EXIST = "SELECT COUNT(*) AS nb FROM hr_superuser_group WHERE name=:name";
    const SQL_IS_GROUP_EXIST2 = "SELECT COUNT(*) AS nb FROM hr_superuser_group WHERE name=:name AND id<>:id";


    const SQL_GET_GROUP_BY_ID = "SELECT * FROM hr_superuser_group WHERE id=:id";

    const SQL_ADD_USER_GROUP = "INSERT INTO hr_superuser_group (
                        `name` ,
                        `superAdmin` ,
                        `dispUserLoggedIn` ,
                        `dispLastAlarm` ,
                        `dispLastTracking` ,
                        `description`
                  )
                  VALUES (
                        :name,
                        :superAdmin,
                        :dispUserLoggedIn,
                        :dispLastAlarm,
                        :dispLastTracking,
                        :description
                  )";

    const SQL_ADD_PERMISSION = "INSERT INTO hr_gui_permissions (
                        `page` ,
                        `selector`,
                        `value`,
                        `allowed`,
                        `shortcut`
                  )
                  VALUES (
                        :page,
                        'group_id',
                        :id,
                        '1',
                        :shortcut
                  )";



    const SQL_ADD_USER = "INSERT INTO hr_superusers (
                        `group_id` ,
                        `user_id` ,
                        `name`,
                        `email`,
                        `password`,
                        `isLogged`
                  )
                  VALUES (
                        :group_id,
                        :user_id,
                        :name,
                        :email,
                        :password,
                        0
                  )";

    const SQL_MOD_USER =  "UPDATE hr_superusers SET
                        `group_id`=:group_id,
                        `user_id`=:user_id,
                        `name`=:name,
                        `email`=:email,
                        `password`=:password
                        WHERE id=:id"
    ;

    const SQL_UPDATE_USER_GROUP = "UPDATE hr_superuser_group SET
                        `name`=:name,
                        `superAdmin`=:superAdmin,
                        `dispUserLoggedIn`=:dispUserLoggedIn,
                        `dispLastAlarm`=:dispLastAlarm,
                        `dispLastTracking`=:dispLastTracking,
                        `description`=:description
                        WHERE id=:id"
    ;
}
