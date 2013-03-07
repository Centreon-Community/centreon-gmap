<?php
/**************************************************
Centreon GMAP Module version: 1.2.1
This module is licensed under the GPLv2
http://www.gnu.org/licenses/gpl.txt
Developped by : Justin Guagliata
Version: 1.2.1
Designed for: Centreon v2
For information : justin@ensgrp.com or www.ensgrp.com
**************************************************/

if (!isset($centreon)) {
    exit ();
}

/*
 * Pear library
 */
require_once "HTML/QuickForm.php";
require_once 'HTML/QuickForm/advmultiselect.php';
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

$form = new HTML_QuickForm();

/*
 * Path to the configuration dir
 */
global $path;
$path = "./modules/centreon-gmap/";

/*
 * PHP functions
 */
require_once $path."core/include/DB-Func.php";
require_once "./include/common/common-Func.php";

/*
 * define globals
 */
global $pearDB, $oreon;

/*
 * Legacy update host
 */
if (isset($_GET['l_id'])) {

    /*
     * Get parameters
     */
    $l_id = htmlentities($_GET['l_id'], ENT_QUOTES);
    $hg_id = htmlentities($_GET['hg_id'], ENT_QUOTES);
    $lat = htmlentities($_GET['lat'], ENT_QUOTES);
    $lng = htmlentities($_GET['lng'], ENT_QUOTES);
    $address = htmlentities($_GET['address'], ENT_QUOTES);
    $action = htmlentities($_GET['action'], ENT_QUOTES);	

    /*
     * Actions
     */
    if ($action == "new") {
        $DBRESULT =& $pearDB->query("INSERT into `mod_gmap_locations` SET `hg_id`='$hg_id',`lat`='$lat',`lng`='$lng',`address`='$address'");
    } else if ($action == "update") {
        $DBRESULT =& $pearDB->query("UPDATE `mod_gmap_locations` SET `hg_id`='$hg_id',`lat`='$lat',`lng`='$lng',`address`='$address' WHERE l_id='$l_id' LIMIT 1");
    } else if ($action == "delete") {
        $DBRESULT =& $pearDB->query("DELETE from `mod_gmap_locations` WHERE l_id='$l_id' LIMIT 1");
    }
    if (PEAR::isError($DBRESULT)) {
        print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
    }
}

// we pass either group or host
getHostgroupLatLong2('group');

getHostGroupList();
$mod_gmap_options = readConfigOptions($pearDB,$oreon);

$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);
$tpl->assign("h_names", $smarty_host);
$tpl->assign("host_list", $smarty_host_list);
$tpl->assign("hostgroup_list", $smarty_hostgroup_list);
$tpl->assign("gmap_zoom", $mod_gmap_options['zoomLevel']);
$tpl->assign("gmap_lat", $mod_gmap_options['lat']);
$tpl->assign("gmap_lng", $mod_gmap_options['lng']);

/*
 * translations
 */
$tpl->assign("host", _("HostGroups"));
$tpl->assign("address", _("Address, postal code, city, country"));
$tpl->assign("latitude", _("Latitude"));
$tpl->assign("lngitude", _("Longitude"));
$tpl->assign("actions", _("Actions"));
$tpl->assign("addLocation", _("Add a location for a host"));
$tpl->assign("messageAlert", _("Geocode was not successful for the following reason : "));

$tpl->display("gmap_config_hostGroup.ihtml");

?>
