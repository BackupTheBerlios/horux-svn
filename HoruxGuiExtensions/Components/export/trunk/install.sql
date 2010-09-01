CREATE TABLE IF NOT EXISTS `hr_export` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `locked` int(11) NOT NULL,
  `sql` text NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `hr_import` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `locked` int(11) NOT NULL,
  `tb_name` varchar(50) NOT NULL,
  `cols` text NOT NULL,
  `terminated_by` varchar(5) NOT NULL,
  `enclosed_by` varchar(5) NOT NULL,
  `escaped_by` varchar(5) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `hr_import` (`name`, `locked`, `tb_name`, `cols`, `terminated_by`, `enclosed_by`, `escaped_by`, `description`) VALUES
('exemple1', 0, 'hr_import', '"", "name", "", "", "", "", "", "", "description"', '#', '&', '\\\\', '...');
