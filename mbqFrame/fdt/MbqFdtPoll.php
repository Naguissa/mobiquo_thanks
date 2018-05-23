<?php

defined('MBQ_IN_IT') or exit;

/**
 * poll module field definition class
 */
Abstract Class MbqFdtPoll extends MbqBaseFdt {
    
    public static $df = array(
        'MbqEtPoll' => array(
            'canRevoting' => array(
                'default' => false,
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            ),
            'canViewBeforeVote' => array(
                'default' => false,
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            ),
            'canPublic' => array(
                'default' => false,
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            )
        )
    );
  
}
MbqBaseFdt::$df['MbqFdtPoll'] = &MbqFdtPoll::$df;
