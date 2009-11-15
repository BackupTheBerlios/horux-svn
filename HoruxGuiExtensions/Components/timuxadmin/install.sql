-- --------------------------------------------------------

--
-- Structure de la table `hr_accessLink_ReaderTCPIP`
--

CREATE TABLE IF NOT EXISTS `hr_accessLink_ReaderTCPIP` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(40) NOT NULL DEFAULT '0',
  `port` int(11) NOT NULL,
  `id_device` int(11) NOT NULL DEFAULT '0',
  `outputTime1` int(11) NOT NULL,
  `outputTime2` int(11) NOT NULL,
  `outputTime3` int(11) NOT NULL,
  `outputTime4` int(11) NOT NULL,
  `antipassback` smallint(6) NOT NULL,
  `open_mode` set('NONE','NO_TIMEOUT','TIMEOUT','TIMEOUT_IN') NOT NULL DEFAULT 'NO_TIMEOUT',
  `open_mode_timeout` int(11) NOT NULL DEFAULT '0',
  `open_mode_input` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_access_level`
--

CREATE TABLE IF NOT EXISTS `hr_access_level` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `full_access` smallint(1) NOT NULL DEFAULT '0',
  `non_working_day` smallint(1) NOT NULL DEFAULT '0',
  `week_end` smallint(1) NOT NULL DEFAULT '0',
  `validity_date` date DEFAULT NULL,
  `validity_date_to` date DEFAULT NULL,
  `monday_default` smallint(6) NOT NULL DEFAULT '0',
  `comment` varchar(50) DEFAULT NULL,
  `locked` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_access_time`
--

CREATE TABLE IF NOT EXISTS `hr_access_time` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_access_level` bigint(20) NOT NULL DEFAULT '0',
  `day` varchar(10) NOT NULL DEFAULT '0',
  `from` int(11) NOT NULL DEFAULT '0',
  `until` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_alarms`
--

CREATE TABLE IF NOT EXISTS `hr_alarms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL DEFAULT '0',
  `datetime_` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `id_object` int(11) NOT NULL DEFAULT '0',
  `message` text NOT NULL,
  `checked` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_component`
--

CREATE TABLE IF NOT EXISTS `hr_component` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `id_install` smallint(6) NOT NULL,
  `parentmenu` smallint(6) NOT NULL,
  `menuname` varchar(30) NOT NULL,
  `page` varchar(100) NOT NULL,
  `iconmenu` varchar(200) NOT NULL,
  `locked` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_config`
--

CREATE TABLE IF NOT EXISTS `hr_config` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `xmlrpc_server` varchar(20) NOT NULL DEFAULT 'localhost',
  `xmlrpc_port` int(11) NOT NULL DEFAULT '1083',
  `log_path` varchar(100) NOT NULL,
  `debug_mode` smallint(1) NOT NULL,
  `server_path` varchar(50) NOT NULL,
  `user_method` varchar(30) NOT NULL,
  `key` varchar(10) NOT NULL,
  `mail_mailer` set('mail','sendmail','smtp') NOT NULL DEFAULT 'sendmail',
  `mail_mail_from` varchar(50) NOT NULL,
  `mail_from_name` varchar(50) NOT NULL,
  `mail_sendmail_path` varchar(50) NOT NULL DEFAULT '/usr/sbin/sendmail',
  `mail_smtp_auth` smallint(1) NOT NULL DEFAULT '0',
  `mail_smtp_safe` set('none','tls','ssl') NOT NULL DEFAULT 'none',
  `mail_smtp_username` varchar(50) NOT NULL,
  `mail_smtp_password` varchar(50) NOT NULL,
  `mail_smtp_host` varchar(50) NOT NULL DEFAULT 'localhost',
  `mail_smtp_port` int(11) NOT NULL DEFAULT '21',
  `publicurl` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_department`
--

CREATE TABLE IF NOT EXISTS `hr_department` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `locked` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_device`
--

CREATE TABLE IF NOT EXISTS `hr_device` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `accessPoint` smallint(1) NOT NULL,
  `type` varchar(40) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `isLog` smallint(1) NOT NULL,
  `locked` smallint(11) NOT NULL DEFAULT '0',
  `description` varchar(150) NOT NULL,
  `accessPlugin` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_gantner_standalone_action`
