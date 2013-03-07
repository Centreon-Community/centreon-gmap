<?php

class centreonGmap {

    var $_hostState;
    var $_serviceState;
    var $_hgState;

    var $_colorHosts;
    var $_colorHostGroup;
    var $_colorServices;

    /*
     * Construct Method
     */
    public function init() {  
        $this->_hostState = array("UP", "DOWN", "UNREACHABLE", "UNREACHABLE", "UNREACHABLE");
        $this->_hgState = array("OK", "NON-OK");
        $this->_serviceState = array("OK", "WARNING", "CRITICAL", "UNKNOWN", "PENDDING");
        
        $this->_colorHost = array("green", "red", "orange");
        $this->_colorHostGroup = array("green", "red");
        $this->_colorService = array("green", "orange", "red", "grey", "grey");
    }

    public function getHostStateString($state) {
        if (isset($this->_hostState[$state])) {
            return $this->_hostState[$state];
        } else {
            return "N/A";
        }
    }

    public function getHGStateString($state) {
        if (isset($this->_hgState[$state])) {
            return $this->_hgState[$state];
        } else {
            return "N/A";
        }
    }
    
    public function getServiceStateString($state) {
        if (isset($this->_serviceState[$state])) {
            return $this->_serviceState[$state];
        } else {
            return "N/A";
        }
    }
    
    public function getHostStateColor($state) {
        if (isset($this->_colorHost[$state])) {
            return $this->_colorHost[$state];
        } else {
            return "N/A";
        }
    }

    public function getHGStateColor($state) {
        if (isset($this->_colorHostGroup[$state])) {
            return $this->_colorHostGroup[$state];
        } else {
            return "N/A";
        }
    }

    public function getServiceStateColor($state) {
        if (isset($this->_colorService[$state])) {
            return $this->_colorService[$state];
        } else {
            return "N/A";
        }
    }

}