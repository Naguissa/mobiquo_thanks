<?php

defined('MBQ_IN_IT') or exit;

/**
 *
 */
Abstract Class MbqBaseActGetSmilies extends MbqBaseAct {

    public function __construct() {
        parent::__construct();
    }

    function getInput()
    {
        $in = new stdClass();
        return $in;
    }

    /**
     * action implement
     */
    protected function actionImplement($in) {
        $oMbqRdCommon = MbqMain::$oClk->newObj('MbqRdCommon');
        $result = $oMbqRdCommon->getSmilies();
        if(is_array($result))
        {
            $this->data['list'] = $oMbqRdCommon->returnApiArrDataSmilies($result);
        }
        else
        {
            $this->data['result'] = false;
            $this->data['result_text'] = $result;
        }
    }

}