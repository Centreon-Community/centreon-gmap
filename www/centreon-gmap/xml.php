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
	
	ini_set("display_errors", "Off");
	
	include_once "/etc/centreon/centreon.conf.php";
	include_once $centreon_path . "www/class/other.class.php";
	include_once $centreon_path . "www/class/centreonGMT.class.php";
	include_once $centreon_path . "www/class/centreonXML.class.php";
	include_once $centreon_path . "www/class/centreonDB.class.php";
	include_once $centreon_path . "www/class/Session.class.php";
	include_once $centreon_path . "www/class/Oreon.class.php";
	include_once $centreon_path . "www/class/centreonLang.class.php";
	include_once $centreon_path . "www/include/common/common-Func.php";
	
	session_start();
	//$oreon = $_SESSION['oreon'];
	
	//global $oreon;
	//$centreonlang = new CentreonLang($centreon_path, $oreon);
	//$centreonlang->bindLang();
	
	/*
	 * Tab status
	 */
	$hostState = array("UP", "DOWN", "UNREACHABLE");
	$serviceState = array("OK", "WARNING", "CRITICAL", "UNKNOWN");
	
	/*
	* Call DB connector
	*/
	$pearDB         = new CentreonDB();
	$pearDBndo      = new CentreonDB("ndo");
	
	$ndo_prefix = getNDOPrefix();
    
	/*
	 * We grab a list of Services and check the status for each host
	 * Returns either UP, DOWN or WARN
	 */
	function ServiceStatusPerHost($host_name) { 
		global $ndo_prefix,$pearDB,$pearDBndo;
		
		$DBRESULT_NDO =& $pearDBndo->query("SELECT DISTINCT ".$ndo_prefix."services.service_object_id as sid, ".
						   " ".$ndo_prefix."servicestatus.current_state as state ".
						   "FROM ".$ndo_prefix."services,".$ndo_prefix."servicestatus,".$ndo_prefix."hosts ".
						   "WHERE ".$ndo_prefix."hosts.display_name='$host_name' ".
						   "AND ".$ndo_prefix."hosts.host_object_id = ".$ndo_prefix."services.host_object_id ".
						   "AND ".$ndo_prefix."services.service_object_id = ".$ndo_prefix."servicestatus.service_object_id");
		if (PEAR::isError($DBRESULT_NDO)) {
	    	print "DB Error : ".$DBRESULT_NDO->getDebugInfo()."<br/>";
		}
	    $state = array();
	    while ($data =& $DBRESULT_NDO->fetchRow()) {
			if (!isset($state[$data["state"]])) {
				$state[$data["state"]] = 0;
			}
			$state[$data["state"]]++;
		}
		return $state;
	}

      
