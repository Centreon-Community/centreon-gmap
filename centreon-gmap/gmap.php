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

	if (!isset ($oreon))
		exit ();

	//Pear library
	require_once 'HTML/QuickForm.php';
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

      $form = new HTML_QuickForm();

	//Path to the configuration dir
	global $path;
	$path = "./modules/gmap/";

	#PHP functions
	require_once "DB-Func.php";
	require_once "./include/common/common-Func.php";

        // define globals
	global $pearDB;
	global $oreon;

        // We read the status.dat file.
	$gmap_op = readConfigOptions($pearDB,$oreon);
	
	if($gmap_op['api_key']) {
	    $tpl = new Smarty();
	    $tpl = initSmartyTpl($path, $tpl);
	    $tpl->assign("gmap_key", $gmap_op['api_key']);
            $tpl->assign("gmap_lat", $gmap_op['lat']);
            $tpl->assign("gmap_long", $gmap_op['long']);
            $tpl->assign("gmap_height", $gmap_op['height']);
            $tpl->assign("gmap_width", $gmap_op['width']);
	    $tpl->display("gmap.ihtml");
	}
	else 
	   echo "You need to obtain a valid google map key which can be obtained <a href=http://www.google.com/apis/maps/>Here</a><br>"
		."After obtaining a valid google api key, add it to the module configuration under <a href=main.php?p=5010190&o=w>options->General Options->Gmap</a> ";


?>
