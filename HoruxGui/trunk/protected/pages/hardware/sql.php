<?php


class SQL {
    const SQL_GET_HARDWARE_ALL = "SELECT * FROM hr_device";
    const SQL_GET_HARDWARE_ACCESSPOINT = "SELECT * FROM hr_device WHERE accessPoint=1";
    const SQL_GET_HARDWARE_OTHERS = "SELECT * FROM hr_device WHERE accessPoint=0";
}

?>
