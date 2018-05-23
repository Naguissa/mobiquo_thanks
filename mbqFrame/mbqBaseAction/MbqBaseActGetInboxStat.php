<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_inbox_stat action
 */
Abstract Class MbqBaseActGetInboxStat extends MbqBaseAct {

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
        /* TODO */
        $oMbqRdEtPc = MbqMain::$oClk->newObj('MbqRdEtPc');
        if (MbqMain::$oMbqConfig->moduleIsEnable('pc') && (MbqMain::$oMbqConfig->getCfg('pc.conversation')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.pc.conversation.range.support'))) {
            $oMbqAclEtPc = MbqMain::$oClk->newObj('MbqAclEtPc');
            $aclResult = $oMbqAclEtPc->canAclGetInboxStat();
            if ($aclResult === true) {    //acl judge
                 $this->data['inbox_unread_count'] = (int) $oMbqRdEtPc->getUnreadPcNum();
            } else {
                MbqError::alert('', $aclResult, '', MBQ_ERR_APP);
            }
        } elseif (MbqMain::$oMbqConfig->moduleIsEnable('pm')) {
            $oMbqRdEtPm = MbqMain::$oClk->newObj('MbqRdEtPm');
            $oMbqEtPmBox = $oMbqRdEtPm->initOMbqEtPmBox('0', array('case' => 'byBoxId'));
            $this->data['inbox_unread_count'] = (int) $oMbqRdEtPm->getTotalMessageInbox($oMbqEtPmBox,'unread');
        } else {
            $this->data['inbox_unread_count'] = (int) 0;
        }
        if (MbqMain::$oMbqConfig->moduleIsEnable('subscribe')) {
            $this->data['subscribed_topic_unread_count'] = (int) 0;
        } else {
            $this->data['subscribed_topic_unread_count'] = (int) 0;
        }
    }

}