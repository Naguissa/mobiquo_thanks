<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_box_info action
 */
Abstract Class MbqBaseActGetBoxInfo extends MbqBaseAct {
    
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
        if (MbqMain::$oMbqConfig->moduleIsEnable('pm')) {
		$oMbqAclEtPm = MbqMain::$oClk->newObj('MbqAclEtPm');
		$aclResult = $oMbqAclEtPm->canAclGetBoxInfo();
		if ($aclResult === true) {    //acl judge
		$oMbqRdEtPm = MbqMain::$oClk->newObj('MbqRdEtPm');
		$objsMbqEtPmBox = $oMbqRdEtPm->getObjsMbqEtPmBox();
		$this->data['result'] = true;
                $this->data['message_room_count'] = $oMbqRdEtPm->getPmRoomCount();
		$this->data['list'] = $oMbqRdEtPm->returnApiArrDataPmBox($objsMbqEtPmBox);
	    } else {
		    MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
	    }
        } else {
            MbqError::alert('', "Not support module private message!", '', MBQ_ERR_NOT_SUPPORT);
        }        
       
    }
  
}