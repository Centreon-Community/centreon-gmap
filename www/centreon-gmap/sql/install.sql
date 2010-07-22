CREATE TABLE `gmap_op` (
  `id` tinyint(4) NOT NULL auto_increment,
  `api_key` varchar(255) NOT NULL default '',
  `lat` float NOT NULL default '0',
  `long` float NOT NULL default '0',
  `height` smallint(6) NOT NULL default '0',
  `width` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

CREATE TABLE locations (
  l_id int(11) NOT NULL auto_increment,
  h_id int(11) default NULL,
  hg_id int(11) default NULL,
  address varchar(60) default NULL,
  lat varchar(12) default NULL,
  `long` varchar(12) default NULL,
  PRIMARY KEY  (l_id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `gmap_op` (`id`, `api_key`, `lat`, `long`, `height`, `width`) VALUES 
(1, '', 14.6048, 7.03125, 1000, 500);
INSERT INTO `css_color_menu` (`id_css_color_menu`, `menu_nb`, `css_name`) VALUES
(9, 9, 'green_css.php');

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES
('', 9, NULL, './include/common/javascript/changetab.js', 'initChangeTab'),
('', 901, NULL, './include/common/javascript/changetab.js', 'initChangeTab');


INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'Google Map', NULL, 2, 245, 100, 1, './modules/centreon-gmap/gmap.php', NULL, '0', '1', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'View', NULL, 245, 24501, 100, 1, './modules/centreon-gmap/gmap.php', NULL, '0', '1', '1');

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'Host Config', NULL, 245, 24502, 101, 1, './modules/centreon-gmap/gmap_config_host.php', NULL, '0', '1', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'Host Group Config', NULL, 245, 24503, 102, 1, './modules/centreon-gmap/gmap_config_hostGroup.php', NULL, '0', '1', '1', NULL, NULL, NULL);


INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'GMAP Options', './modules/gmap/img/ico_google.jpg', 50101, 5010190, 130, 1, './modules/centreon-gmap/gmap_Opt.php', '&o=w', '0', '0', '1', NULL, NULL, NULL);


