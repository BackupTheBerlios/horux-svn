--
-- Structure de la table `hr_gantner_standalone_action`
--

CREATE TABLE IF NOT EXISTS `hr_gantner_standalone_action` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` set('user','key','key_user','reason','balancesText','balances','reinit','load') NOT NULL,
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
  `autoBooking` smallint(1) NOT NULL,
  `inputDBEText1` varchar(255) DEFAULT NULL,
  `inputDBEText2` varchar(255) DEFAULT NULL,
  `inputDBEText3` varchar(255) DEFAULT NULL,
  `inputDBEText4` varchar(255) DEFAULT NULL,
  `inputDBEText5` varchar(255) DEFAULT NULL,
  `inputDBEText6` varchar(255) DEFAULT NULL,
  `inputDBEText7` varchar(255) DEFAULT NULL,
  `inputDBEText8` varchar(255) DEFAULT NULL,
  `inputDBEText9` varchar(255) DEFAULT NULL,
  `inputDBEText10` varchar(255) DEFAULT NULL,
  `inputDBEText11` varchar(255) DEFAULT NULL,
  `inputDBEText12` varchar(255) DEFAULT NULL,
  `inputDBEText13` varchar(255) NOT NULL,
  `inputDBEText14` varchar(255) DEFAULT NULL,
  `inputDBEText15` varchar(255) DEFAULT NULL,
  `inputDBEText16` varchar(255) DEFAULT NULL,
  `inputDBEText17` varchar(255) DEFAULT NULL,
  `inputDBEText18` varchar(255) DEFAULT NULL,
  `inputDBEText19` varchar(255) DEFAULT NULL,
  `inputDBEText20` varchar(255) DEFAULT NULL,
  `inputDBECheck1` smallint(1) NOT NULL,
  `inputDBECheck2` smallint(1) NOT NULL,
  `inputDBECheck3` smallint(1) NOT NULL,
  `inputDBECheck4` smallint(1) NOT NULL,
  `inputDBECheck5` smallint(1) NOT NULL,
  `inputDBECheck6` smallint(1) NOT NULL,
  `inputDBECheck7` smallint(1) NOT NULL,
  `inputDBECheck8` smallint(1) NOT NULL,
  `inputDBECheck9` smallint(1) NOT NULL,
  `inputDBECheck10` smallint(1) NOT NULL,
  `inputDBECheck11` smallint(1) NOT NULL,
  `inputDBECheck12` smallint(1) NOT NULL,
  `inputDBECheck13` smallint(1) NOT NULL,
  `inputDBECheck14` smallint(1) NOT NULL,
  `inputDBECheck15` smallint(1) NOT NULL,
  `inputDBECheck16` smallint(1) NOT NULL,
  `inputDBECheck17` smallint(1) NOT NULL,
  `inputDBECheck18` smallint(1) NOT NULL,
  `inputDBECheck19` smallint(1) NOT NULL,
  `inputDBECheck20` smallint(1) NOT NULL,
  `inputDBEFormat1` varchar(100) DEFAULT NULL,
  `inputDBEFormat2` varchar(100) DEFAULT NULL,
  `inputDBEFormat3` varchar(100) DEFAULT NULL,
  `inputDBEFormat4` varchar(100) DEFAULT NULL,
  `inputDBEFormat5` varchar(100) DEFAULT NULL,
  `inputDBEFormat6` varchar(100) DEFAULT NULL,
  `inputDBEFormat7` varchar(100) DEFAULT NULL,
  `inputDBEFormat8` varchar(100) DEFAULT NULL,
  `inputDBEFormat9` varchar(100) DEFAULT NULL,
  `inputDBEFormat10` varchar(100) DEFAULT NULL,
  `inputDBEFormat11` varchar(100) DEFAULT NULL,
  `inputDBEFormat12` varchar(100) DEFAULT NULL,
  `inputDBEFormat13` varchar(100) DEFAULT NULL,
  `inputDBEFormat14` varchar(100) DEFAULT NULL,
  `inputDBEFormat15` varchar(100) DEFAULT NULL,
  `inputDBEFormat16` varchar(100) DEFAULT NULL,
  `inputDBEFormat17` varchar(100) DEFAULT NULL,
  `inputDBEFormat18` varchar(100) DEFAULT NULL,
  `inputDBEFormat19` varchar(100) DEFAULT NULL,
  `inputDBEFormat20` varchar(100) DEFAULT NULL,
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
  `params` varchar(100) NOT NULL,
  KEY `device_id` (`device_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;