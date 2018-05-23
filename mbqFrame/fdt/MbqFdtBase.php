<?php

defined('MBQ_IN_IT') or exit;

/**
 * base module field definition class
 */
Abstract Class MbqFdtBase extends MbqBaseFdt {
    
    public static $df = array(
        
    );
  
}
MbqBaseFdt::$df['MbqFdtBase'] = &MbqFdtBase::$df;