/*	function CreateHosts($lid,$pearDB) { // lid is the location id
	 	$DBRESULT =& $pearDB->query("SELECT hostgroup_hg_id as id,host_name as name,host_id as hostid ".
				     "FROM hostgroup_relation ".
				     "JOIN host ".
				     "ON hostgroup_relation.host_host_id = host.host_id ".
				     "WHERE hostgroup_relation.hostgroup_hg_id='$lid'");
		if (PEAR::isError($DBRESULT)) {
        	print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		}
        
        $host_status = "";
		$n = 0;
		$location_status = "UP";

        while ($new_map_location =& $DBRESULT->fetchRow()) {		
        	$i = 0;
            $host_name = $new_map_location['name'];
			$host_id = $new_map_location['hostid'];
            $location_id = $new_map_location['id'];*/
				
			/*
			 * lookup host status
			 */
			/*$state = ServiceStatusPerHost($host_name);		
			
			if ($state == "UP") {
				$host_status = $host_status."<a href=main.php?p=201&o=hd&host_name=".urlencode($host_name)."><img src=\"modules/gmap/img/green-dot.png\"></a>";
				$location_status = "UP";
			} else if($state == "WARN") {
				$host_status = $host_status."<a href=main.php?p=201&o=hd&host_name=".urlencode($host_name)."><img src=\"modules/gmap/img/yellow-dot.png\"></a>";
				$location_status = "WARN";
			} else {
				$host_status = $host_status."<a href=main.php?p=201&o=hd&host_name=".urlencode($host_name)." ><img src=\"modules/gmap/img/red-dot.png\"></a>";
				$location_status = "DOWN";
			}
			
			$n++;
			if($n > 11) {
				$host_status = $host_status."<BR>";
				$n = 0;
			}	
		}
		return $host_status;
	}*/
	
	function getHostState($host_name, $pearDBndo, $ndo_prefix) {
		$rq1 = 	" SELECT DISTINCT no.name1, nhs.current_state," .
			" nhs.problem_has_been_acknowledged, " .
			" nhs.last_check as last_check2, " .
			" nhs.output," .
			" unix_timestamp(nhs.last_check) as last_check," .
			" nh.address," .
			" no.name1 as host_name," .
			" nh.action_url," .
			" nh.notes_url," .
			" nh.notes," .
			" nh.icon_image," .
			" nh.icon_image_alt," .
			" nhs.max_check_attempts," .
			" nhs.state_type," .
			" nhs.current_check_attempt, " .
			" nhs.scheduled_downtime_depth" .
			" FROM ".$ndo_prefix."hoststatus nhs, ".$ndo_prefix."objects no, ".$ndo_prefix."hosts nh " .
			" WHERE no.object_id = nhs.host_object_id AND nh.host_object_id = no.object_id AND name1 LIKE '$host_name' LIMIT 1";
		$DBRESULT =& $pearDBndo->query($rq1);
		$data =& $DBRESULT->fetchRow();
		return $data;
	}
	
	$dom = new DOMDocument("1.0");
	$node = $dom->createElement("markers");
	$parnode = $dom->appendChild($node);
	
	$DBRESULT =& $pearDB->query("SELECT DISTINCT host.host_name AS name, locations.lat AS lat, ". 
								"locations.address AS addr, locations.lng AS lng, locations.h_id AS h_id, locations.hg_id AS hg_id ".
								"FROM locations, host, hostgroup ".
								"WHERE locations.h_id = host.host_id ".
								"UNION ".
								"SELECT hostgroup.hg_name as name, ".
								"locations.lat as lat, ".
								"locations.lng as lng, ".
								"locations.address as addr, ".
								"locations.h_id as hid, ".
								"locations.hg_id AS hg_id ".
								"FROM locations,hostgroup ".
								"WHERE locations.hg_id = hostgroup.hg_id ".
								"ORDER BY name ASC");
	if (PEAR::isError($DBRESULT)) {
        print "DB Error : ".$DBRESULT->getDebugInfo()."<br/>";
	}
    while ($marker =& $DBRESULT->fetchRow()) {
		$popupString = "";
		$information = getHostState($marker['name'], $pearDBndo, $ndo_prefix);
		$stateService = ServiceStatusPerHost($marker['name']);
		print_r($stateservice);
		$node = $dom->createElement("location");
		$newnode = $parnode->appendChild($node);
        $newnode->setAttribute("name",$marker['name']);
		$newnode->setAttribute("lat", $marker['lat']);
		$newnode->setAttribute("lng", $marker['lng']);
		
		$newnode->setAttribute("location_status", $hostState[$information["current_state"]]);
		if ($information["icon_image"] != "") {
			$information["icon_image"] = "./img/media/".$information["icon_image"];
			$popupString .= "<img src='".$information["icon_image"]."' width='25px' heigth='25px'><br/><br/>";
		} else {
			$information["icon_image"] = "./img/icones/16x16/server_network.gif";
			$popupString .= "<img src='".$information["icon_image"]."' width='20px' heigth='20px'><br/><br/>";
		}
		$newnode->setAttribute("icon", $information["icon_image"]);
		$popupString .= "<b>"._("Host name :")."</b>"." ".$marker['name']."<br/>";
		$popupString .= "<b>"._("Host address :")."</b>"." ".$information['address']."<br/>";	
		
		if ($hostState[$information["current_state"]] == "DOWN") {
			$popupString .= "<b>"._("Status :")."<font color='red'>"." ".$hostState[$information["current_state"]]."</font></b><br/>";
		} else if ($hostState[$information["current_state"]] == "UP") {
			$popupString .= "<b>"._("Status :")."<font color='green'>"." ".$hostState[$information["current_state"]]."</font></b><br/>";
		} else if ($hostState[$information["current_state"]] == "UNREACHABLE") {
			$popupString .= "<b>"._("Status :")."<font color='orange'>"." ".$hostState[$information["current_state"]]."</font></b><br/>";
		}
				
		if ($information["current_state"]) {
			$popupString .= "<b>"._("Acknownledge :")."</b>"." ".($information["problem_has_been_acknowledged"] > 0 ? _("Yes") : _("No"))."<br/>";
		}
		$popupString .= "<b>"._("Last check :")."</b>"." ".$information["last_check2"]."<br/><br/>";
		
		foreach ($stateService as $state => $nb) {
			if ($serviceState[$state] == "OK") {
				$popupString .= "<b>"._("Services ")."<font color='green'>".$serviceState[$state]."</font> :"."</b>"." ".$nb."<br/>";
			}
			else if ($serviceState[$state] == "WARNING") {
				$popupString .= "<b>"._("Services ")."<font color='orange'>".$serviceState[$state]."</font> :"."</b>"." ".$nb."<br/>";
			}
			else if ($serviceState[$state] == "CRITICAL") {
				$popupString .= "<b>"._("Services ")."<font color='red'>".$serviceState[$state]."</font> :"."</b>"." ".$nb."<br/>";
			}
			else if ($serviceState[$state] == "UNKNOWN") {
				$popupString .= "<b>"._("Services ")."<font color='grey'>".$serviceState[$state]."</font> :"."</b>"." ".$nb."<br/>";
			}
		}
		
		$popupString .= "<br/><b>"._("Location :")."</b>"." ".$marker['addr']."<br/>";		
		
		$popupString .= "<br/><br/><center><a href='?p=20201&o=svc&hostsearch=".$marker['name']."'>"._("Show Services Details")."</a></center>";
		$newnode2 = $node->appendChild($dom->createTextNode($popupString));
		
	}
	$DBRESULT->free();

