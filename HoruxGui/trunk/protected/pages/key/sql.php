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

  const SQL_SELECT_SETBLOCK_KEY = "UPDATE hr_keys SET isBlocked=:flag WHERE id=:id";

  const SQL_IS_SERIALNUMBER_EXIST = "SELECT * FROM hr_keys WHERE serialNumber=:serialNumber";
  const SQL_IS_SERIALNUMBER_EXIST_EXCEPT_ID = "SELECT * FROM hr_keys WHERE serialNumber=:serialNumber AND id!=:id";


  const SQL_IS_IDENTIFICATOR_EXIST = "SELECT * FROM hr_keys WHERE identificator=:identificator";
  const SQL_IS_IDENTIFICATOR_EXIST_EXCEPT_ID = "SELECT * FROM hr_keys WHERE identificator=:identificator AND id!=:id";
  
  const SQL_GET_KEY = "SELECT * FROM hr_keys WHERE id=:id";
  
  const SQL_ADD_KEY = "INSERT INTO hr_keys (
                        `identificator` , 
                        `serialNumber`,
                        `isBlocked`,
                        `isUsed`
                  )
                  VALUES (
                        :identificator,
                        :serialNumber,
                        :isBlocked,
                        :isUsed
                  )";
                  
  const SQL_MOD_KEY =  "UPDATE hr_keys SET
                        `identificator`=:identificator,
                        `serialNumber`=:serialNumber,
                        `isBlocked`=:isBlocked,
                        `isUsed`=:isUsed
                        WHERE id=:id"
                      ;
                      
  const SQL_REMOVE_TAG = "DELETE FROM hr_keys WHERE id=:id";
  
  const SQL_REMOVE_TAG_ATTRIBUTION = "DELETE FROM hr_keys_attribution WHERE id_key=:id";
  
  const SQL_GET_PERSON = "SELECT id AS Value, CONCAT(name, ' ', firstname) AS Text FROM hr_user WHERE name<>'??'";
  const SQL_GET_PERSON_SQLITE = "SELECT id AS Value, name || ' ' || firstname AS Text FROM hr_user WHERE name<>'??'";
  
  const SQL_GET_ATTRIBUTION = "SELECT pe.id FROM hr_user AS pe LEFT JOIN hr_keys_attribution AS ta ON ta.id_user=pe.id WHERE ta.id_key=:id";
  
  const SQL_ADD_TAG_ATTRIBUTION = "INSERT INTO hr_keys_attribution (
                        `id_key` , 
                        `id_user`
                  )
                  VALUES (
                        :id_key,
                        :id_user
                  )"; 
}
