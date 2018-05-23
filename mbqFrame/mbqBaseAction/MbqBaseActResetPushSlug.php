<?php

defined('MBQ_IN_IT') or exit;

/**
 * ResetPushSlug
 */
Abstract Class MbqBaseActResetPushSlug extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        
        if(MbqMain::isRawPostProtocol())
        {
            $in->code = MbqMain::$input['code'];
        }
        include_once(MBQ_3RD_LIB_PATH . 'classTTConnection.php');
        $connection = new classTTConnection();
        $response = $connection->actionVerification($in->code,'reset_push_slug');
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
        $TapatalkPush = new \TapatalkPush();
        $push_v_data = array();     //Slug
        $push_v_data[0] = 3;        //        $push_v_data['max_times'] = 3;                //max push failed attempt times in period
        $push_v_data[1] = 300;      //        $push_v_data['max_times_in_period'] = 300;     //the limitation period
        $push_v_data[2] = 1;        //        $push_v_data['result'] = 1;                   //indicate if the output is valid of not
        $push_v_data[3] = '';       //        $push_v_data['result_text'] = '';             //invalid reason
        $push_v_data[4] = array();  //        $push_v_data['stick_time_queue'] = array();   //failed attempt timestamps
        $push_v_data[5] = 0;        //        $push_v_data['stick'] = 0;                    //indicate if push attempt is allowed
        $push_v_data[6] = 0;        //        $push_v_data['stick_timestamp'] = 0;          //when did push be sticked
        $push_v_data[7] = 600;      //        $push_v_data['stick_time'] = 600;             //how long will it be sticked
        $push_v_data[8] = 1;        //        $push_v_data['save'] = 1;                     //indicate if you need to save the slug into db
        $result = $TapatalkPush->set_push_slug(serialize($push_v_data));
        $this->data = array('result' => $result);
    }
  
}