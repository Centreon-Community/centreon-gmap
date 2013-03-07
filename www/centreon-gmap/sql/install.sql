CREATE TABLE `mod_gmap_options` (
  `id` tinyint(4) NOT NULL auto_increment,
  `lat` float NOT NULL default '0',
  `lng` float NOT NULL default '0',
  `height` smallint(6) NOT NULL default '0',
  `zoomLevel` smallint(3) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

CREATE TABLE mod_gmap_locations (
  l_id int(11) NOT NULL auto_increment,
  h_id int(11) default NULL,
  hg_id int(11) default NULL,
  address varchar(60) default NULL,
  lat varchar(12) default NULL,
  `lng` varchar(12) default NULL,
  PRIMARY KEY  (l_id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `mod_gmap_options` (`id`, `lat`, `lng`, `height`, `zoomLevel`) VALUES (1, 47.9016, 1.99951, 500, 5);
INSERT INTO `css_color_menu` (`id_css_color_menu`, `menu_nb`, `css_name`) VALUES (9, 9, 'green_css.php');

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 9, NULL, './include/common/javascript/changetab.js', 'initChangeTab'), ('', 901, NULL, './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`) VALUES
('', 'View', './modules/centreon-gmap/img/ico_google.jpg', 7, 704, 100, 1, './modules/centreon-gmap/core/console/gmap.php', NULL, '0', '1', '1', NULL, NULL, NULL, '1'),
('', 'Host Config', './img/icones/16x16/server_network.gif', 7, 702, 101, 2, './modules/centreon-gmap/core/configHost/gmap_config_host.php', NULL, '0', '1', '1', NULL, NULL, NULL, '1'),
('', 'Host Group Config', './img/icones/16x16/clients.gif', 7, 703, 102, 1, './modules/centreon-gmap/core/configHostGroup/gmap_config_hostGroup.php', NULL, '0', '1', '1', NULL, NULL, NULL, '1'),
('', 'Gmap Options', NULL, 50101, 5010191, 130, 1, './modules/centreon-gmap/core/options/gmapOpt.php', '&o=w', '0', '0', '1', NULL, NULL, NULL, '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`) VALUES
('', 'Google Map', NULL, NULL, 7, 70, 1, NULL, NULL, '0', '0', '1', NULL, NULL, NULL, '1');
