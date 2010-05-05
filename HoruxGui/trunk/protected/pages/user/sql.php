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

    const SQL_UPDATE_SETBLOCK_USER = "UPDATE hr_user SET isBlocked=:flag WHERE id=:id";
    const SQL_UPDATE_SETBLOCK_KEY = "UPDATE hr_keys SET isBlocked=:flag WHERE id=:id";

    const SQL_DELETE_KEY_ATTRIBUTION = "DELETE FROM hr_keys_attribution WHERE id_key=:id";
    const SQL_GET_KEY = "SELECT t.id, t.identificator, t.serialNumber, t.isBlocked FROM hr_keys AS t LEFT JOIN hr_keys_attribution AS ta ON ta.id_key=t.id LEFT JOIN hr_user AS pe ON pe.id=ta.id_user WHERE pe.id=:id";
    const SQL_GET_KEY2 = "SELECT * FROM hr_keys WHERE id=:id";

    const SQL_GET_UNATTRIBUTED_KEY = "SELECT id, identificator FROM hr_keys WHERE isUsed=0 AND identificator<>'??' ORDER BY identificator ";
    const SQL_SET_USED_KEY = "UPDATE hr_keys SET isUsed=:flag WHERE id=:id";

    const SQL_ATTRIBUTE_KEY = "INSERT INTO hr_keys_attribution (id_key, id_user) VALUES (:id_key, :id_user)";

    const SQL_GET_GROUPS3 = "SELECT  name FROM hr_user_group WHERE id=:id";
    const SQL_GET_GROUPS2 = "SELECT id, name FROM hr_user_group";
    const SQL_GET_GROUPS = "SELECT pg.id, pg.name, pg.comment FROM hr_user_group AS pg LEFT JOIN hr_user_group_attribution AS ga ON ga.id_group=pg.id LEFT JOIN hr_user AS pe ON pe.id=ga.id_user WHERE pe.id=:id";
    const SQL_GET_UNATTRIBUTED_GROUP = "SELECT * FROM hr_user_group WHERE id NOT IN (SELECT id_group AS id FROM hr_user_group_attribution WHERE id_user=:id) ORDER BY name";
    const SQL_ATTRIBUTE_GROUP = "INSERT INTO hr_user_group_attribution (id_group, id_user) VALUES (:id_group, :id_user)";
    const SQL_DELETE_GROUP_ATTRIBUTION = "DELETE FROM hr_user_group_attribution WHERE id_group=:id_group AND id_user=:id_user";

    const SQL_ADD_PERSON = "INSERT INTO hr_user (`name`,`firstname`,`street`,`city`,`country`,`zip`,`phone1`,`phone2`,`email1`,`email2`,`language`,`sex`,`department`,`firme`,`street_pr`,`npa_pr`,`city_pr`,`country_pr`,`picture`,`pin_code`,`password`, `fax`, `avs`, `masterAuthorization`) VALUES (:name,:firstname,:street,:city,:country,:zip,:phone1,:phone2,:email1,:email2,:language,:sex,:department,:firme,:street_pr,:npa_pr,:city_pr,:country_pr,:picture,:pin_code, :password, :fax, :avs, :masterAuthorization)";

    const SQL_GET_PERSON = "SELECT * FROM hr_user WHERE id=:id";
    const SQL_UPDATE_PERSON = "UPDATE hr_user SET `masterAuthorization`=:masterAuthorization, `avs`=:avs, `fax`=:fax, `password`=:password, `pin_code`=:pin_code,`name`=:name,`firstname`=:firstname,`street`=:street,`city`=:city,`country`=:country,`zip`=:zip,`phone1`=:phone1,`phone2`=:phone2,`email1`=:email1,`email2`=:email2,`language`=:language,`sex`=:sex,`department`=:department,`firme`=:firme,`street_pr`=:street_pr,`npa_pr`=:npa_pr,`city_pr`=:city_pr,`country_pr`=:country_pr,`picture`=:picture WHERE id=:id";


    const SQL_DELETE_PERSON = "DELETE FROM hr_user WHERE id=:id";
    const SQL_DELETE_ALL_GROUP_ATTRIBUTION = "DELETE FROM hr_user_group_attribution WHERE id_user=:id";
    const SQL_DELETE_KEY_ATTRIBUTION_FROM_IDPERSON = "DELETE FROM hr_keys_attribution WHERE id_user=:id";

    const SQL_UPDATE_KEYS_FOR_IDPERSON = "UPDATE hr_keys SET isUsed=0 WHERE id IN(SELECT id_key FROM hr_keys_attribution WHERE id_user=:id)";

    const SQL_GET_PICTURE = "SELECT picture FROM hr_user WHERE id=:id";


    const SQL_ADD_KEY = "INSERT INTO hr_keys (
                        `identificator` ,
                        `serialNumber`,
                        `isBlocked`,
                        `isUsed`
                  )
                  VALUES (
                        CONCAT('Key', ' - ', :serialNumber),
                        :serialNumber,
                        '0',
                        '1'
                  )";

    const SQL_ADD_KEY_SQLITE = "INSERT INTO hr_keys (
                        `identificator` ,
                        `serialNumber`,
                        `isBlocked`,
                        `isUsed`
                  )
                  VALUES (
                        'Key' || ' - ' || :serialNumber,
                        :serialNumber,
                        '0',
                        '1'
                  )";

}
