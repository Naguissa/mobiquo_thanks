<?php

defined('MBQ_IN_IT') or exit;

/**
 * feed module field definition class
 */
Abstract Class MbqFdtFeed extends MbqBaseFdt {
    
    public static $df = array(
        'MbqEtFeed' => array(
            'type' => array(
                'range' => array(
                    'user' => 'user',
                    'topic' => 'topic',
                    'post' => 'post'
                )
            ),
            'newFeed' => array(
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            )
        )
    );
  
}
MbqBaseFdt::$df['MbqFdtFeed'] = &MbqFdtFeed::$df;
