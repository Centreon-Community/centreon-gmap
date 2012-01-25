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
		
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the configuration dir
	global $path;
	$path = "./modules/centreon-gmap/";

	#PHP functions
	require_once $path."DB-Func.php";	

	switch ($o)	{
		case "w" : require_once($path."formGmap.php"); break;
		default : require_once($path."formGmap.php"); break;
	}
?>
