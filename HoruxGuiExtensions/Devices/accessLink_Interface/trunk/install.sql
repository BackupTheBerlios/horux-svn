CREATE TABLE IF NOT EXISTS `hr_accessLink_Interface` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `id_device` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL default '',
  `mask` varchar(15) NOT NULL default '255.255.255.0',
  `gateway` varchar(15) NOT NULL default '0.0.0.0',
  `data_port` int(11) NOT NULL default '1025',
  `server1` varchar(15) NOT NULL default '',
  `server2` varchar(15) NOT NULL default '',
  `server3` varchar(15) NOT NULL default '',
  `password` varchar(15) NOT NULL default '',
  `time_zone` smallint(6) NOT NULL default '0',
  `temp_max` int(11) NOT NULL default '60',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `ip` (`ip`)
) TYPE=MyISAM CHARACTER SET `utf8`;