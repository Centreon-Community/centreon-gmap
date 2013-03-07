<?php

class centreonGmapBroker extends centreonGmap {
    var $_db;
    var $_dbc;
    var $prefix = "";

    /*
     * Constructor
     */
    public function __construct($db, $dbc) {
        $this->_db = $db;
        $this->_dbc = $dbc;
    }
    
    public function getProblemsForHosts($host_name) {
        $state = array();

        $DBRESULT = $this->_dbc->query("SELECT s.description, s.state, s.last_hard_state_change FROM services s, hosts h WHERE h.host_id = s.host_id AND h.name = '".$host_name."' AND s.enabled = 1 AND s.state <> '0' ORDER BY state ASC, description ASC");
        while ($data = $DBRESULT->fetchRow()) {
            $state[$data['description']] = array("state" => $data['state'], "last_hard_state_change" => $data["last_hard_state_change"]);	
        }
        return($state);
    }

    public function getProblemsForHostGroup($hg_name) {
        $state = array();

        $hostList = "";
        $DBRESULT = $this->_dbc->query("SELECT host_id from hostgroups hg, hosts_hostgroups hhg WHERE hg.name LIKE '$hg_name' AND hg.hostgroup_id = hhg.hostgroup_id");
        while ($data = $DBRESULT->fetchRow()) {
            if ($hostList != "") {
                $hostList .= ", ";
            }
            $hostList .= "'".$data["host_id"]."'";
        }

        if ($hostList != "") {
            $DBRESULT = $this->_dbc->query("SELECT h.name, s.description, s.state, s.last_hard_state_change FROM services s, hosts h WHERE h.host_id = s.host_id AND h.host_id IN ($hostList) AND s.enabled = 1 AND s.state <> '0' ORDER BY state ASC, description ASC LIMIT 10");
            while ($data = $DBRESULT->fetchRow()) {
                $state[$data["name"]." / ".$data['description']] = array("state" => $data['state'], "last_hard_state_change" => $data["last_hard_state_change"]);	
            }
        }
        return($state);
    }

    public function getHostProblemsForHostGroup($hg_name) {
        $state = array();

        $hostList = "";
        $DBRESULT = $this->_dbc->query("SELECT host_id from hostgroups hg, hosts_hostgroups hhg WHERE hg.name LIKE '$hg_name' AND hg.hostgroup_id = hhg.hostgroup_id");
        while ($data = $DBRESULT->fetchRow()) {
            if ($hostList != "") {
                $hostList .= ", ";
            }
            $hostList .= "'".$data["host_id"]."'";
        }

        if ($hostList != "") {
            $DBRESULT = $this->_dbc->query("SELECT name, state, last_hard_state_change FROM hosts WHERE host_id IN ($hostList) AND enabled = 1 AND state <> '0' ORDER BY state ASC, name ASC LIMIT 10");
            if (PEAR::isError($DBRESULT)) {
                print "DB Error : ".$DBRESULT->getDebugInfo()."<br/>";
            }
            while ($data = $DBRESULT->fetchRow()) {
                $state[$data["name"]] = array("state" => $data['state'], "last_hard_state_change" => $data["last_hard_state_change"]);	
            }
        }
        return($state);
    }
    
    /* 
     * We grab a list of Services and check the status for each host
     * Returns either UP, DOWN or WARN
     */
    public function ServiceStatusPerHost($host_name) { 
        $state = array();
        for ($i = 4; $i != -1; $i--) {
            $state[$i] = 0;
        }

        /*
         * Get Results
         */
        $DBRESULT = $this->_dbc->query("SELECT s.state FROM services s, hosts h WHERE h.host_id = s.host_id AND h.name = '".$host_name."' AND s.enabled = 1");
        while ($data = $DBRESULT->fetchRow()) {
            $state[$data['state']]++;	
        }
        return $state;
    }

