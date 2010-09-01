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
    const SQL_GET_ALL_EXPORT = "SELECT * FROM hr_export";

    const SQL_GET_EXPORT = "SELECT * FROM hr_export WHERE id=:id";

    const SQL_DELETE_EXPORT = "DELETE FROM hr_export WHERE id=:id";

    const SQL_ADD_EXPORT = "INSERT INTO hr_export (
                        `name` ,
                        `sql`,
                        `description`
                  )
                  VALUES (
                        :name,
                        :sql,
                        :description
                  )";

    const SQL_UPDATE_EXPORT = "UPDATE hr_export SET
                        `name`=:name ,
                        `sql`=:sql,
                        `description`=:description WHERE id=:id";

    const SQL_GET_ALL_IMPORT = "SELECT * FROM hr_import";

    const SQL_GET_IMPORT = "SELECT * FROM hr_import WHERE id=:id";

    const SQL_DELETE_IMPORT = "DELETE FROM hr_import WHERE id=:id";

    const SQL_ADD_IMPORT = "INSERT INTO hr_import (
                        `name` ,
                        `tb_name`,
                        `cols`,
                        `terminated_by`,
                        `enclosed_by`,
                        `escaped_by`,
                        `description`
                  )
                  VALUES (
                        :name,
                        :tb_name,
                        :cols,
                        :terminated_by,
                        :enclosed_by,
                        :escaped_by,
                        :description
                  )";

    const SQL_UPDATE_IMPORT = "UPDATE hr_import SET
                        `name`=:name ,
                        `tb_name`=:tb_name,
                        `cols`=:cols,
                        `terminated_by`=:terminated_by,
                        `enclosed_by`=:enclosed_by,
                        `escaped_by`=:escaped_by,
                        `description`=:description WHERE id=:id";
}
?>
