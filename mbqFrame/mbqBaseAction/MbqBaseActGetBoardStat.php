<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_online_users action
 */
Abstract Class MbqBaseActGetBoardStat extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
          
        }
        else
        {
        }
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        $oMbqRdEtSysStatistics = MbqMain::$oClk->newObj('MbqRdEtSysStatistics');
        $oMbqEtSysStatistics = $oMbqRdEtSysStatistics->initOMbqEtSysStatistics();
        $this->data = $oMbqRdEtSysStatistics->returnApiDataSysStatistics($oMbqEtSysStatistics);
        $this->data['result'] = true;
    }
}