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

  const SQL_SELECT_ALL_GROUP = "SELECT * FROM hr_user_group ORDER BY name";
  const SQL_GET_GROUP = "SELECT * FROM hr_user_group WHERE id=:id";
  const SQL_GET_ACCESS_GROUP = "SELECT * FROM hr_user_group_access WHERE id_group=:id AND id_device=:readerId ";

  const SQL_ADD_GROUP = "INSERT INTO hr_user_group (
                        `name` , 
                        `comment`,
                        `accessPlugin`

                  )
                  VALUES (
                        :name,
                        :comment,
						:accessPlugin
                  )";

  const SQL_ADD_ACCESS_GROUP = "INSERT INTO hr_user_group_access (
                                      `id_group` , 
                                      `id_device`,
                                      `id_access_level` 
                                )
                                VALUES (
                                      :lastId,
                                      :readerId,
                                      :accessLevelId
                                )";


  const SQL_IS_NAME_EXIST = "SELECT name FROM hr_user_group WHERE name=:name";

  const SQL_IS_NAME_EXIST_EXCEPT_ID = "SELECT name FROM hr_user_group WHERE name=:name AND id!=:id ";

  const SQL_MOD_GROUP =  "UPDATE hr_user_group SET
                        `name`=:name,
                        `comment`=:comment,
						`accessPlugin`=:accessPlugin
                        WHERE id=:id"
                      ;

  const SQL_REMOVE_GROUP = "DELETE FROM hr_user_group WHERE id=:id";
  const SQL_REMOVE_ACCESS_GROUP = "DELETE FROM hr_user_group_access WHERE id_group=:id";


  const SQL_HAS_CHILDREN = "SELECT COUNT(*) AS n FROM  hr_user_group_attribution WHERE id_group=:id";

  const SQL_GET_ACCESS_LEVEL = "SELECT * FROM hr_access_level";

  const SQL_GET_HARDWARE_LINK2GROUP = "SELECT * FROM hr_device WHERE accessPoint=1";

  const SQL_SELECT_ALL_HARDWARE_GROUP = "SELECT * FROM hr_reader_group ORDER BY name";

}
