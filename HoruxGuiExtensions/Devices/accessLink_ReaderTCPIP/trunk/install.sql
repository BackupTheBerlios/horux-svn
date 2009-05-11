CREATE TABLE IF NOT EXISTS `hr_accessLink_ReaderTCPIP` (
  `id` int(11) NOT NULL auto_increment,
  `ip` varchar(40) NOT NULL default '0',
  `port` int(11) NOT NULL,
  `id_device` int(11) NOT NULL default '0',
  `outputTime1` int(11) NOT NULL,
  `outputTime2` int(11) NOT NULL,
  `outputTime3` int(11) NOT NULL,
  `outputTime4` int(11) NOT NULL,
  `antipassback` smallint(6) NOT NULL,
  `open_mode` set( 'NONE', 'NO_TIMEOUT', 'TIMEOUT', 'TIMEOUT_IN') NOT NULL default 'NO_TIMEOUT',
  `open_mode_timeout` int(11) NOT NULL default '0',
  `open_mode_input` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM CHARACTER SET `utf8`;