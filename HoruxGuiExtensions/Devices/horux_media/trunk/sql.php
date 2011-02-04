<?php

class SQL {
	
      const SQL_GET_ACCESSPOINT = "SELECT id, name FROM hr_device WHERE accessPoint=1";

	const SQL_ADD_INFODISPLAY = "INSERT INTO hr_horux_media (
                        `id_device` , 
                        `ip` , 
                        `port` , 
                        `id_action_device`
                  )
                  VALUES (
                        :id_device,
                        :ip,
                        :port,
                        :id_action_device
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
                        'horux_media',
                        :isLog,
                        0,
                        0,
                        :description,
			''
                  )";
                  
  	const SQL_IS_NAME_EXIST = "SELECT name FROM 
  								hr_device 
  								WHERE type='horux_media' AND name=:name";

	const SQL_GET_INFODISPLAY = "SELECT * FROM hr_horux_media AS ai LEFT JOIN hr_device AS d ON d.id=ai.id_device  WHERE ai.id_device=:id";
	
	const SQL_MOD_DEVICE =  "UPDATE hr_device SET
			            `name`=:name,
                                `isLog`=:isLog, 
                                `description`=:description
                                  WHERE id=:id"
                      ;
                      
	const SQL_UPDATE_INFODISPLAY =  "UPDATE hr_horux_media SET
                        `ip`=:ip, 
                        `port`=:port, 
                        `id_action_device`=:id_action_device
                        WHERE id_device=:id"
                      ;
                      
   	const SQL_IS_READER_NAME_EXIST2 = "SELECT name FROM 
  								hr_device
  								WHERE type='horux_media' AND name=:name AND id<>:id";
}

?>
