<?php

defined('MBQ_IN_IT') or exit;

/**
 * attachment field definition class
 */
Abstract Class MbqFdtAtt extends MbqBaseFdt {
    
    public static $df = array(
        'MbqEtAtt' => array(
            'attType' => array(
                'range' => array(
                    'forumPostAtt' => 'forumPostAtt',
                    'userAvatar' => 'userAvatar',
                    'pcMsgAtt' => 'pcMsgAtt'
                )
            ),
            'contentType' => array(
                'range' => array(
                    'image' => 'image',
                    'pdf' => 'pdf',
                    'other' => 'other'
                )
            )
        )
    );
  
}
MbqBaseFdt::$df['MbqFdtAtt'] = &MbqFdtAtt::$df;
