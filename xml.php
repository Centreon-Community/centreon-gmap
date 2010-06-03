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
$oreon = $_SESSION['oreon'];

$centreonlang = new CentreonLang($centreon_path, $oreon);
$centreonlang->bindLang();

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
	$DBRESULT_NDO =& $pearDBndo->query("SELECT ".$ndo_prefix."services.service_object_id as sid, ".
					   " ".$ndo_prefix."servicestatus.current_state as state ".
					   "FROM ".$ndo_prefix."services,".$ndo_prefix."servicestatus,".$ndo_prefix."hosts ".
					   "WHERE ".$ndo_prefix."hosts.display_name='$host_name' ".
					   "AND ".$ndo_prefix."hosts.host_object_id = ".$ndo_prefix."services.host_object_id ".
					   "AND ".$ndo_prefix."services.service_object_id = ".$ndo_prefix."servicestatus.service_object_id");
	if (PEAR::isError($DBRESULT_NDO))
             print "DB Error : ".$DBRESULT_NDO->getDebugInfo()."<br>";
        $state = "UP";
	while($DBRESULT_NDO->fetchInto($get_service_states)) {
		if($get_service_states['state'] == 2) {
			$state = "DOWN";	
		}
		if($state != "DOWN" && $get_service_states['state'] == 1) {
			$state = "WARN";
		}
	
	}
	return $state;
	}

      
	function CreateHosts($lid,$pearDB) { // lid is the location id
	 $DBRESULT =& $pearDB->query("SELECT hostgroup_hg_id as id,host_name as name,host_id as hostid ".
				     "FROM hostgroup_relation ".
				     "JOIN host ".
				     "ON hostgroup_relation.host_host_id = host.host_id ".
				     "WHERE hostgroup_relation.hostgroup_hg_id='$lid'");
		if (PEAR::isError($DBRESULT))
                        print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
                $host_status = "";
		$n = 0;
		$location_status = "UP";

                        while($DBRESULT->fetchInto($new_map_location)) {
				
                                $i = 0;
                                $host_name = $new_map_location['name'];
				$host_id = $new_map_location['hostid'];
                                $location_id = $new_map_location['id'];
				
				#lookup host status
				$state = ServiceStatusPerHost($host_name);		
				
				if($state == "UP") {
					$host_status = $host_status."<a href=main.php?p=201&o=hd&host_name=".urlencode($host_name)."><img src=\"modules/gmap/img/green-dot.png\"></a>";
					$location_status = "UP";
				}
				elseif($state == "WARN") {
					$host_status = $host_status."<a href=main.php?p=201&o=hd&host_name=".urlencode($host_name)."><img src=\"modules/gmap/img/yellow-dot.png\"></a>";
					$location_status = "WARN";
				}
                                else {
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

	}
	
		$dom = new DOMDocument("1.0");
                $node = $dom->createElement("markers");
                $parnode = $dom->appendChild($node);
                header("Content-type: text/xml");
		$DBRESULT =& $pearDB->query("SELECT DISTINCT host.host_name AS name, locations.lat AS lat, ". 
					    "locations.long AS lng, locations.h_id AS h_id, locations.hg_id AS hg_id ".
					    "FROM locations, host, hostgroup ".
					    "WHERE locations.h_id = host.host_id ".
					    "UNION ".
					    "SELECT hostgroup.hg_name as name, ".
					    "locations.lat as lat, ".
					    "locations.long as lng, ".
					    "locations.h_id as hid, ".
					    "locations.hg_id AS hg_id ".
					    "FROM locations,hostgroup ".
					    "WHERE locations.hg_id = hostgroup.hg_id ".
					    "ORDER BY name ASC");
                if (PEAR::isError($DBRESULT))
                        print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
                while($DBRESULT->fetchInto($marker)) {
			$node = $dom->createElement("location");
			$newnode = $parnode->appendChild($node);
                        $newnode->setAttribute("name",$marker['name']);
                        $newnode->setAttribute("lat", $marker['lat']);
                        $newnode->setAttribute("lng", $marker['lng']);
			if($marker['hg_id'] != '0') {
			$host_status = CreateHosts($marker['hg_id'],$pearDB);
			}
			else { 
			$state = ServiceStatusPerHost($marker['name']);

				if($state == "UP") {
					$host_status = $host_status."<a href=main.php?p=201&o=hd&host_name=".urlencode($host_name)."><img src=\"modules/gmap/img/green-dot.png\"></a>";
					$location_status = "UP";
				}
				elseif($state == "WARN") {
					$host_status = $host_status."<a href=main.php?p=201&o=hd&host_name=".urlencode($host_name)."><img src=\"modules/gmap/img/yellow-dot.png\"></a>";
					$location_status = "WARN";
				}
                                else {
					$host_status = $host_status."<a href=main.php?p=201&o=hd&host_name=".urlencode($host_name)." ><img src=\"modules/gmap/img/red-dot.png\"></a>";
					$location_status = "DOWN";
				}
			}
		
			$location_critical = strpos($host_status,"red-dot");
			$location_warn = strpos($host_status,"yellow-dot");
			if($location_critical === TRUE) {
				$newnode->setAttribute("location_status", "DOWN");
			}
			elseif($location_warn === TRUE) {
				$newnode->setAttribute("location_status", "WARN");
			}
			else {
				$newnode->setAttribute("location_status", "UP");
			}
			$newnode2 = $node->appendChild($dom->createTextNode("".$marker['name']." <br> $host_status "));
		}
		
                echo $dom->saveXML();



?>
