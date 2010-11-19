CREATE TABLE IF NOT EXISTS `hr_moxa_iologic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(40) NOT NULL DEFAULT '0',
  `port` int(11) NOT NULL,
  `id_device` int(11) NOT NULL DEFAULT '0',
  `password` varchar(8) NOT NULL,
  `initialOutput` varchar(16) NOT NULL DEFAULT '0,0,0,0,0,0,0,0',
  `output0_func` set('none','accessAccepted','accessRefused','keyDetectedReset') NOT NULL DEFAULT 'none',
  `output1_func` set('none','accessAccepted','accessRefused','keyDetectedReset') NOT NULL DEFAULT 'none',
  `output2_func` set('none','accessAccepted','accessRefused','keyDetectedReset') NOT NULL DEFAULT 'none',
  `output3_func` set('none','accessAccepted','accessRefused','keyDetectedReset') NOT NULL DEFAULT 'none',
  `output4_func` set('none','accessAccepted','accessRefused','keyDetectedReset') NOT NULL DEFAULT 'none',
  `output5_func` set('none','accessAccepted','accessRefused','keyDetectedReset') NOT NULL DEFAULT 'none',
  `output6_func` set('none','accessAccepted','accessRefused','keyDetectedReset') NOT NULL DEFAULT 'none',
  `output7_func` set('none','accessAccepted','accessRefused','keyDetectedReset') NOT NULL DEFAULT 'none',
  `output0Time` int(11) NOT NULL DEFAULT '0',
  `output1Time` int(11) NOT NULL DEFAULT '0',
  `output2Time` int(11) NOT NULL DEFAULT '0',
  `output3Time` int(11) NOT NULL DEFAULT '0',
  `output4Time` int(11) NOT NULL DEFAULT '0',
  `output5Time` int(11) NOT NULL DEFAULT '0',
  `output6Time` int(11) NOT NULL DEFAULT '0',
  `output7Time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) TYPE=MyISAM CHARACTER SET `utf8`;