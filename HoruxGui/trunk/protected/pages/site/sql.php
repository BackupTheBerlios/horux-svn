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

  const SQL_UPDATE_SITE =  "UPDATE hr_site SET
                        `name`=:name,
                        `logo`=:logo,
                        `street`=:street,
                        `npa`=:npa,
                        `city`=:city,
                        `phone`=:phone,
                        `fax`=:fax,
                        `email`=:email,
                        `website`=:website,
						`tva_number`=:tva_number,
						`tva`=:tva,
						`devise`=:devise
                        WHERE id=1"
                      ;
                      
	const SQL_GET_SITE = "SELECT * FROM hr_site WHERE id=1";
}
