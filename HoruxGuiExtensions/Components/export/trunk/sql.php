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





    const SQL_ADD_IMPORT = "INSERT INTO hr_user (
                           `name`,
                            `firstname`,
                            `street`,
                            `city`,
                            `country`,
                            `zip`,
                            `phone1`,
                            `phone2`,
                            `email1`,
                            `email2`,
                            `language`,
                            `sex`,
                            `department`,
                            `firme`,
                            `street_pr`,
                            `npa_pr`,
                            `city_pr`,
                            `country_pr`,
                            `picture`,
                            `pin_code`,
                            `password`,
                            `fax`,
                            `avs`,
                            `masterAuthorization`,
                            `validity_date`
                        )
                        VALUES (
                            :name,
                            :firstname,
                            :street,
                            :city,
                            :country,
                            :zip,
                            :phone1,
                            :phone2,
                            :email1,
                            :email2,
                            :language,
                            :sex,
                            :department,
                            :firme,
                            :street_pr,
                            :npa_pr,
                            :city_pr,
                            :country_pr,
                            :picture,
                            :pin_code,
                            :password,
                            :fax,
                            :avs,
                            :masterAuthorization,
                            :validity_date
                        )";

    const SQL_ADD_IMPORT2 = "INSERT INTO hr_user (
                           `name`,
                            `firstname`,
                            `street`,
                            `city`,
                            `country`,
                            `zip`,
                            `phone1`,
                            `phone2`,
                            `email1`,
                            `email2`,
                            `language`,
                            `sex`,
                            `department`,
                            `firme`,
                            `street_pr`,
                            `npa_pr`,
                            `city_pr`,
                            `country_pr`,
                            `picture`,
                            `pin_code`,
                            `password`,
                            `fax`,
                            `avs`,
                            `masterAuthorization`,
                            `validity_date`,
                            `isBlocked`,
                            `locked`
                        )
                        VALUES (
                            :name,
                            :firstname,
                            :street,
                            :city,
                            :country,
                            :zip,
                            :phone1,
                            :phone2,
                            :email1,
                            :email2,
                            :language,
                            :sex,
                            :department,
                            :firme,
                            :street_pr,
                            :npa_pr,
                            :city_pr,
                            :country_pr,
                            :picture,
                            :pin_code,
                            :password,
                            :fax,
                            :avs,
                            :masterAuthorization,
                            :validity_date,
                            :isBlocked,
                            :locked
                        )";

    const SQL_ADD_IMPORT3 = "INSERT INTO hr_user (
                           `name`,
                            `firstname`,
                            `validity_date`,
                            `isBlocked`,
                            `locked`
                        )
                        VALUES (
                            :name,
                            :firstname,
                            :validity_date,
                            :isBlocked,
                            :locked
                        )";

}

?>
