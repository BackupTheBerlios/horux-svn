--
-- Structure de la table `hr_timux_activity_counter`
--

CREATE TABLE IF NOT EXISTS `hr_timux_activity_counter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `timecode_id` int(11) NOT NULL,
  `nbre` float NOT NULL,
  `year` int(11) NOT NULL DEFAULT '0',
  `month` int(11) NOT NULL DEFAULT '0',
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
  `roundBooking` time NOT NULL,
  `internet` smallint(1) NOT NULL DEFAULT '0',
  `closed` smallint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tracking_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_timux_closed_month`
--

CREATE TABLE IF NOT EXISTS `hr_timux_closed_month` (
  `user_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `hours` float NOT NULL,
  `absences` float NOT NULL,
  `hoursByMonth` float NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_timux_config`
--

CREATE TABLE IF NOT EXISTS `hr_timux_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `daysByWeek` int(11) NOT NULL DEFAULT '5',
  `minimumBreaks` int(11) NOT NULL,
  `bookingRounding` int(11) NOT NULL,
  `hoursByWeek` int(11) NOT NULL,
  `holidayByYear` int(11) NOT NULL,
  `hoursBlockMorning1` time DEFAULT NULL,
  `hoursBlockMorning2` time DEFAULT NULL,
  `hoursBlockMorning3` time DEFAULT NULL,
  `hoursBlockMorning4` time DEFAULT NULL,
  `hoursBlockAfternoon1` time DEFAULT NULL,
  `hoursBlockAfternoon2` time DEFAULT NULL,
  `hoursBlockAfternoon3` time DEFAULT NULL,
  `hoursBlockAfternoon4` time DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_timux_request`
--

CREATE TABLE IF NOT EXISTS `hr_timux_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `type` set('leave','sign') NOT NULL DEFAULT 'leave',
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
  `user_id` int(11) NOT NULL,
  `validatorLevel` smallint(6) NOT NULL
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
  `type` set('leave','absence','overtime','load') NOT NULL,
  `locked` int(11) NOT NULL,
  `useMinMax` int(1) NOT NULL,
  `minHour` int(11) NOT NULL,
  `maxHour` int(11) NOT NULL,
  `defaultHoliday` int(1) NOT NULL,
  `defaultOvertime` int(1) NOT NULL,
  `formatDisplay` set('hour','day') NOT NULL,
  `signtype` set('none','in','out','both') NOT NULL,
  `timeworked` smallint(1) unsigned NOT NULL DEFAULT '0',
  `deviceDisplay` varchar(255) NOT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#36c',
  PRIMARY KEY (`id`),
  KEY `abbreviation` (`abbreviation`),
  KEY `type` (`type`)
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
  `holidaysByYear` float NOT NULL,
  `role` set('employee','manager','rh') NOT NULL DEFAULT 'employee',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `hr_user_action` (`name`, `page`, `icon`, `tip`, `catalog`, `type`) VALUES
('Super user', 'protected/pages/components/timuxadmin/wizard.tpl', '', '', 'timuxadmin', 'userWizardTpl'),
('TimuxModule', 'components.timuxadmin.TimuxModule', '', '', 'timuxadmin', 'module');

