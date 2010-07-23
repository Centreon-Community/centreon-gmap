<?php
/**************************************************
Centreon GMAP Module version: 1.1
This module is licensed under the GPLv2
http://www.gnu.org/licenses/gpl.txt
Developped by : Justin Guagliata
Version: 1.1
Designed for: Centreon v2
For information : justin@ensgrp.com or www.ensgrp.com
**************************************************/

	if (!isset($oreon))
		exit();

	## Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	$form = new HTML_QuickForm();

	## Path to the configuration dir
	global $path;
	$path = "./modules/centreon-gmap/";
	
	## PHP functions
	require_once "DB-Func.php";
	require_once "./include/common/common-Func.php";

	## legacy update host	
	if (isset($_GET['l_id'])) {
		$l_id = $_GET['l_id'];
		$hg_id = $_GET['hg_id'];
		$lat = $_GET['lat'];
		$long = $_GET['long'];
		$address = $_GET['address'];
		$action = $_GET['action'];	
        
        if ($action == "new") {
			$DBRESULT =& $pearDB->query("INSERT into`locations` SET `h_id`='$hg_id',`lat`='$lat',`long`='$long',`address`='$address'");
		} else if ($action == "update") {
			$DBRESULT =& $pearDB->query("UPDATE `locations` SET `h_id`='$hg_id',`lat`='$lat',`long`='$long',`address`='$address' WHERE l_id='$l_id' LIMIT 1");
		} else if ($action == "delete") {
                $DBRESULT =& $pearDB->query("DELETE from `locations` WHERE l_id='$l_id' LIMIT 1");
        }

		if (PEAR::isError($DBRESULT)) {
      		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		}
	}
	
	/*
	 * we pass either group or host
	 */
	getHostgroupLatLong2('host');
	getHostList();
	
	$gmap_op = readConfigOptions($pearDB,$oreon);
	
	/*
	 * if the gmap api is set, display page 
	 */ 
	if ($gmap_op['api_key']) {
		$tpl = new Smarty();
	   	$tpl = initSmartyTpl($path, $tpl);
	   	$tpl->assign("h_names", $smarty_host);
		$tpl->assign("host_list", $smarty_host_list);
		$tpl->assign("hostgroup_list", $smarty_hostgroup_list);
		$tpl->assign("gmap_key", $gmap_op['api_key']);
		$tpl->assign("gmap_lat", $gmap_op['lat']);
        $tpl->assign("gmap_long", $gmap_op['long']);
        $tpl->assign("gmap_height", $gmap_op['height']);
        $tpl->assign("gmap_width", $gmap_op['width']);
        
        /*
         * translations
         */
        $tpl->assign("host", _("Hosts"));
        $tpl->assign("address", _("Address, postal code, city, country"));
        $tpl->assign("latitude", _("Latitude"));
        $tpl->assign("longitude", _("Longitude"));
        $tpl->assign("actions", _("Actions"));
        $tpl->assign("addLocation", _("Add a location for a host"));
        
	   	$tpl->display("gmap_config_host.ihtml");
	}
?>