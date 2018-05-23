<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_box action
 */
Abstract Class MbqBaseActGetBox extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    function getInput()
    {
        $in = new stdClass();
        $oMbqDataPage = MbqMain::$oClk->newObj('MbqDataPage');
        if(MbqMain::isJsonProtocol())
        {
            $in->boxId = $this->getInputParam('boxId');
            $oMbqDataPage->initByPageAndPerPage($this->getInputParam('page'), $this->getInputParam('perPage'));
        }
        else
        {
            $in->boxId = $this->getInputParam(0);
            $startNum = (int) $this->getInputParam(1);
            $lastNum = (int) $this->getInputParam(2);
            $oMbqDataPage->initByStartAndLast($startNum, $lastNum);
        }
        $in->oMbqDataPage = $oMbqDataPage;
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (MbqMain::$oMbqConfig->moduleIsEnable('pm')) {
	        $oMbqRdEtPm = MbqMain::$oClk->newObj('MbqRdEtPm');
	        if ($oMbqEtPmBox = $oMbqRdEtPm->initOMbqEtPmBox($in->boxId, array('case' => 'byBoxId'))) {
	            $oMbqAclEtPm = MbqMain::$oClk->newObj('MbqAclEtPm');
                $aclResult = $oMbqAclEtPm->canAclGetBox($oMbqEtPmBox);
	            if ($aclResult === true) {
	                $in->oMbqDataPage = $oMbqRdEtPm->getObjsMbqEtPm($oMbqEtPmBox, array('case' => 'byBox', 'oMbqDataPage' => $in->oMbqDataPage));
	                $this->data = $oMbqRdEtPm->returnApiDataPmBox($oMbqEtPmBox);
	                $this->data['result'] = true;
                    $this->data['total_message_count'] = (int) $oMbqRdEtPm->getTotalMessageInbox($oMbqEtPmBox);
                    $this->data['total_unread_count'] = (int) $oMbqRdEtPm->getTotalMessageInbox($oMbqEtPmBox, 'unread');
	                $this->data['list'] = $oMbqRdEtPm->returnApiArrDataPm($in->oMbqDataPage->datas, true);
	            } else {
	                MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
	            }
	        } else {
	            MbqError::alert('', "Need valid pm box id!", '', MBQ_ERR_APP);
	        }
        } else {
            MbqError::alert('', "Not support module private message!", '', MBQ_ERR_NOT_SUPPORT);
        }      
    }
    
}