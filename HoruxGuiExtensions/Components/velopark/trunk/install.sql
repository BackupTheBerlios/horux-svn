CREATE TABLE IF NOT EXISTS `hr_vp_parking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `area` int(11) NOT NULL DEFAULT '0',
  `access_unknown_msg` varchar(255) NOT NULL,
  `access_ko_msg` varchar(255) NOT NULL,
  `filling` int(11) NOT NULL DEFAULT '0',
  `locked` int(11) NOT NULL,
  `device_ids` varchar(255) NOT NULL,
  `access_credit_warning_msg` varchar(255) NOT NULL,
  `access_warning_msg` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `hr_vp_subscription` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `validity` varchar(30) NOT NULL,
  `credit` int(11) NOT NULL,
  `locked` int(11) NOT NULL,
  `price` float NOT NULL,
  `start` set('immediatly','firstaccess') NOT NULL,
  `multiticket` smallint(1) NOT NULL DEFAULT '0',
  `VAT` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `hr_vp_subscription_attribution` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `subcription_id` int(11) NOT NULL DEFAULT '0',
  `create_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` set('not_start','started','finished','waiting','canceled') NOT NULL DEFAULT 'not_start',
  `credit` int(11) NOT NULL DEFAULT '0',
  `start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `create_by` varchar(100) NOT NULL,
  `multiticket` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `start` (`start`,`end`,`user_id`,`subcription_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `hr_vp_deleted_user` (
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `hr_user_action` (`name`, `page`, `icon`, `tip`, `catalog`, `type`) VALUES
('Subscription', 'components.velopark.attribution', './protected/pages/components/velopark/assets/icon-16-ticket.jpg', 'Ticketing - Subscription Attribution', 'velopark', 'userList'),
('Attribute a subscription', 'protected/pages/components/velopark/wizard.tpl', '', '', 'velopark', 'userWizardTpl'),
('VeloparkModule', 'components.velopark.VeloparkModule', '', '', 'velopark', 'module');
