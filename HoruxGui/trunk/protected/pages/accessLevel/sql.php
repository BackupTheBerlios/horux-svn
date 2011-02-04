<?php

class SQL {

    const SQL_GET_ALL_ACCESS_LEVEL = "SELECT * FROM hr_access_level";
    const SQL_GET_ACCESS_LEVEL_ID = "SELECT * FROM hr_access_level WHERE id=:id";
    const SQL_GET_ACCESS_TIME_ID = "SELECT * FROM hr_access_time WHERE id_access_level=:id";
    const SQL_IS_ACCESS_LEVEL_NAME_EXIST = "SELECT * FROM hr_access_level WHERE name=:name";
    const SQL_IS_ACCESS_LEVEL_NAME_EXIST_EXCEPT_ID = "SELECT * FROM hr_access_level WHERE name=:name AND id!=:id";


    const SQL_ADD_ACCESS_LEVEL = "INSERT INTO hr_access_level (
                        `name` ,
                        `full_access`,
                        `non_working_day`,
                        `monday_default`,
                        `comment`
                  )
                  VALUES (
                        :name,
                        :full_access,
                        :non_working_day,
                        :monday_default,
                        :comment
                  )";

    const SQL_MOD_ACCESS_LEVEL = "UPDATE hr_access_level SET
                        `name`=:name,
                        `full_access`=:full_access,
                        `non_working_day`=:non_working_day,
                        `monday_default`=:monday_default,
                        `comment`=:comment
                        WHERE id=:id"
    ;


    const SQL_ADD_ACCESS_LEVEL_TIME = "INSERT INTO hr_access_time (
                        `id_access_level` ,
                        `day`,
                        `from`,
                        `until`,
                        `pinCodeNecessary`,
                        `specialRelayPlan`,
                        `exitingOnly`

                  )
                  VALUES (
                        :id_access_level,
                        :day,
                        :from,
                        :until,
                        :pinCodeNecessary,
                        :specialRelayPlan,
                        :exitingOnly
                  )";

    const SQL_REMOVE_ACCESS_LEVEL = "DELETE FROM hr_access_level WHERE id=:id";
    const SQL_REMOVE_ACCESS_TIME = "DELETE FROM hr_access_time WHERE id_access_level=:id";
}