/*
		if ($marker['hg_id'] != '0') {
			$host_status = CreateHosts($marker['hg_id'],$pearDB);
		} else { 
			
			
			//$state = ServiceStatusPerHost($marker['name']);

			if ($information["current_state"] == "UP") {
				$host_status = $host_status."<a href=main.php?p=201&o=hd&host_name=".urlencode($host_name)."><img src=\"modules/gmap/img/green-dot.png\"></a>";
				$location_status = "UP";
			} else if ($information["current_state"] == "WARN") {
				$host_status = $host_status."<a href=main.php?p=201&o=hd&host_name=".urlencode($host_name)."><img src=\"modules/gmap/img/yellow-dot.png\"></a>";
				$location_status = "UNREACHABLE";	
			} else {
				$host_status = $host_status."<a href=main.php?p=201&o=hd&host_name=".urlencode($host_name)." ><img src=\"modules/gmap/img/red-dot.png\"></a>";
				$location_status = "DOWN";
			}
		}

		$location_critical = strpos($host_status,"red-dot");
		$location_warn = strpos($host_status,"yellow-dot");
		if ($location_critical === TRUE) {
			$newnode->setAttribute("location_status", "DOWN");
		} else if($location_warn === TRUE) {
			$newnode->setAttribute("location_status", "UNREACHABLE");
		} else {
			$newnode->setAttribute("location_status", "UP");
		}
		*/


	header("Content-type: text/xml");
	
    echo $dom->saveXML();

?>
