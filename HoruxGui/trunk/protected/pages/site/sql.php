<?php

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
