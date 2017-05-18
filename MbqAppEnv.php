<?php
defined('MBQ_IN_IT') or exit;

/**
 * application environment class
 */
Class MbqAppEnv extends MbqBaseAppEnv {
    
    /* this class fully relys on the application,so you can define the properties what you need come from the application. */
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * application environment init
     */
    public function init() {
        global $auth, $user, $request, $phpbb_root_path, $phpEx, $phpbb_home;
        $request->enable_super_globals();
        $user->session_begin();
        $auth->acl($user->data);
        $user->setup();
        $phpbb_home = generate_board_url().'/';
        if($user->data['is_registered'] == true)
        {
            MbqMain::$oMbqAppEnv->currentUserInfo = $user->data;
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            $oMbqRdEtUser->initOCurMbqEtUser($user->data['user_id']);
        }
         
     }
   
}
