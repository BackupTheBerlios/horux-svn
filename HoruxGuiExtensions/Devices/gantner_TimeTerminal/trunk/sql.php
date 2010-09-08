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

    const SQL_GET_ACCESSPOINT = "SELECT id, name FROM hr_device WHERE accessPoint=1";

    const SQL_ADD_GANTNERTERMINAL = "INSERT INTO hr_gantner_TimeTerminal (
                        `id_device` ,
                        `ipOrDhcp` ,
                        `isAutoRestart` ,
                        `autoRestart` ,
                        `displayTimeout`,
                        `inputTimeout`,
                        `brightness`,
                        `udpServer`,
                        `checkBooking`,
                        `language`,
                        `autoBooking`,
                        `inputDBEText1`,
                        `inputDBEText2`,
                        `inputDBEText3`,
                        `inputDBEText4`,
                        `inputDBEText5`,
                        `inputDBEText6`,
                        `inputDBEText7`,
                        `inputDBEText8`,
                        `inputDBEText9`,
                        `inputDBEText10`,
                        `inputDBEText11`,
                        `inputDBEText12`,
                        `inputDBEText13`,
                        `inputDBEText14`,
                        `inputDBEText15`,
                        `inputDBEText16`,
                        `inputDBEText17`,
                        `inputDBEText18`,
                        `inputDBEText19`,
                        `inputDBEText20`,
                        `inputDBECheck1`,
                        `inputDBECheck2`,
                        `inputDBECheck3`,
                        `inputDBECheck4`,
                        `inputDBECheck5`,
                        `inputDBECheck6`,
                        `inputDBECheck7`,
                        `inputDBECheck8`,
                        `inputDBECheck9`,
                        `inputDBECheck10`,
                        `inputDBECheck11`,
                        `inputDBECheck12`,
                        `inputDBECheck13`,
                        `inputDBECheck14`,
                        `inputDBECheck15`,
                        `inputDBECheck16`,
                        `inputDBECheck17`,
                        `inputDBECheck18`,
                        `inputDBECheck19`,
                        `inputDBECheck20`,
                        `inputDBEFormat1`,
                        `inputDBEFormat2`,
                        `inputDBEFormat3`,
                        `inputDBEFormat4`,
                        `inputDBEFormat5`,
                        `inputDBEFormat6`,
                        `inputDBEFormat7`,
                        `inputDBEFormat8`,
                        `inputDBEFormat9`,
                        `inputDBEFormat10`,
                        `inputDBEFormat11`,
                        `inputDBEFormat12`,
                        `inputDBEFormat13`,
                        `inputDBEFormat14`,
                        `inputDBEFormat15`,
                        `inputDBEFormat16`,
                        `inputDBEFormat17`,
                        `inputDBEFormat18`,
                        `inputDBEFormat19`,
                        `inputDBEFormat20`
                  )
                  VALUES (
                        :id_device ,
                        :ipOrDhcp,
                        :isAutoRestart,
                        :autoRestart,
                        :displayTimeout,
                        :inputTimeout,
                        :brightness,
                        :udpServer,
                        :checkBooking,
                        :language,
                        :autoBooking,
                        :inputDBEText1
                        :inputDBEText2
                        :inputDBEText3
                        :inputDBEText4
                        :inputDBEText5
                        :inputDBEText6
                        :inputDBEText7
                        :inputDBEText8
                        :inputDBEText9
                        :inputDBEText10
                        :inputDBEText11
                        :inputDBEText12
                        :inputDBEText13
                        :inputDBEText14
                        :inputDBEText15
                        :inputDBEText16
                        :inputDBEText17
                        :inputDBEText18
                        :inputDBEText19
                        :inputDBEText20,
                        :inputDBECheck1,
                        :inputDBECheck2,
                        :inputDBECheck3,
                        :inputDBECheck4,
                        :inputDBECheck5,
                        :inputDBECheck6,
                        :inputDBECheck7,
                        :inputDBECheck8,
                        :inputDBECheck9,
                        :inputDBECheck10,
                        :inputDBECheck11,
                        :inputDBECheck12,
                        :inputDBECheck13,
                        :inputDBECheck14,
                        :inputDBECheck15,
                        :inputDBECheck16,
                        :inputDBECheck17,
                        :inputDBECheck18,
                        :inputDBECheck19,
                        :inputDBECheck20,
                        :inputDBEFormat1,
                        :inputDBEFormat2,
                        :inputDBEFormat3,
                        :inputDBEFormat4,
                        :inputDBEFormat5,
                        :inputDBEFormat6,
                        :inputDBEFormat7,
                        :inputDBEFormat8,
                        :inputDBEFormat9,
                        :inputDBEFormat10,
                        :inputDBEFormat11,
                        :inputDBEFormat12,
                        :inputDBEFormat13,
                        :inputDBEFormat14,
                        :inputDBEFormat15,
                        :inputDBEFormat16,
                        :inputDBEFormat17,
                        :inputDBEFormat18,
                        :inputDBEFormat19,
                        :inputDBEFormat20
                  )";


    const SQL_ADD_DEVICE = "INSERT INTO hr_device (
                        `name` , 
                        `accessPoint` , 
                        `type` , 
                        `isLog` , 
                        `locked` , 
                        `parent_id` , 
                        `description`,
                        `accessPlugin`
                  )
                  VALUES (
                        :name,
                        1,
                        'gantner_TimeTerminal',
                        :isLog,
                        0,
                        0,
                        :description,
                        ''
                  )";

    const SQL_IS_NAME_EXIST = "SELECT name FROM
  								hr_device 
  								WHERE type='hr_gantner_TimeTerminal' AND name=:name";

    const SQL_GET_GANTNERTERMINAL = "SELECT * FROM hr_gantner_TimeTerminal AS ai LEFT JOIN hr_device AS d ON d.id=ai.id_device  WHERE ai.id_device=:id";

    const SQL_MOD_DEVICE =  "UPDATE hr_device SET
			            `name`=:name,
                                `isLog`=:isLog, 
                                `description`=:description
                                  WHERE id=:id"
    ;

    const SQL_UPDATE_GANTNERTERMINAL  =  "UPDATE hr_gantner_TimeTerminal SET
                        `ipOrDhcp`=:ipOrDhcp ,
                        `isAutoRestart`=:isAutoRestart ,
                        `autoRestart`=:autoRestart ,
                        `displayTimeout`=:displayTimeout,
                        `inputTimeout`=:inputTimeout,
                        `brightness`=:brightness,
                        `udpServer`=:udpServer,
                        `checkBooking`=:checkBooking,
                        `language`=:language,
                        `autoBooking`=:autoBooking,                        
                        `inputDBECheck1`=:inputDBECheck1,
                        `inputDBECheck2`=:inputDBECheck2,
                        `inputDBECheck3`=:inputDBECheck3,
                        `inputDBECheck4`=:inputDBECheck4,
                        `inputDBECheck5`=:inputDBECheck5,
                        `inputDBECheck6`=:inputDBECheck6,
                        `inputDBECheck7`=:inputDBECheck7,
                        `inputDBECheck8`=:inputDBECheck8,
                        `inputDBECheck9`=:inputDBECheck9,
                        `inputDBECheck10`=:inputDBECheck10,
                        `inputDBECheck11`=:inputDBECheck11,
                        `inputDBECheck12`=:inputDBECheck12,
                        `inputDBECheck13`=:inputDBECheck13,
                        `inputDBECheck14`=:inputDBECheck14,
                        `inputDBECheck15`=:inputDBECheck15,
                        `inputDBECheck16`=:inputDBECheck16,
                        `inputDBECheck17`=:inputDBECheck17,
                        `inputDBECheck18`=:inputDBECheck18,
                        `inputDBECheck19`=:inputDBECheck19,
                        `inputDBECheck20`=:inputDBECheck20,
                        `inputDBEText1`=:inputDBEText1,
                        `inputDBEText2`=:inputDBEText2,
                        `inputDBEText3`=:inputDBEText3,
                        `inputDBEText4`=:inputDBEText4,
                        `inputDBEText5`=:inputDBEText5,
                        `inputDBEText6`=:inputDBEText6,
                        `inputDBEText7`=:inputDBEText7,
                        `inputDBEText8`=:inputDBEText8,
                        `inputDBEText9`=:inputDBEText9,
                        `inputDBEText10`=:inputDBEText10,
                        `inputDBEText11`=:inputDBEText11,
                        `inputDBEText12`=:inputDBEText12,
                        `inputDBEText13`=:inputDBEText13,
                        `inputDBEText14`=:inputDBEText14,
                        `inputDBEText15`=:inputDBEText15,
                        `inputDBEText16`=:inputDBEText16,
                        `inputDBEText17`=:inputDBEText17,
                        `inputDBEText18`=:inputDBEText18,
                        `inputDBEText19`=:inputDBEText19,
                        `inputDBEText20`=:inputDBEText20,
                        `inputDBEFormat1`=:inputDBEFormat1,
                        `inputDBEFormat2`=:inputDBEFormat2,
                        `inputDBEFormat3`=:inputDBEFormat3,
                        `inputDBEFormat4`=:inputDBEFormat4,
                        `inputDBEFormat5`=:inputDBEFormat5,
                        `inputDBEFormat6`=:inputDBEFormat6,
                        `inputDBEFormat7`=:inputDBEFormat7,
                        `inputDBEFormat8`=:inputDBEFormat8,
                        `inputDBEFormat9`=:inputDBEFormat9,
                        `inputDBEFormat10`=:inputDBEFormat10,
                        `inputDBEFormat11`=:inputDBEFormat11,
                        `inputDBEFormat12`=:inputDBEFormat12,
                        `inputDBEFormat13`=:inputDBEFormat13,
                        `inputDBEFormat14`=:inputDBEFormat14,
                        `inputDBEFormat15`=:inputDBEFormat15,
                        `inputDBEFormat16`=:inputDBEFormat16,
                        `inputDBEFormat17`=:inputDBEFormat17,
                        `inputDBEFormat18`=:inputDBEFormat18,
                        `inputDBEFormat19`=:inputDBEFormat19,
                        `inputDBEFormat20`=:inputDBEFormat20
                        WHERE id_device=:id"
    ;

    const SQL_IS_READER_NAME_EXIST2 = "SELECT name FROM
  								hr_device
  								WHERE type='hr_gantner_TimeTerminal' AND name=:name AND id<>:id";

    const SQL_GET_KEY = "SELECT * FROM hr_gantner_TimeTerminal_key WHERE device_id=:id";

    const SQL_REMOVE_KEY = "DELETE FROM hr_gantner_TimeTerminal_key WHERE device_id=:id";

    const SQL_ADD_KEY = "INSERT INTO hr_gantner_TimeTerminal_key (
                        `device_id` ,
                        `type` ,
                        `key` ,
                        `text` ,
                        `dialog`,
                        `params`
                  )
                  VALUES (
                        :id,
                        :type,
                        :key,
                        :text,
                        :dialog,
                        :params
                  )";
}

?>
