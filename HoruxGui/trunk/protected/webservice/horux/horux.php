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

class horux
{
    /**
     * @param int $id id of the user to be return
     * @return mixed Return the user data
     * @soapmethod
     */
    public function getUserById($id)
    {
        $app = Prado::getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;
        $db->Active=true;

        $sql = "SELECT
                    u.id,
                    u.name,
                    u.firstname,
                    u.picture,
                    u.language,
                    u.sex,
                    u.validity_date,
                    u.isBlocked,
                    d.name AS department,
                    u.pin_code,
                    u.street AS street_private,
                    u.city AS city_private,
                    u.country AS country_private,
                    u.zip AS zip_private,
                    u.phone1 AS phone_private,
                    u.email1 AS email_private,
                    u.firme,
                    u.street_pr AS street_professional,
                    u.city_pr AS city_professional,
                    u.country_pr AS country_professional,
                    u.npa_pr AS zip_professional,
                    u.phone2 AS phone_professional,
                    u.email2 AS email_professional,
                    u.fax AS fax_professional
                 FROM hr_user AS u 
                 LEFT JOIN hr_department AS d ON u.department = d.id
                 WHERE u.id=$id";

        $cmd= $db->createCommand($sql);
        $data = $cmd->query();
        $data = $data->read();

        return $data;
    }

    /**
     * @return mixed Return the users data
     * @soapmethod
     */
    public function getAllUser()
    {
        $app = Prado::getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;
        $db->Active=true;

        $sql = "SELECT
                    id,
                    name,
                    firstname,
                    picture,
                    language,
                    sex,
                    validity_date,
                    isBlocked,
                    department AS department_id,
                    pin_code,
                    street AS street_private,
                    city AS city_private,
                    country AS country_private,
                    zip AS zip_private,
                    phone1 AS phone_private,
                    email1 AS email_private,
                    firme,
                    street_pr AS street_professional,
                    city_pr AS city_professional,
                    country_pr AS country_professional,
                    npa_pr AS zip_professional,
                    phone2 AS phone_professional,
                    email2 AS email_professional,
                    fax AS fax_professional
                FROM hr_user WHERE id>1 ORDER BY name, firstname";

        $cmd= $db->createCommand($sql);
        $data = $cmd->query();
        $data = $data->readAll();

        return $data;
    }
}

?>
