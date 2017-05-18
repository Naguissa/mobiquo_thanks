<?php

defined('MBQ_IN_IT') or exit;

/**
 *
 */
Abstract Class MbqBaseActSyncUser extends MbqBaseAct {

    public function __construct() {
        parent::__construct();
    }

    function getInput()
    {
        $in = new stdClass();

        if(MbqMain::isRawPostProtocol())
        {
            $in->code = MbqMain::$input['code'];
            $in->start = MbqMain::$input['start'];
            $in->limit = MbqMain::$input['limit'];
        }
        include_once(MBQ_3RD_LIB_PATH . 'classTTConnection.php');
        $connection = new classTTConnection();
        $response = $connection->actionVerification($in->code,'sync_user');
        if(!$response)
        {
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_PARAMS_ERROR);
        }
        return $in;
    }

    /**
     * action implement
     */
    protected function actionImplement($in) {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }

}