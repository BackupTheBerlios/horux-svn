<?php


class SQL {

	const SQL_ADD_LCDDISPLAY = "INSERT INTO hr_horux_lcddisplay (
                        `ip` , 
                        `port` , 
                        `id_device`,
                        `messageTimerDisplay`,
                        `defaultMessage`
                  )
                  VALUES (
                        :ip,
                        :port,
                        :id_device,
                        :messageTimerDisplay,
                        :defaultMessage
                  )";


	const SQL_UPDATE_LCDDISPLAY =  "UPDATE hr_horux_lcddisplay SET
                        `ip`=:ip, 
                        `port`=:port,
                        `messageTimerDisplay`=:messageTimerDisplay,
                        `defaultMessage`=:defaultMessage
                        WHERE id_device=:id"
                      ;
                      
}

?>
