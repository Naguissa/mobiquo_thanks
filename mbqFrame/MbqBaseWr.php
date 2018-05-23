<?php

defined('MBQ_IN_IT') or exit;

/**
 * write base class
 */
Abstract Class MbqBaseWr {
    
    public $neededMethod;   /* describe the methods that should be implemented in all extention class. */
    
    public function __construct() {
        $this->neededMethods = array();
    }
  
}
