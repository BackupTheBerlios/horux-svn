--
-- Structure de la table `hr_gantner_standalone_action`
--

CREATE TABLE IF NOT EXISTS `hr_gantner_standalone_action` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` set('user','key','key_user','reason','balancesText','balances','reinit') NOT NULL,
  `func` set('add','sub') NOT NULL,
  `userId` int(11) NOT NULL DEFAULT '0',
  `keyId` int(11) NOT NULL DEFAULT '0',
  `deviceId` int(11) NOT NULL DEFAULT '0',
  `param` varchar(255) NOT NULL DEFAULT '',
  `param2` varchar(255) NOT NULL DEFAULT '',
  `param3` varchar(255) NOT NULL,
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
  `language` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `hr_gantner_TimeTerminal_key`
--

CREATE TABLE IF NOT EXISTS `hr_gantner_TimeTerminal_key` (
  `device_id` int(11) NOT NULL,
  `type` set('fixed','soft') NOT NULL,
  `key` smallint(6) NOT NULL,
  `text` varchar(255) NOT NULL,
  `dialog` varchar(255) NOT NULL,
  KEY `device_id` (`device_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
