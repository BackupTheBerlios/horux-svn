<?php

class SQL {

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
                        :inputDBEText1,
                        :inputDBEText2,
                        :inputDBEText3,
                        :inputDBEText4,
                        :inputDBEText5,
                        :inputDBEText6,
                        :inputDBEText7,
                        :inputDBEText8,
                        :inputDBEText9,
                        :inputDBEText10,
                        :inputDBEText11,
                        :inputDBEText12,
                        :inputDBEText13,
                        :inputDBEText14,
                        :inputDBEText15,
                        :inputDBEText16,
                        :inputDBEText17,
                        :inputDBEText18,
                        :inputDBEText19,
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
