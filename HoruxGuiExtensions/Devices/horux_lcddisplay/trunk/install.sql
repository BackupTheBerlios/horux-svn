CREATE TABLE IF NOT EXISTS `hr_horux_lcddisplay` (
  `id` int(11) NOT NULL auto_increment,
  `ip` varchar(40) NOT NULL default '0',
  `port` int(11) NOT NULL,
  `id_device` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM CHARACTER SET `utf8`;