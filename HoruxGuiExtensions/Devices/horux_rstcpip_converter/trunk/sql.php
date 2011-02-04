<?php

class SQL {

	const SQL_ADD_DEVICE = "INSERT INTO hr_horux_rstcpip_converter (
                        `ip` , 
                        `port` , 
                        `id_device`
                  )
                  VALUES (
                        :ip,
                        :port,
                        :id_device
                  )";


                      
	const SQL_UPDATE_DEVICE =  "UPDATE hr_horux_rstcpip_converter SET
                        `ip`=:ip, 
                        `port`=:port
                        WHERE id_device=:id"
                      ;                      
}

?>
