<?php
/**************************************************
Centreon GMAP Module version: 1.2.1
This module is licensed under the GPLv2
http://www.gnu.org/licenses/gpl.txt
Developped by : Justin Guagliata and Julien Mathis
Version: 1.2.1
Designed for: Centreon v2
For information : justin@ensgrp.com or www.ensgrp.com
**************************************************/

ini_set("display_errors", "On");

/*
 * Configuration file
 */
include_once "/etc/centreon/centreon.conf.php";

/*
 * Include centreon classes
 */
include_once $centreon_path . "www/class/centreonDuration.class.php";
include_once $centreon_path . "www/class/centreonGMT.class.php";
include_once $centreon_path . "www/class/centreonXML.class.php";
include_once $centreon_path . "www/class/centreonDB.class.php";
include_once $centreon_path . "www/class/centreonBroker.class.php";
include_once $centreon_path . "www/class/centreonSession.class.php";
include_once $centreon_path . "www/class/centreon.class.php";
include_once $centreon_path . "www/class/centreonLang.class.php";
include_once $centreon_path . "www/class/centreonACL.class.php";
include_once $centreon_path . "www/include/common/common-Func.php";

/*
 * Include Centreon-GMAP Classes
 */
include_once $centreon_path . "www/modules/centreon-gmap/core/class/gmap.class.php";
include_once $centreon_path . "www/modules/centreon-gmap/core/class/gmap-ndo.class.php";
include_once $centreon_path . "www/modules/centreon-gmap/core/class/gmap-broker.class.php";

session_start();

/*
 * Tab status
 */
$hostState = array("UP", "DOWN", "UNREACHABLE", "UNREACHABLE", "UNREACHABLE");
$hgState = array("OK", "NON-OK");
$serviceState = array("OK", "WARNING", "CRITICAL", "UNKNOWN", "PENDDING");

$tabColorHosts = array("green", "red", "orange");
$tabColorHostGroup = array("green", "red");
$tabColorServices = array("green", "orange", "red", "grey", "grey");

/*
 * Call DB connector
 */
$pearDB = new CentreonDB();    

/*
 * Init Centreon-broker obj
 */
$brokerObj = new CentreonBroker($pearDB);

if ($brokerObj->getBroker() == "ndo") {
  $pearDBndo = new CentreonDB("ndo");
  $ndo_prefix = getNDOPrefix();
  $gmapObj = new centreonGmapNDO($pearDB, $pearDBndo, $ndo_prefix);
  $gmapObj->init();
} else {
  $pearDBC = new centreonDB("centstorage");
  $gmapObj = new centreonGmapBroker($pearDB, $pearDBC);
  $gmapObj->init();
}

/*
 * Analyse User Profile
 */
$session = htmlentities($_GET["session_id"], ENT_QUOTES);
$DBRESULT = $gmapObj->_db->query("SELECT user_id FROM session WHERE session_id = '$session'");
$data = $DBRESULT->fetchRow();
$user_id = $data["user_id"];
unset($data);

/*
 * Access Control List
 */
$admin = 0;
$aclObj = new CentreonACL($user_id);
if ($aclObj->admin == 0) {
    // Get Group List
    $accessGroup = $aclObj->getAccessGroups();
    $groupList = "";
    foreach ($accessGroup as $key => $value) {
        if ($groupList != '') {
            $groupList .= ", ";
        }
        $groupList .= "'".$key."'";
    }
    $hostGroupList = $aclObj->getHostGroupsString();
} else {
    $admin = 1;
}

/*
 * Create DOM
 */
$dom = new DOMDocument("1.0");
$node = $dom->createElement("markers");
$parnode = $dom->appendChild($node);