    public function ServiceStatusPerHostGroup($hg_name) { 
        $state = array();
        for ($i = 4; $i != -1; $i--) {
            $state[$i] = 0;
        }

        $hostList = "";
        $DBRESULT = $this->_dbc->query("SELECT host_id from hostgroups hg, hosts_hostgroups hhg WHERE hg.name LIKE '$hg_name' AND hg.hostgroup_id = hhg.hostgroup_id");
        while ($data = $DBRESULT->fetchRow()) {
            if ($hostList != "") {
                $hostList .= ", ";
            }
            $hostList .= "'".$data["host_id"]."'";
        }

        /*
         * Get Results
         */
        if ($hostList != "") {
            $DBRESULT = $this->_dbc->query("SELECT s.state FROM services s, hosts h WHERE h.host_id = s.host_id AND h.host_id IN (".$hostList.") AND s.enabled = 1");
            while ($data = $DBRESULT->fetchRow()) {
                $state[$data['state']]++;	
            }
        }
        return $state;
    }

    /**
     * Get only the Host state 
     *
     */
    public function getHostHostGroupState($hg_name) { 
        $state = array();
        for ($i = 4; $i != -1; $i--) {
            $state[$i] = 0;
        }
        
        $hostList = "";
        $DBRESULT = $this->_dbc->query("SELECT host_id from hostgroups hg, hosts_hostgroups hhg WHERE hg.name LIKE '$hg_name' AND hg.hostgroup_id = hhg.hostgroup_id");
        while ($data = $DBRESULT->fetchRow()) {
            if ($hostList != "") {
                $hostList .= ", ";
            }
            $hostList .= "'".$data["host_id"]."'";
        }

        /*
         * Get Results
         */
        if ($hostList != "") {
            $DBRESULT = $this->_dbc->query("SELECT state FROM hosts WHERE host_id IN ($hostList) AND enabled = 1");
            while ($data = $DBRESULT->fetchRow()) {
                $state[$data['state']]++;	
            }
        }
        return $state;
    }

    public function getHostStatus($host_name) { 
        $state = -1;

        /*
         * Get Results
         */
        $DBRESULT = $this->_dbc->query("SELECT state FROM hosts WHERE name = '$host_name' AND enabled = 1");
        while ($data = $DBRESULT->fetchRow()) {
            return($data['state']);	
        }
        return $state;
    }

    public function getHostState($host_name) {
        $rq1 =  " SELECT state as current_state," .
            " acknowledged AS problem_has_been_acknowledged, " .
            " last_check as last_check2, " .
            " output," .
            " last_check," .
            " address," .
            " name as host_name," .
            " action_url," .
            " notes_url," .
            " notes," .
            " icon_image," .
            " icon_image_alt," .
            " max_check_attempts," .
            " state_type," .
            " check_attempt AS current_check_attempt, " .
            " scheduled_downtime_depth" .
            " FROM hosts " .
            " WHERE name LIKE '$host_name' AND enabled = '1' LIMIT 1";    
        $DBRESULT = $this->_dbc->query($rq1);
        $data =& $DBRESULT->fetchRow();
        return($data);
    }

    public function getBrokerIcon($hgName) {
        if (!isset($hgName) || $hgName == "") {
            return -1;
        }

        $DBRESULT = $this->_db->query("SELECT hg_icon_image FROM hostgroup WHERE hg_name LIKE '$hgName'");
        $data =& $DBRESULT->fetchRow();
        if (isset($data["hg_icon_image"])) {
            return $data["hg_icon_image"];
        }
        return -1;
    }

    /*
     * Return the status of an hostgroupe
     * OK : 0
     * NON-OK : 1
     *
     */
    public function getHGState($hg_name) { 
        $status = 0;
        
        $stateService = $this->ServiceStatusPerHostGroup($hg_name);
        foreach ($stateService as $key => $value) {
            if ($key != 0 && $value != 0) {
                $status = 1;
            }
        }
        if ($status == 1) {
            return($status);
        }

        $stateHost = $this->getHostHostGroupState($hg_name);
        foreach ($stateHost as $key => $value) {
            if ($key != 0 && $value != 0) {
                $status = 1;
            }
        }
        return($status);
    }

    public function getHostGroupIDList($hgName) {
        $idList = "";

        $DBRESULT = $this->_dbc->query("SELECT hostgroup_id FROM hostgroups WHERE name LIKE '".$hgName."'");
        while ($data = $DBRESULT->fetchRow()) {
            if ($idList != "") {
                $idList .= ",";
            }
            $idList .= $data["hostgroup_id"];
        }
        $DBRESULT->free();
        return($idList);
    }

}

?>