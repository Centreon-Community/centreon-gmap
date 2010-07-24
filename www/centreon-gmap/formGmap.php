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

	if (!isset($oreon))
		exit();
	
	isset($_GET["id"]) ? $sG = $_GET["id"] : $sG = NULL;
	isset($_POST["id"]) ? $sP = $_POST["id"] : $sP = NULL;
	$sG ? $id = $sG : $id = $sP;
		
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the configuration dir
	global $path;
	$path = "./modules/centreon-gmap/";

	#PHP functions
	require_once $path."DB-Func.php";		
		

	$sopt = readConfigOptions($pearDB,$oreon);

	$attrsText 		= array("size"=>"126");
	$attrsText2		= array("size"=>"20");
	$attrsText3		= array("size"=>"10");
	$attrsAdvSelect = null;
	$zoomLevel		= array('0' => '0', 
							'1' => '1',
							'2' => '2',
							'3' => '3',
							'4' => '4',
							'5' => '5',
							'6' => '6',
							'7' => '7',
							'8' => '8',
							'9' => '9',
							'10' => '10',
							'11' => '11',
							'12' => '12',
							'13' => '13',
							'14' => '14',
							'15' => '15',
							'16' => '16',
							'17' => '17',
							'18' => '18',
							'19' => '19');
	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Gmap Module Options"));
	$form->addElement('header', 'gmap_header', _("Gmap Module Options"));
	$form->addElement('text', 'api_key', _("Google API Key"), $attrsText );
	$form->addElement('text', 'lat', _("Lattitude"), $attrsText3 );
	$form->addElement('text', 'long', _("Longitude"), $attrsText3 );
	$form->addElement('text', 'height', _("Height"), $attrsText3 );
	$form->addElement('text', 'width', _("Width"), $attrsText3 );
	$form->addElement('select', 'zoomLevel', _("Zoom Level"), $zoomLevel);
	
	$form->addElement('hidden', 'id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	#
	## Form Rules
	#
	function slash($elem = NULL)	{
		if ($elem)
			return rtrim($elem, "/")."/";
	}
	$form->applyFilter('_ALL_', 'trim');


	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	$form->setDefaults($sopt);
	

	$subC =& $form->addElement('submit', 'submitC', _("Save"));
	$DBRESULT =& $form->addElement('reset', 'reset', _("Reset"));


    $valid = false;
	if ($form->validate())	{

		updateGmapCFG($form->getSubmitValue("id"));
		$o = "w";
   		$valid = true;
		$form->freeze();

	}
	if (!$form->validate() && isset($_POST["id"]))	{
	    print("<div class='msg' align='center'>".$lang["quickFormError"]."</div>");
	}

	$form->addElement('button', 'change', _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=gmap_opt'"));

	#
	##Apply a template definition
	#

	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('valid', $valid);
	$tpl->display("formGmap.ihtml");
?>
