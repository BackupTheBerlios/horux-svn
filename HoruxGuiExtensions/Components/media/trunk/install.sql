CREATE TABLE IF NOT EXISTS `hr_horux_media_media` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `type` set('IMAGE','MOVIE') NOT NULL,
  `path` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `id_device` int(11) NOT NULL,
  `published` smallint(1) NOT NULL,
  `locked` smallint(6) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM CHARACTER SET `utf8`;

CREATE TABLE IF NOT EXISTS `hr_horux_media_message` (
  `id` int(11) NOT NULL auto_increment,
  `id_user` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` set('USER','ALL','INFO','UNKNOWN') NOT NULL,
  `startDisplay` datetime NOT NULL,
  `stopDisplay` datetime NOT NULL,
  `locked` int(6) NOT NULL,
  `name` varchar(30) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM CHARACTER SET `utf8`;

INSERT INTO `hr_user_action` (`id` ,`name` ,`page` ,`icon` ,`tip` ,`catalog`)
VALUES (NULL , 'media', 'components.media.addUserMessage', './protected/pages/components/media/assets/icon-16-message.png', 'Attribute a message to the user', 'media');


INSERT INTO `hr_horux_media_message` (`id`, `id_user`, `message`, `type`, `startDisplay`, `stopDisplay`) VALUES
(1, 0, '', 'ALL', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 0, '', 'UNKNOWN', '0000-00-00 00:00:00', '0000-00-00 00:00:00');