<?php

defined('MBQ_IN_IT') or exit;

/**
 * user module field definition class
 */
Abstract Class MbqFdtUser extends MbqBaseFdt {
    
    public static $df = array(
        'MbqEtUser' => array(
            'canPm' => array(
                'default' => false,
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            ),
            'canSendPm' => array(
                'default' => false,
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            ),
            'canModerate' => array(
                'default' => false,
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            ),
            'canSearch' => array(
                'default' => false,
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            ),
            'canWhosonline' => array(
                'default' => false,
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            ),
             'canProfile' => array(
                'default' => true,
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            ),
            'canUploadAvatar' => array(
                'default' => false,
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            ),
            'isOnline' => array(
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            ),
            'acceptPm' => array(
                'default' => true,
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            ),
            'iFollowU' => array(
                'default' => false,
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            ),
            'uFollowMe' => array(
                'default' => false,
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            ),
            'acceptFollow' => array(
                'default' => true,
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            ),
            'canBan' => array(
                'default' => false,
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            ),
            'isBan' => array(
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            ),
            'canMarkSpam' => array(
                'default' => false,
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            ),
            'isSpam' => array(
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            ),
            'userType' => array(
                'default' => 'normal',
                'range' => array(
                    'banned' => 'banned',
                    'unapproved' => 'unapproved',
                    'inactive' => 'inactive',
                    'normal' => 'normal',
                    'mod' => 'mod',
                    'admin' => 'admin',
                )
            ),
            'canActive' => array(
                'default' => false,
                'range' => array(
                    'yes' => true,
                    'no' => false
                )
            )
        )
    );
  
}
MbqBaseFdt::$df['MbqFdtUser'] = &MbqFdtUser::$df;
