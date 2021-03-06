<?php

class SQL {

	const SQL_ADD_DEVICE = "INSERT INTO hr_moxa_iologic (
                        `ip` , 
                        `port` , 
                        `id_device`,
                        `password`,
                        `initialOutput`,
                        `output0_func`,
                        `output1_func`,
                        `output2_func`,
                        `output3_func`,
                        `output4_func`,
                        `output5_func`,
                        `output6_func`,
                        `output7_func`,
                        `output0Time`,
                        `output1Time`,
                        `output2Time`,
                        `output3Time`,
                        `output4Time`,
                        `output5Time`,
                        `output6Time`,
                        `output7Time`
                  )
                  VALUES (
                        :ip,
                        :port,
                        :id_device,
                        :password,
                        :initialOutput,
                        :output0_func,
                        :output1_func,
                        :output2_func,
                        :output3_func,
                        :output4_func,
                        :output5_func,
                        :output6_func,
                        :output7_func,
                        :output0Time,
                        :output1Time,
                        :output2Time,
                        :output3Time,
                        :output4Time,
                        :output5Time,
                        :output6Time,
                        :output7Time
                  )";

                     
	const SQL_UPDATE_DEVICE =  "UPDATE hr_moxa_iologic SET
                        `ip`=:ip, 
                        `port`=:port,
                        `password`=:password,
                        `initialOutput`=:initialOutput,
                        `output0_func` =:output0_func,
                        `output1_func` =:output1_func,
                        `output2_func` =:output2_func,
                        `output3_func` =:output3_func,
                        `output4_func` =:output4_func,
                        `output5_func` =:output5_func,
                        `output6_func` =:output6_func,
                        `output7_func` =:output7_func,
                        `output0Time`  =:output0Time,
                        `output1Time`  =:output1Time,
                        `output2Time`  =:output2Time,
                        `output3Time`  =:output3Time,
                        `output4Time`  =:output4Time,
                        `output5Time`  =:output5Time,
                        `output6Time`  =:output6Time,
                        `output7Time`  =:output7Time
                        WHERE id_device=:id"
                      ;
                      
}

?>