--

CREATE TABLE IF NOT EXISTS `hr_gantner_standalone_action` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` set('user','key','key_user','reason') NOT NULL,
  `func` set('add','sub') NOT NULL,
  `userId` int(11) NOT NULL DEFAULT '0',
  `keyId` int(11) NOT NULL DEFAULT '0',
  `deviceId` int(11) NOT NULL DEFAULT '0',
  `param` varchar(255) NOT NULL DEFAULT '',
  `param2` varchar(255) NOT NULL DEFAULT '',
  `reasonId` varchar(30) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_gantner_TimeTerminal`
--

CREATE TABLE IF NOT EXISTS `hr_gantner_TimeTerminal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_device` int(11) NOT NULL,
  `ipOrDhcp` varchar(40) NOT NULL,
  `isAutoRestart` int(1) NOT NULL,
  `autoRestart` time NOT NULL,
  `displayTimeout` int(11) NOT NULL DEFAULT '5000',
  `inputTimeout` int(11) NOT NULL DEFAULT '5000',
  `brightness` smallint(6) NOT NULL DEFAULT '50',
  `udpServer` int(1) NOT NULL,
  `checkBooking` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_gui_log`
--

CREATE TABLE IF NOT EXISTS `hr_gui_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `who` varchar(30) NOT NULL DEFAULT '',
  `what` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_gui_permissions`
--

CREATE TABLE IF NOT EXISTS `hr_gui_permissions` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `page` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `selector` enum('user_id','group_id') CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` smallint(5) unsigned DEFAULT NULL,
  `allowed` enum('','1') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `shortcut` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `selector` (`selector`,`value`,`page`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_helloworld`
--

CREATE TABLE IF NOT EXISTS `hr_helloworld` (
  `text` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_horux_keyboard`
--

CREATE TABLE IF NOT EXISTS `hr_horux_keyboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_device` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(40) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_install`
--

CREATE TABLE IF NOT EXISTS `hr_install` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` set('template','component','language','device') NOT NULL,
  `system` smallint(1) NOT NULL,
  `default` smallint(1) NOT NULL,
  `param` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_keys`
--

CREATE TABLE IF NOT EXISTS `hr_keys` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `identificator` varchar(30) NOT NULL DEFAULT '',
  `serialNumber` varchar(128) NOT NULL,
  `isUsed` smallint(1) NOT NULL DEFAULT '0',
  `isBlocked` smallint(1) NOT NULL DEFAULT '0',
  `locked` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `serialNumber` (`serialNumber`),
  KEY `identificator` (`identificator`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_keys_attribution`
--

CREATE TABLE IF NOT EXISTS `hr_keys_attribution` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_key` bigint(20) NOT NULL DEFAULT '0',
  `id_user` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_non_working_day`
--

CREATE TABLE IF NOT EXISTS `hr_non_working_day` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `from` date DEFAULT NULL,
  `until` date DEFAULT NULL,
  `comment` varchar(100) DEFAULT NULL,
  `locked` int(11) NOT NULL DEFAULT '0',
  `color` varchar(7) NOT NULL DEFAULT '#FF6666',
  `timeStart` time NOT NULL,
  `timeEnd` time NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_notification`
--

CREATE TABLE IF NOT EXISTS `hr_notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `emails` text NOT NULL,
  `description` varchar(255) NOT NULL,
  `locked` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_notification_code`
--

CREATE TABLE IF NOT EXISTS `hr_notification_code` (
  `id_notification` int(11) NOT NULL,
  `type` varchar(40) NOT NULL,
  `code` varchar(30) NOT NULL,
  `param` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_notification_su`
--

CREATE TABLE IF NOT EXISTS `hr_notification_su` (
  `id_notification` int(11) NOT NULL,
  `id_superuser` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_openTime`
--

CREATE TABLE IF NOT EXISTS `hr_openTime` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `non_working_day` smallint(1) NOT NULL DEFAULT '0',
  `week_end` smallint(1) NOT NULL DEFAULT '0',
  `validity_date` date DEFAULT NULL,
  `validity_date_to` date DEFAULT NULL,
  `monday_default` smallint(6) NOT NULL DEFAULT '0',
  `comment` varchar(50) DEFAULT NULL,
  `locked` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_openTime_attribution`
--

CREATE TABLE IF NOT EXISTS `hr_openTime_attribution` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_device` bigint(20) NOT NULL DEFAULT '0',
  `id_openTime` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_openTime_time`
--

CREATE TABLE IF NOT EXISTS `hr_openTime_time` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_openTime` bigint(20) NOT NULL DEFAULT '0',
  `day` varchar(10) NOT NULL DEFAULT '0',
  `from` int(11) NOT NULL DEFAULT '0',
  `until` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_site`
--

CREATE TABLE IF NOT EXISTS `hr_site` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `street` varchar(255) NOT NULL,
  `npa` varchar(10) NOT NULL,
  `city` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `fax` varchar(20) NOT NULL,
  `email` varchar(40) NOT NULL,
  `website` varchar(50) NOT NULL,
  `logo` varchar(50) NOT NULL,
  `tva_number` varchar(50) NOT NULL,
  `devise` varchar(5) NOT NULL,
  `tva` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_standalone_action_service`
--

CREATE TABLE IF NOT EXISTS `hr_standalone_action_service` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` set('add','sub') NOT NULL DEFAULT 'add',
  `serialNumber` varchar(128) NOT NULL DEFAULT '0',
  `rd_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_superusers`
--

CREATE TABLE IF NOT EXISTS `hr_superusers` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` smallint(5) unsigned NOT NULL,
  `user_id` smallint(5) unsigned DEFAULT '0',
  `name` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `isLogged` smallint(1) NOT NULL,
  `locked` int(11) NOT NULL DEFAULT '0',
  `session_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `lastConnection` datetime NOT NULL,
  `email` varchar(30) DEFAULT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_superuser_group`
--

CREATE TABLE IF NOT EXISTS `hr_superuser_group` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `superAdmin` smallint(1) NOT NULL DEFAULT '0',
  `description` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `locked` int(11) NOT NULL DEFAULT '0',
  `dispUserLoggedIn` int(1) NOT NULL,
  `dispLastAlarm` int(1) NOT NULL,
  `dispLastTracking` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_timux_activity_counter`
--

CREATE TABLE IF NOT EXISTS `hr_timux_activity_counter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `timecode_id` int(11) NOT NULL,
  `nbre` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_timux_booking`
--

CREATE TABLE IF NOT EXISTS `hr_timux_booking` (
  `tracking_id` int(11) NOT NULL,
  `action` int(11) NOT NULL,
  `actionReason` varchar(30) NOT NULL,
  `roundBooking` time NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_timux_config`
--

CREATE TABLE IF NOT EXISTS `hr_timux_config` (
  `daysByWeek` int(11) NOT NULL DEFAULT '5',
  `minimumBreaks` int(11) NOT NULL,
  `bookingRounding` int(11) NOT NULL,
  `hoursByWeek` int(11) NOT NULL,
  `holidayByYear` int(11) NOT NULL,
  `hourblocks` smallint(1) NOT NULL DEFAULT '0',
  `hoursBlockMorning1` time DEFAULT NULL,
  `hoursBlockMorning2` time DEFAULT NULL,
  `hoursBlockMorning3` time DEFAULT NULL,
  `hoursBlockMorning4` time DEFAULT NULL,
  `hoursBlockAfternoon1` time DEFAULT NULL,
  `hoursBlockAfternoon2` time DEFAULT NULL,
  `hoursBlockAfternoon3` time DEFAULT NULL,
  `hoursBlockAfternoon4` time DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_timux_request`
--

CREATE TABLE IF NOT EXISTS `hr_timux_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `type` set('leave','sign') NOT NULL,
  `state` set('draft','sended','validating','validate','canceled','refused','closed') NOT NULL,
  `createDate` date NOT NULL DEFAULT '0000-00-00',
  `modifyDate` date NOT NULL DEFAULT '0000-00-00',
  `modifyUserId` int(11) NOT NULL,
  `timecodeId` int(11) NOT NULL,
  `remark` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_timux_request_leave`
--

CREATE TABLE IF NOT EXISTS `hr_timux_request_leave` (
  `request_id` int(11) NOT NULL,
  `datefrom` date NOT NULL,
  `dateto` date NOT NULL,
  `period` set('allday','morning','afternoon') NOT NULL,
  PRIMARY KEY (`request_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_timux_request_workflow`
--

CREATE TABLE IF NOT EXISTS `hr_timux_request_workflow` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_timux_timeclass`
--

CREATE TABLE IF NOT EXISTS `hr_timux_timeclass` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `multiplier` float NOT NULL,
  `fromHour` time NOT NULL,
  `toHour` time NOT NULL,
  `locked` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_timux_timecode`
--

CREATE TABLE IF NOT EXISTS `hr_timux_timecode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `abbreviation` varchar(10) NOT NULL,
  `type` set('leave','absence') NOT NULL,
  `locked` int(11) NOT NULL,
  `useMinMax` int(1) NOT NULL,
  `minHour` int(11) NOT NULL,
  `maxHour` int(11) NOT NULL,
  `compensation` int(11) NOT NULL,
  `defaultHoliday` int(1) NOT NULL,
  `defaultOvertime` int(1) NOT NULL,
  `formatDisplay` set('hour','day') NOT NULL,
  `signtype` set('none','in','out','both') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `abbreviation` (`abbreviation`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_timux_timeunit`
--

CREATE TABLE IF NOT EXISTS `hr_timux_timeunit` (
  `device_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_timux_workflow`
--

CREATE TABLE IF NOT EXISTS `hr_timux_workflow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `locked` int(11) NOT NULL,
  `type` set('leave','sign') NOT NULL,
  `departmentId` int(11) NOT NULL,
  `validator1` int(11) NOT NULL,
  `validator11` int(11) NOT NULL,
  `validator12` int(11) NOT NULL,
  `validator2` int(11) NOT NULL,
  `validator21` int(11) NOT NULL,
  `validator22` int(11) NOT NULL,
  `validator3` int(11) NOT NULL,
  `validator31` int(11) NOT NULL,
  `validator32` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_timux_workingtime`
--

CREATE TABLE IF NOT EXISTS `hr_timux_workingtime` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `locked` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL,
  `workingPercent` float NOT NULL,
  `hoursByWeek` float NOT NULL,
  `mondayTime_m` float NOT NULL DEFAULT '0',
  `tuesdayTime_m` float NOT NULL DEFAULT '0',
  `wednesdayTime_m` float NOT NULL DEFAULT '0',
  `thursdayTime_m` float NOT NULL DEFAULT '0',
  `fridayTime_m` float NOT NULL DEFAULT '0',
  `saturdayTime_m` float NOT NULL DEFAULT '0',
  `sundayTime_m` float NOT NULL DEFAULT '0',
  `mondayTime_a` float NOT NULL,
  `tuesdayTime_a` float NOT NULL,
  `wednesdayTime_a` float NOT NULL,
  `thursdayTime_a` float NOT NULL,
  `fridayTime_a` float NOT NULL,
  `saturdayTime_a` float NOT NULL,
  `sundayTime_a` float NOT NULL,
  `startDate` date NOT NULL,
  `remark` text NOT NULL,
  `endOfActivity` smallint(1) NOT NULL DEFAULT '0',
  `hourblocks` smallint(1) NOT NULL DEFAULT '0',
  `holidaysByYear` float NOT NULL,
  `role` set('employee','manager','rh') NOT NULL DEFAULT 'employee',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_tracking`
--

CREATE TABLE IF NOT EXISTS `hr_tracking` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL DEFAULT '0',
  `id_key` int(11) NOT NULL DEFAULT '0',
  `time` time NOT NULL DEFAULT '00:00:00',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `id_entry` int(11) NOT NULL DEFAULT '0',
  `is_access` smallint(1) NOT NULL DEFAULT '1',
  `id_comment` int(11) NOT NULL DEFAULT '0',
  `key` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`,`date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_user`
--

CREATE TABLE IF NOT EXISTS `hr_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL,
  `firstname` varchar(30) DEFAULT NULL,
  `street` varchar(40) DEFAULT NULL,
  `city` varchar(30) DEFAULT NULL,
  `country` varchar(30) DEFAULT NULL,
  `zip` varchar(10) DEFAULT NULL,
  `phone1` varchar(16) DEFAULT NULL,
  `phone2` varchar(16) DEFAULT NULL,
  `email1` varchar(50) DEFAULT NULL,
  `email2` varchar(50) DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `language` varchar(30) DEFAULT NULL,
  `sex` enum('F','M') NOT NULL DEFAULT 'F',
  `validity_date` date NOT NULL DEFAULT '0000-00-00',
  `isBlocked` smallint(1) NOT NULL DEFAULT '0',
  `locked` int(11) NOT NULL DEFAULT '0',
  `department` int(11) DEFAULT NULL,
  `firme` varchar(30) DEFAULT NULL,
  `street_pr` varchar(30) DEFAULT NULL,
  `npa_pr` varchar(10) DEFAULT NULL,
  `city_pr` varchar(30) DEFAULT NULL,
  `country_pr` varchar(30) DEFAULT NULL,
  `pin_code` varchar(12) DEFAULT NULL,
  `password` varchar(64) DEFAULT NULL,
  `fax` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `firstname` (`firstname`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_user_action`
--

CREATE TABLE IF NOT EXISTS `hr_user_action` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `page` varchar(100) NOT NULL,
  `icon` varchar(100) NOT NULL,
  `tip` varchar(255) NOT NULL,
  `catalog` varchar(30) NOT NULL,
  `type` set('userList','userWizardTpl','module') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_user_group`
--

CREATE TABLE IF NOT EXISTS `hr_user_group` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '',
  `comment` varchar(50) DEFAULT NULL,
  `locked` int(11) NOT NULL DEFAULT '0',
  `accessPlugin` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_user_group_access`
--

CREATE TABLE IF NOT EXISTS `hr_user_group_access` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_group` bigint(20) NOT NULL DEFAULT '0',
  `id_device` bigint(20) NOT NULL DEFAULT '0',
  `id_access_level` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_user_group_attribution`
--

CREATE TABLE IF NOT EXISTS `hr_user_group_attribution` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_user` bigint(20) NOT NULL DEFAULT '0',
  `id_group` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_vp_parking`
--

CREATE TABLE IF NOT EXISTS `hr_vp_parking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `area` int(11) NOT NULL DEFAULT '0',
  `display_id` int(11) NOT NULL DEFAULT '0',
  `default_msg` varchar(255) NOT NULL,
  `access_ok_msg` varchar(255) NOT NULL,
  `access_ko_msg` varchar(255) NOT NULL,
  `displayTime` smallint(6) NOT NULL DEFAULT '4',
  `accesspoint_id` int(11) NOT NULL DEFAULT '0',
  `lightinfo_id` int(11) NOT NULL DEFAULT '0',
  `lightinfo_io` smallint(6) NOT NULL,
  `filling` int(11) NOT NULL DEFAULT '0',
  `locked` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_vp_subscription`
--

CREATE TABLE IF NOT EXISTS `hr_vp_subscription` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `validity` varchar(30) NOT NULL,
  `credit` int(11) NOT NULL,
  `locked` int(11) NOT NULL,
  `price` float NOT NULL,
  `start` set('immediatly','firstaccess') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_vp_subscription_attribution`
--

CREATE TABLE IF NOT EXISTS `hr_vp_subscription_attribution` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `subcription_id` int(11) NOT NULL DEFAULT '0',
  `create_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` set('not_start','started','finished') NOT NULL DEFAULT 'not_start',
  `credit` int(11) NOT NULL DEFAULT '0',
  `start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `create_by` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `start` (`start`,`end`,`user_id`,`subcription_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
