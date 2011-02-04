<?php

class SQL {

    const SQL_ADD_DEVICE = "INSERT INTO hr_a3m_lgm (
                    `address` ,
                    `id_device`,
                    `serialNumberFormat`
              )
              VALUES (
                    :address,
                    :id_device,
                    :serialNumberFormat
              )";
    const SQL_UPDATE_DEVICE =  "UPDATE hr_a3m_lgm SET
                    `address`=:address,
                    `serialNumberFormat`=:serialNumberFormat
                    WHERE id_device=:id"
                  ;
}

?>
