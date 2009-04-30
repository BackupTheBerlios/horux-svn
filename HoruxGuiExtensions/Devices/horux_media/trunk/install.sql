CREATE TABLE IF NOT EXISTS `hr_horux_InfoDisplay` (
  `id` int(11) NOT NULL auto_increment,
  `id_device` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `port` int(11) NOT NULL,
  `id_action_device` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM CHARACTER SET `utf8`;