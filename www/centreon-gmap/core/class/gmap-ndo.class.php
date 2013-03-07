<?php

class centreonGmapNDO {

      
	/* 
	 * We grab a list of Services and check the status for each host
	 * Returns either UP, DOWN or WARN
	 */
	private function ServiceStatusPerHost($host_name) { 
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
	    
	    while ($data =& $DBRESULT_NDO->fetchRow()) {
		      $state = $data['state'];	
		      return $state;
	    }
    }

  	private function getHostState($host_name, $pearDBndo, $ndo_prefix) {
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




}


?>