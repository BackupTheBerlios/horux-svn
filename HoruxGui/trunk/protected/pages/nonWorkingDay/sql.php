<?php


class SQL 
{
    const SQL_ADD_NONWORKINGDAY = "INSERT INTO hr_non_working_day (
                        `name` ,
                        `from` ,
                        `until`,
                        `color`,
                        `comment`,
                        `period`
                  )
                  VALUES (
                        :name,
                        :from,
                        :until,
                        :color,
                        :comment,
                        :period
                  )";

    const SQL_GET_NONWORKINGDAY = "SELECT * FROM hr_non_working_day WHERE id=:id";

    const SQL_UPDATE_NONWORKINGDAY = "UPDATE hr_non_working_day SET `name`=:name, `until`=:until, `from`=:from, `color`=:color, `comment`=:comment, `period`=:period  WHERE id=:id";

    const SQL_DELETE_NONWORKINGDAY = "DELETE FROM hr_non_working_day WHERE id=:id";
}
