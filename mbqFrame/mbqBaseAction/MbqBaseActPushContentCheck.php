<?php

defined('MBQ_IN_IT') or exit;

/**
 * forget password
 */
Abstract Class MbqBaseActPushContentCheck extends MbqBaseAct {
    
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
        $response = $connection->actionVerification($in->code,'push_content_check');
        if(!$response)
        {
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_PARAMS_ERROR);
        }
        $in->data = unserialize(MbqMain::$input['data']);
        return $in;
    }
    
    /**
     * action implement
     */
    protected function actionImplement($in) {
        $result = false;
        switch($in->data['type'])
        {
            case 'newtopic':
            case 'sub':
            case 'quote':
            case 'tag':
                {
                    $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
                    if($oMbqEtForumPost  = $oMbqRdEtForumPost->initOMbqEtForumPost($in->data['subid'], array('case'=>'byPostId')))
                    {
                        if($oMbqEtForumPost->topicId->oriValue == $in->data['id'] && ($oMbqEtForumPost->postAuthorId->oriValue == $in->data['authorid'] || $oMbqEtForumPost->oAuthorMbqEtUser->userName->oriValue  == $in->data['author']))
                        {
                           $result = true;
                        }
                    }
                    break;
                } 
            case 'conv':
                {
                    $oMbqRdEtPcMsg = MbqMain::$oClk->newObj('MbqRdEtPcMsg');
                    if ($oMbqEtPcMsg = $oMbqRdEtPcMsg->initOMbqEtPcMsg(null, array('case' => 'byPcMsgId', 'pcMsgId' => $in->data['mid'])))
                    {
                        if($oMbqEtPcMsg->oAuthorMbqEtUser->userId->oriValue == $in->data['authorid'] || $oMbqEtPcMsg->oAuthorMbqEtUser->userName->oriValue == $in->data['author'])
                        {
                           $result = true;
                        }
                    }
                    break;
                }
            case 'pm':
                {
                    $oMbqRdEtPcMsg = MbqMain::$oClk->newObj('MbqRdEtPcMsg');
                    if ($oMbqEtPcMsg = $oMbqRdEtPcMsg->initOMbqEtPcMsg(null, array('case' => 'byPcMsgId', 'pcMsgId' => $in->data['mid'])))
                    {
                        if($oMbqEtPcMsg->oAuthorMbqEtUser->userId->oriValue == $in->data['authorid'] || $oMbqEtPcMsg->oAuthorMbqEtUser->userName->oriValue == $in->data['author'])
                        {
                            $result = true;
                        }
                    }
                    break;
                }
            
        }
        $this->data['result'] = $result;
    }
  
}