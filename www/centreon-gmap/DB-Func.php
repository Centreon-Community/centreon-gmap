<?
/**************************************************
Centreon GMAP Module version: 1.1
This module is licensed under the GPLv2
http://www.gnu.org/licenses/gpl.txt
Developped by : Justin Guagliata
Version: 1.1
Designed for: Centreon v2
For information : justin@ensgrp.com or www.ensgrp.com
**************************************************/
	
	function createMapLocations($pearDB,$oreon,$location_array){
		global $smarty_hg_dot,$smarty_host_dot;
		
		$lat = $location_array[0];
		$long = $location_array[1];
		$h_id = $location_array[2];
		$hg_id = $location_array[3];
		$l_id = $location_array[4];
	
		if ($hg_id == 0) {
			$DBRESULT =& $pearDB->query("SELECT host_id as id,host_name as name FROM host WHERE host_id='$h_id'");
		}
		if ($hg_id > 0)  {
			$DBRESULT4 =& $pearDB->query("SELECT hg_id as id,hg_name as name FROM hostgroup WHERE hg_id='$hg_id'");
        	while ($location_n =& $DBRESULT4->fetchRow()) {
				$location_name = $location_n['name'];
			}
			
			$DBRESULT =& $pearDB->query("SELECT hostgroup_hg_id as id,host_name as name FROM hostgroup_relation JOIN host ON hostgroup_relation.host_host_id = host.host_id WHERE hostgroup_relation.hostgroup_hg_id='$hg_id'");
			//$DBRESULT =& $pearDB->query("SELECT hg_id as id,hg_name as name FROM hostgroup WHERE hg_id='$hg_id'");
			}
			if (PEAR::isError($DBRESULT)) {
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";	
			}
	        
	        $n = 0;
	        while ($new_map_location =& $DBRESULT->fetchRow()) {
	            $i = 0;
	            $location_nam = $new_map_location['name'];
				$location_id = $new_map_location['id'];
				## First, set the location dot as green
				$host_status = "<a href=main.php?p=2020202&host_name=".urlencode($location_name)."><img src=\"modules/gmap/img/green-dot.png\"></a>";
			
				$n++;
				if ($n == 10) {
					$host_status = $host_status."<br>";
					$n = 0;
				}
				$smarty_host_dot[$l_id][$location_name] = $host_status;
			}
			
			$n = 0;
			$hg_status = "iconUp";
				
			$smarty_hg_dot[$l_id][] = $location_nam;
			$smarty_hg_dot[$l_id][] = $hg_status;
			$smarty_hg_dot[$l_id][] = $lat;
			$smarty_hg_dot[$l_id][] = $long;
			$smarty_hg_dot[$l_id][] = $h_id;
			$smarty_hg_dot[$l_id][] = $l_id;
		}  
        $DBRESULT->free();

	    if (!isset($oreon)) {
           exit();
    }


	function readConfigOptions()	{
		global $pearDB, $form;
		
		$gmap_cfg = array();
		$res =& $pearDB->query("SELECT * FROM gmap_op WHERE id = '1' LIMIT 1");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		# Set base value
		$gmap_cfg = array_map("myDecode", $res->fetchRow());
		return $gmap_cfg;
	}	
	
	function updateGmapCFG($id = null)	{
		global $form, $pearDB, $oreon;
		
		if (!$id) { 
			return;
		}
		
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE `gmap_op` SET ";
		$rq .= "`api_key` = ";
		isset($ret["api_key"]) && $ret["api_key"] != NULL ? $rq .= "'".htmlentities($ret["api_key"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "`lat` = ";
		isset($ret["lat"]) && $ret["lat"] != NULL ? $rq .= "'".htmlentities($ret["lat"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "`long` = ";
		isset($ret["long"]) && $ret["long"] != NULL ? $rq .= "'".htmlentities($ret["long"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "`height` = ";
		isset($ret["height"]) && $ret["height"] != NULL ? $rq .= "'".htmlentities($ret["height"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "`width` = ";
		isset($ret["width"]) && $ret["width"] != NULL ? $rq .= "'".htmlentities($ret["width"], ENT_QUOTES)."', ": $rq .= "NULL ";
		$rq .= "`zoomLevel` = ";
		isset($ret["zoomLevel"]) && $ret["zoomLevel"] != NULL ? $rq .= "'".htmlentities($ret["zoomLevel"], ENT_QUOTES)."' ": $rq .= "NULL ";
		$rq .= "WHERE `id` = '".$id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT)) {
			print $DBRESULT->getDebugInfo()."<br>";
		}
	}



	function getHostgroupLatLong2($hostOrGroup)  {
		global $smarty_host_list, $smarty_host, $pearDB, $oreon;
		
        $graphTs = array(NULL=>NULL);
		if ($hostOrGroup == 'host') {
        	$DBRESULT =& $pearDB->query("SELECT * FROM locations where hg_id IS NULL");
		}
		if ($hostOrGroup == 'group') {
        	$DBRESULT =& $pearDB->query("SELECT * FROM locations where h_id IS NULL");
        }

        if (PEAR::isError($DBRESULT)) {
    	    print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
        }
        while ($locations =& $DBRESULT->fetchRow()) {
			if ($hostOrGroup == 'host') {
				$host_name = $locations['h_id'];
			} else {
				$host_name = $locations['hg_id'];
			}
			$smarty_host[$host_name][] = $locations['l_id'];
		   	$smarty_host[$host_name][] = $locations['h_id'];
			$smarty_host[$host_name][] = $locations['hg_id'];
			$smarty_host[$host_name][] = $locations['address'];
			$smarty_host[$host_name][] = $locations['lat'];
			$smarty_host[$host_name][] = $locations['long'];
			
	    }
        $DBRESULT->free();
        $DBRESULT =& $pearDB->query("SELECT * FROM host WHERE host_register='1'");
        if (PEAR::isError($DBRESULT)) {
        	print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
        }
        while ($host =& $DBRESULT->fetchRow()) {
			$smarty_host_list[$host['host_id']] = $host['host_name'];
	   	}
       	$DBRESULT->free();
	}
	
	function getHostGroupList() {
		global $smarty_hostgroup_list, $pearDB, $oreon;

		$DBRESULT =& $pearDB->query("SELECT * FROM hostgroup WHERE hg_activate='1' ORDER BY hg_name");
        if (PEAR::isError($DBRESULT)) {
                print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
        }
	    while ($hostgroup =& $DBRESULT->fetchInto()) {
        	$smarty_hostgroup_list[$hostgroup['hg_id']] = $hostgroup['hg_name'];
        }
        $DBRESULT->free();
	}
        
	function getHostList() {
        global $smarty_hostgroup_list, $pearDB, $oreon;

        $DBRESULT =& $pearDB->query("SELECT * FROM host WHERE host_activate='1' AND host_register='1' ORDER BY host_name");
        if (PEAR::isError($DBRESULT)) {
        	print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
        }
	    while ($host =& $DBRESULT->fetchRow()) {
        	$smarty_hostgroup_list[$host['host_id']] = $host['host_name'];
        }
        $DBRESULT->free();
	}

	function getValidLocations($pearDB,$oreon)  {
	 	$DBRESULT =& $pearDB->query("SELECT * FROM locations WHERE `lat` IS NOT NULL AND `long` IS NOT NULL");
	    if (PEAR::isError($DBRESULT))
	        print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	    while ($coors =& $DBRESULT->fetchRow()) {
	  		$l_id = $coors['l_id'];
			$lat = $coors['lat'];	
	       	$long = $coors['long'];
			$h_id = $coors['h_id'];
			$hg_id = $coors['hg_id'];
	    	$locations[$l_id][] = $lat;
	    	$locations[$l_id][] = $long;
	    	$locations[$l_id][] = $h_id;
	    	$locations[$l_id][] = $hg_id;
			$locations[$l_id][] = $l_id;
		}
	    ## return valid location array 
	    return $locations;
	}
?>