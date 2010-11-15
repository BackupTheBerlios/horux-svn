CREATE TABLE IF NOT EXISTS `hr_a3m_lgm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_device` int(11) NOT NULL,
  `address` int(11) NOT NULL,
  `serialNumberFormat` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) TYPE=MyISAM CHARACTER SET `utf8`;