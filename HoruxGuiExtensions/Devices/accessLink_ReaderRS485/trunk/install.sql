CREATE TABLE IF NOT EXISTS `hr_accessLink_ReaderRS485` (
  `id` int(11) NOT NULL auto_increment,
  `address` int(5) NOT NULL default '0',
  `id_device` int(11) NOT NULL default '0',
  `memory` set('200','1000') NOT NULL default '200',
  `rtc` smallint(1) NOT NULL default '0',
  `lcd` smallint(1) NOT NULL default '0',
  `keyboard` smallint(1) NOT NULL default '0',
  `eeprom` smallint(1) NOT NULL default '0',
  `defaultText` text NOT NULL,
  `outputTime1` int(11) NOT NULL,
  `outputTime2` int(11) NOT NULL,
  `outputTime3` int(11) NOT NULL,
  `outputTime4` int(11) NOT NULL,
  `antipassback` smallint(6) NOT NULL,
  `standalone` smallint(1) NOT NULL default '0',
  `open_mode` set( 'NONE', 'NO_TIMEOUT', 'TIMEOUT', 'TIMEOUT_IN') NOT NULL default 'NO_TIMEOUT',
  `open_mode_timeout` int(11) NOT NULL default '0',
  `open_mode_input` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM CHARACTER SET `utf8`;