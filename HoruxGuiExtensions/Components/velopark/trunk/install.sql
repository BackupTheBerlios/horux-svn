CREATE TABLE IF NOT EXISTS `hr_vp_parking` (
  `id` int(11) NOT NULL auto_increment,
  `area` int(11) NOT NULL default '0',
  `display_id` int(11) NOT NULL default '0',
  `default_msg` varchar(255) NOT NULL,
  `access_ok_msg` varchar(255) NOT NULL,
  `access_ko_msg` varchar(255) NOT NULL,
  `displayTime` smallint(6) NOT NULL default '4',
  `accesspoint_id` int(11) NOT NULL default '0',
  `lightinfo_id` int(11) NOT NULL default '0',
  `lightinfo_io` smallint(6) NOT NULL,
  `filling` int(11) NOT NULL default '0',
  `locked` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM CHARACTER SET `utf8`;

CREATE TABLE IF NOT EXISTS `hr_vp_subscription` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `validity` varchar(30) NOT NULL,
  `credit` int(11) NOT NULL,
  `locked` int(11) NOT NULL,
  `price` float NOT NULL,
  `start` set('immediatly','firstaccess') NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM CHARACTER SET `utf8`;


CREATE TABLE IF NOT EXISTS `hr_vp_subscription_attribution` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `subcription_id` int(11) NOT NULL default '0',
  `create_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `status` set('not_start','started','finished') NOT NULL default 'not_start',
  `credit` int(11) NOT NULL default '0',
  `start` datetime NOT NULL default '0000-00-00 00:00:00',
  `end` datetime NOT NULL default '0000-00-00 00:00:00',
  `create_by` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `start` (`start`,`end`,`user_id`,`subcription_id`)
) TYPE=MyISAM CHARACTER SET `utf8`;

INSERT INTO `hr_user_action` (`name`, `page`, `icon`, `tip`, `catalog`, `type`) VALUES
('Subscription', 'components.velopark.attribution', './protected/pages/components/velopark/assets/icon-16-ticket.jpg', 'Velo Park - Subscription Attribution', 'velopark', 'userList'),
('Attribute a subscription', 'protected/pages/components/velopark/wizard.tpl', '', '', 'velopark', 'userWizardTpl'),
('VeloparkModule', 'components.velopark.VeloparkModule', '', '', 'velopark', 'module');
