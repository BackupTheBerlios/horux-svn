<?php
/**
* @version      $Id$
* @package      Horux
* @subpackage   Horux
* @copyright    Copyright (C) 2007  Letux. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* Horux is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/


class SQL {

  const SQL_UPDATE_CONFIG =  "UPDATE hr_config SET
                        `xmlrpc_server`=:xmlrpc_server,
                        `xmlrpc_port`=:xmlrpc_port,
                        `log_path`=:log_path,
                        `debug_mode`=:debug_mode,

                        `mail_mailer`=:mail_mailer,
                        `mail_mail_from`=:mail_mail_from,
                        `mail_from_name`=:mail_from_name,
                        `mail_sendmail_path`=:mail_sendmail_path,
                        `mail_smtp_auth`=:mail_smtp_auth,
                        `mail_smtp_safe`=:mail_smtp_safe,
                        `mail_smtp_username`=:mail_smtp_username,
                        `mail_smtp_password`=:mail_smtp_password,
                        `mail_smtp_host`=:mail_smtp_host,
                        `mail_smtp_port`=:mail_smtp_port,


                        `key`=:key
                        WHERE id=1"
                      ;
                      
	const SQL_GET_CONFIG = "SELECT * FROM hr_config WHERE id=1";
}
