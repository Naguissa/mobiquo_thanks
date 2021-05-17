<?php

defined('MBQ_IN_IT') or exit;

/**
 * create_message action
 */
Abstract Class MbqBaseActCreateMessage extends MbqBaseAct {

    public function __construct() {
        parent::__construct();
    }

    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->usernames = $this->getInputParam('username');
            $in->subject = $this->getInputParam('subject');
            $in->body = $this->getInputParam('body');
            $in->action = $this->getInputParam('action');
            $in->messageId = $this->getInputParam('messageId');
        }
        else
        {
            $in->usernames = (array) $this->getInputParam(0);
            $in->subject = $this->getInputParam(1);
            $in->body = $this->getInputParam(2);
            $in->action = $this->getInputParam(3);
            $in->messageId = (int) $this->getInputParam(4);
        }
        $in->body =str_replace(array('<','>'),array('&lt;','&gt;'),$in->body);
        $in->subject =str_replace(array('<','>'),array('&lt;','&gt;'),$in->subject);
        return $in;
    }

    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (MbqMain::$oMbqConfig->moduleIsEnable('pm')) {
            $oMbqEtPm = MbqMain::$oClk->newObj('MbqEtPm');
            $oMbqEtPm->userNames->setOriValue($in->usernames);
            $oMbqEtPm->msgTitle->setOriValue($in->subject);
            $oMbqEmoji = MbqMain::$oClk->newObj('MbqEmoji');
            $body = $oMbqEmoji->DoReplace($in->body);
            $oMbqEtPm->msgContent->setOriValue($body);
            if($in->action == 1)
            {
                $oMbqEtPm->isReply->setOriValue(true);
                $oMbqEtPm->toMsgId->setOriValue($in->messageId);
            }
            else if($in->action == 2)
            {
                $oMbqEtPm->isForward->setOriValue(true);
                $oMbqEtPm->toMsgId->setOriValue($in->messageId);
            }
            $oMbqAclEtPm = MbqMain::$oClk->newObj('MbqAclEtPm');
            $aclResult = $oMbqAclEtPm->canAclCreateMessage();
	        if ($aclResult === true) {
                $oMbqWrEtPm = MbqMain::$oClk->newObj('MbqWrEtPm');
                $oMbqEtPm = $oMbqWrEtPm->addMbqEtPm($oMbqEtPm);
                if($oMbqEtPm instanceof MbqEtPm)
                {
                    $this->data['result'] = true;
                    $this->data['msg_id'] = (string) $oMbqEtPm->msgId->oriValue;
                    $oTapatalkPush = new TapatalkPush();
                    $oTapatalkPush->callMethod('doInternalPushNewMessage', array(
                        'oMbqEtPm' => $oMbqEtPm
                    ));
                }
                else
                {
                    $this->data['result'] = false;
                    $this->data['result_text'] = $oMbqEtPm;
                }
            } else {
                MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Not support module private message!", '', MBQ_ERR_NOT_SUPPORT);
        }
    }

}