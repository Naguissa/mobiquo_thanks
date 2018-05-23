<?php

defined('MBQ_IN_IT') or exit;

/**
 * follow module field definition class
 */
Abstract Class MbqFdtFollow extends MbqBaseFdt {
    
    public static $df = array(
        
    );
  
}
MbqBaseFdt::$df['MbqFdtFollow'] = &MbqFdtFollow::$df;
