<?php

defined('MBQ_IN_IT') or exit;

/**
 * subscribe module field definition class
 */
Abstract Class MbqFdtSubscribe extends MbqBaseFdt {
    
    public static $df = array(
        'MbqEtSubscribe' => array(
            'subscribeMode' => array(
                'range' => array(
                    'noEmailNotificationOrThroughMyControlPanelOnly' => 0,
                    'instantNotificationByEmail' => 1,
                    'dailyUpdatesByEmail' => 2,
                    'weeklyUpdatesByEmail' => 3
                )
            ),
            'type' => array(
                'range' => array(
                    'forum' => 'forum',
                    'topic' => 'topic'
                )
            )
        )
    );
  
}
MbqBaseFdt::$df['MbqFdtSubscribe'] = &MbqFdtSubscribe::$df;
