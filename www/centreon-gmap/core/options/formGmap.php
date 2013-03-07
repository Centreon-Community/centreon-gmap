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
    exit();
}

isset($_GET["id"]) ? $sG = $_GET["id"] : $sG = NULL;
isset($_POST["id"]) ? $sP = $_POST["id"] : $sP = NULL;
$sG ? $id = $sG : $id = $sP;

/* 
 * Pear library
 */
require_once "HTML/QuickForm.php";
require_once 'HTML/QuickForm/advmultiselect.php';
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

/*
 * Path to the configuration dir
 */
global $path;
$path = "./modules/centreon-gmap/";

/*
 * PHP functions
 */
require_once $path."DB-Func.php";		

/*
 * Get options
 */
global $sopt;
$sopt = readConfigOptions($pearDB,$oreon);

/*
 * Parameters
 */
$attrsTextHeight	= array("size"=>"10");
$attrsTextLong		= array("size"=>"10", "id"=>"lng", "readonly"=>"readonly");
$attrsTextLat		= array("size"=>"10", "id"=>"lat", "readonly"=>"readonly");
$attrsZoom			= array("size"=>"10", "id"=>"zoomLevel", "readonly"=>"readonly");

$valid = false;

/*
 * Form begin
 */
$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
$form->addElement('header', 'title', _("Gmap Module Options"));
$form->addElement('header', 'gmap_header', _("Gmap Module Options"));
$form->addElement('text', 'lat', _("Latitude"), $attrsTextLat);
$form->addElement('text', 'lng', _("Longitude"), $attrsTextLong);
$form->addElement('text', 'height', _("Map Height"), $attrsTextHeight);
$form->addElement('text', 'zoomLevel', _("Zoom Level"), $attrsZoom);
$form->addElement('hidden', 'id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

/*
 * Form Rules
 */
function slash($elem = NULL) {
    if ($elem) {
        return rtrim($elem, "/")."/";
    }
}

$form->addRule('height', _("You need to fix a map height"), 'required', '', 'client');
$form->setJsWarnings(_("Input Error"), "");

$form->applyFilter('_ALL_', 'trim');
$form->setDefaults($sopt);
$form->addElement('submit', 'submitC', _("Save"));

/* 
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

if ($form->validate()) {
    updateGmapCFG($form->getSubmitValue("id"));
    $o = "w";
    $valid = true;
    $sopt = readConfigOptions($pearDB,$oreon);
}

/*
 * Apply a template definition
 */
$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('o', $o);
$tpl->assign('valid', $valid);
$tpl->assign("gmap_lat", $sopt['lat']);
$tpl->assign("gmap_lng", $sopt['lng']);
$tpl->assign("gmap_zoom", $sopt['zoomLevel']);
$tpl->assign("text_opt", _("To center the map on a point, take the marker and move it wherever you want on the map.<br/>"
                           ."You can also select the + and - to set zoom level.<br/>"
                           ."It is impossible to edit by hand the lngitude, latitude and zoom values. Only the map height is editable by hand.<br/><br/>"));
$tpl->display("formGmap.ihtml");

?>