if ($admin == 1) {
    $DBRESULT = $gmapObj->_db->query("SELECT DISTINCT host.host_name AS name, host.host_alias AS alias, locations.lat AS lat, ". 
                                     "locations.address AS addr, locations.lng AS lng, locations.h_id AS h_id, locations.hg_id AS hg_id ".
                                     "FROM locations, host, hostgroup ".
                                     "WHERE locations.h_id = host.host_id ".
                                     "UNION ".
                                     "SELECT hostgroup.hg_name as name, hostgroup.hg_alias AS alias, ".
                                     "locations.lat as lat, ".
                                     "locations.address as addr, ".
                                     "locations.lng as lng, ".
                                     "locations.h_id as hid, ".
                                     "locations.hg_id AS hg_id ".
                                     "FROM locations,hostgroup ".
                                     "WHERE locations.hg_id = hostgroup.hg_id ".
                                     "ORDER BY name ASC");
} else {
    $DBRESULT = $gmapObj->_db->query("SELECT DISTINCT host.host_name AS name, host.host_alias AS alias, locations.lat AS lat, ". 
                                     "locations.address AS addr, locations.lng AS lng, locations.h_id AS h_id, locations.hg_id AS hg_id ".
                                     "FROM locations, host, hostgroup, centreon_storage.centreon_acl acl ".
                                     "WHERE locations.h_id = host.host_id AND acl.host_id = host.host_id AND acl.group_id IN ($groupList) ".
                                     "UNION ".
                                     "SELECT hostgroup.hg_name as name, hostgroup.hg_alias AS alias, ".
                                     "locations.lat as lat, ".
                                     "locations.address as addr, ".
                                     "locations.lng as lng, ".
                                     "locations.h_id as hid, ".
                                     "locations.hg_id AS hg_id ".
                                     "FROM locations, hostgroup ".
                                     "WHERE locations.hg_id = hostgroup.hg_id AND hostgroup.hg_id IN ($hostGroupList) ".
                                     "ORDER BY name ASC");
}
if (PEAR::isError($DBRESULT)) {
    print "DB Error : ".$DBRESULT->getDebugInfo()."<br/>";
}
while ($marker = $DBRESULT->fetchRow()) {
    $popupString = "";
    
    $node = $dom->createElement("location");
    $newnode = $parnode->appendChild($node);
  
    $newnode->setAttribute("name",$marker['name']);
    $newnode->setAttribute("lat", $marker['lat']);
    $newnode->setAttribute("lng", $marker['lng']);

    if ($marker['hg_id'] == NULL) {

        $information = $gmapObj->getHostState($marker['name']);

        if (!isset($information['current_state'])) {
            $information["current_state"] = 2;
        }

        $newnode->setAttribute("location_status", isset($hostState[$information["current_state"]]) ? $hostState[$information["current_state"]] : $hostState[2]);

        // Icone Management
        if (isset($information['icon_image']) && $information["icon_image"] != "") {
            $information["icon_image"] = "./img/media/".$information["icon_image"];
            $popupString .= "<span><img src='".$information["icon_image"]."' width='25px' heigth='25px' style='padding-right:10px;'>";
        } else {
            $information["icon_image"] = "./img/icones/16x16/server_network.gif";
            $popupString .= "<img src='".$information["icon_image"]."' width='20px' heigth='20px' style='padding-right:10px;'>";
        }
        $newnode->setAttribute("icon", $information["icon_image"]);
        
        $popupString .= $marker['name']." (".$marker["alias"].")</span><br/><br/>";
        $popupString .= "<b>"._("Host address:")."</b> ".(isset($information['address']) ? $information['address'] : "N/A")."<br/>";	
        if (isset($tabColorHosts[$hostState[$information["current_state"]]]) && isset($information["current_state"])) {
            $popupString .= "<b>"._("Status:")."<font color='".$gmapObj->getHostStateColor((isset($hostState[$information["current_state"]]) ? $hostState[$information["current_state"]] : $hostState[2]))."'>"." ".$gmapObj->getHostStateString($information["current_state"])."</font></b><br/>";
        }
    
        if ($information["current_state"]) {
            $popupString .= "<b>"._("Acknownledged:")."</b>"." ".(isset($information["problem_has_been_acknowledged"]) && $information["problem_has_been_acknowledged"] > 0 ? _("Yes") : _("No"))."<br/>";
        }

        $popupString .= "<b>"._("Last check:")."</b>"." ".(isset($information["last_check"]) ? date("j/m/Y h:m", $information["last_check"]) : "N/A")."<br/>";
        $popupString .= "<b>"._("Location :")."</b> ".$marker['addr']."<br/><br/>";

        // Services States
        $stateService = $gmapObj->ServiceStatusPerHost($marker['name']);
        if (count($stateService) != 0) {
            $popupString .= "<b><u>"._("Services Status").":</u></b><br>";
            foreach ($stateService as $state => $number) {
                if ($number != 0) {
                    $popupString .= "&ordm; $number <font color='".$gmapObj->getServiceStateColor($state)."'>".$gmapObj->getServiceStateString($state)."</font></b><br/>";
                }
            }
        }

        // Display services problems
        $problemList = $gmapObj->getProblemsForHosts($marker["name"]);        
        if (count($problemList)) {
            $popupString .= "<br/><b><u>"._("Details Problems:")."</u></b>"."<br/>";
            foreach ($problemList as $key => $value) {
                $popupString .= "$key is <font color='".$tabColorServices[$value["state"]]."'>".$serviceState[$value['state']]."</font> (".CentreonDuration::toString(time() - $value['last_hard_state_change']).")<br/>";
            }
        }
        
        $popupString .= "<br/><br/><center><a href='?p=20201&o=svc&host_search=".$marker['name']."&strict=1&hostgroup='>"._("Show Services Details")."</a></center>";
    
    } else {

        $information = $gmapObj->getBrokerIcon($marker['name']);

        $hgStatus = $gmapObj->getHGState($marker["name"]);
        $newnode->setAttribute("location_status", $gmapObj->getHGStateString($hgStatus));
        
        // Icone Management
        $ico = $gmapObj->getBrokerIcon($marker['name']);
        if ($ico <= 0) {
            $information["icon_image"] = "./img/icones/16x16/clients.gif";
            $popupString .= "<img src='".$information["icon_image"]."' width='20px' heigth='20px' style='padding-right:10px;'>";
        } else {
            $information["icon_image"] = "./img/media/".$information["icon_image"];
            $popupString .= "<span><img src='".$information["icon_image"]."' width='25px' heigth='25px' style='padding-right:10px;'>";
        }
        $newnode->setAttribute("icon", $information["icon_image"]);
        
        $popupString .= "<b>"._("Host Group:")."</b> ".$marker['name']." (".$marker["alias"].")<br/><br/>";
        $popupString .= "<b>"._("Host Group Status:")."</b> ".$gmapObj->getHGStateString($hgStatus)."<br/>";
        $popupString .= "<b>"._("Location :")."</b> ".$marker['addr']."<br/><br/>";
        
        // Host Details
        $stateHost = $gmapObj->getHostHostGroupState($marker['name']);
        if (count($stateHost) != 0) {
            $popupString .= "<b><u>"._("Host Status").":</u></b><br>";
            foreach ($stateHost as $state => $number) {
                if ($number != 0) {
                    $popupString .= "&ordm; $number <font color='".$gmapObj->getHostStateColor($state)."'>".$gmapObj->getHostStateString($state)."</font></b><br/>";
                }
            }
        }
        $popupString .= "<br/>";

        // Services Details
        $stateService = $gmapObj->ServiceStatusPerHostGroup($marker['name']);
        if (count($stateService) != 0) {
            $popupString .= "<b><u>"._("Services Status").":</u></b><br>";
            foreach ($stateService as $state => $number) {
                if ($number != 0) {
                    $popupString .= "&ordm; $number <font color='".$gmapObj->getServiceStateColor($state)."'>".$gmapObj->getServiceStateString($state)."</font></b><br/>";
                }
            }
        }

        // Display Host problems
        $problemList = $gmapObj->getHostProblemsForHostGroup($marker["name"]);        
        if (count($problemList)) {
            $popupString .= "<br/><b><u>"._("Details Problems:")."</u></b>"."<br/>";
            foreach ($problemList as $key => $value) {
                $popupString .= "$key is <font color='".$gmapObj->getHostStateColor($value["state"])."'>".$gmapObj->getHostStateString($value['state'])."</font> (".CentreonDuration::toString(time() - $value['last_hard_state_change']).")<br/>";
            }
        }

        // Display Service problems
        $problemList = $gmapObj->getProblemsForHostGroup($marker["name"]);        
        if (count($problemList)) {
            $popupString .= "<br/><b><u>"._("Details Problems:")."</u></b>"."<br/>";
            foreach ($problemList as $key => $value) {
                $popupString .= "$key is <font color='".$tabColorServices[$value["state"]]."'>".$serviceState[$value['state']]."</font> (".CentreonDuration::toString(time() - $value['last_hard_state_change']).")<br/>";
            }
        }

        /* 
         * Get HG Filter ids.
         */
        $hostGroupIDList = $gmapObj->getHostGroupIDList($marker['name']);

        $popupString .= "<br/><br/><center><a href='?p=20201&o=svc&hostgroup=$hostGroupIDList'>"._("Show Services Details")."</a></center>";
    }
    $newnode2 = $node->appendChild($dom->createTextNode($popupString));
}
$DBRESULT->free();

/*
 * Send HEader
 */
header("Content-type: text/xml");

/*
 * Write XML
 */
echo $dom->saveXML();

?>