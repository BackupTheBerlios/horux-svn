CREATE TABLE IF NOT EXISTS `hr_horux_rstcpip_converter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_device` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `port` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) TYPE=MyISAM CHARACTER SET `utf8`;