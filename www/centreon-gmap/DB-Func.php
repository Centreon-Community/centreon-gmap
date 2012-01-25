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

	function readConfigOptions()	{
		global $pearDB, $form;
		
		$gmap_cfg = array();
		$res =& $pearDB->query("SELECT * FROM mod_gmap_options WHERE id = '1' LIMIT 1");
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
		$rq = "UPDATE `mod_gmap_options` SET ";
		$rq .= "`lat` = ";
		isset($ret["lat"]) && $ret["lat"] != NULL ? $rq .= "'".htmlentities($ret["lat"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "`long` = ";
		isset($ret["long"]) && $ret["long"] != NULL ? $rq .= "'".htmlentities($ret["long"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "`height` = ";
		isset($ret["height"]) && $ret["height"] != NULL ? $rq .= "'".htmlentities($ret["height"], ENT_QUOTES)."', ": $rq .= "NULL, ";
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
	 while($DBRESULT->fetchInto($hostgroup)) {
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

?>
