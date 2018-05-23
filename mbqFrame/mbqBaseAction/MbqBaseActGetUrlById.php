<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_url_by_id
 */
Abstract Class MbqBaseActGetUrlById extends MbqBaseAct {

    public function __construct() {
        parent::__construct();
    }

    function getInput()
    {
        $in = new stdClass();
        if(MbqMain::isJsonProtocol())
        {
            $in->mode = $this->getInputParam('mode');
            $in->id = $this->getInputParam('id');
            $in->redirect = $this->getInputParam('redirect');
        }
        else
        {
            $in->mode = $this->getInputParam(0,$this->getInputParam('mode',''));
            $in->id = $this->getInputParam(1,$this->getInputParam('id',0));
            $in->redirect = $this->getInputParam(2,$this->getInputParam('redirect',false));
        }
        return $in;
    }

    /**
     * action implement
     */
    protected function actionImplement($in) {
        if (!MbqMain::$oMbqConfig->moduleIsEnable('forum')) {
            MbqError::alert('', "Not support module forum!", '', MBQ_ERR_NOT_SUPPORT);
        }
        $oMbqRdCommon = MbqMain::$oClk->newObj('MbqRdCommon');
        switch($in->mode)
        {
            case 'forum':
                {
                    $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
                    if ($oMbqEtForum = $oMbqRdEtForum->initOMbqEtForum($in->id, array('case' => 'byForumId'))
                        ) {
                        $this->data['result'] = true;
                        $this->data['url'] = $oMbqRdEtForum->getUrl($oMbqEtForum);
                        if($in->redirect == true)
                        {
                            $this->DoRedirect($this->data['url']);
                        }
                    }
                    else
                    {
                        if($in->redirect == true)
                        {
                            $this->DoRedirect($oMbqRdCommon->getForumUrl());
                            exit;
                        }
                        MbqError::alert('', "Need valid forum id!", '', MBQ_ERR_APP);
                    }
                    break;
                }
            case 'topic':
                {
                    $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
                    if ($oMbqEtForumTopic = $oMbqRdEtForumTopic->initOMbqEtForumTopic($in->id, array('case' => 'byTopicId'))
                        ) {
                        $this->data['result'] = true;
                        $this->data['url'] = $oMbqRdEtForumTopic->getUrl($oMbqEtForumTopic);
                        if($in->redirect == true)
                        {
                            $this->DoRedirect($this->data['url']);
                        }
                    }
                    else
                    {
                        if($in->redirect == true)
                        {
                            $this->DoRedirect($oMbqRdCommon->getForumUrl());
                            exit;
                        }
                        MbqError::alert('', "Need valid topic id!", '', MBQ_ERR_APP);
                    }
                    break;
                }
            case 'post':
                {
                    $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
                    if ($oMbqEtForumPost = $oMbqRdEtForumPost->initOMbqEtForumPost($in->id, array('case' => 'byPostId'))) {
                        $this->data['result'] = true;
                        $this->data['url'] = $oMbqRdEtForumPost->getUrl($oMbqEtForumPost);
                        if($in->redirect == true)
                        {
                            $this->DoRedirect($this->data['url']);
                        }
                    }
                    else
                    {
                        if($in->redirect == true)
                        {
                            $this->DoRedirect($oMbqRdCommon->getForumUrl());
                            exit;
                        }
                        MbqError::alert('', "Need valid post id!", '', MBQ_ERR_APP);
                    }
                    break;
                }
            case 'pm':
                {
                    $oMbqRdEtPm = MbqMain::$oClk->newObj('MbqRdEtPm');
                    if ($oMbqEtPm = $oMbqRdEtPm->initOMbqEtPm(array('msgId'=> $in->id), array('case' => 'byMsgId'))
                        ) {
                        $this->data['result'] = true;
                        $this->data['url'] = $oMbqRdEtPm->getUrl($oMbqEtPm);
                        if($in->redirect == true)
                        {
                            $this->DoRedirect($this->data['url']);
                        }
                    }
                    else
                    {
                        if($in->redirect == true)
                        {
                            $this->DoRedirect($oMbqRdCommon->getForumUrl());
                            exit;
                        }
                        MbqError::alert('', "Need valid pcmsg id!", '', MBQ_ERR_APP);
                    }
                    break;
                }
            case 'conv':
                {
                    $oMbqRdEtPc = MbqMain::$oClk->newObj('MbqRdEtPc');
                    if ($oMbqEtPc = $oMbqRdEtPc->initOMbqEtPc($in->id, array('case' => 'byConvId'))
                        ) {
                        $this->data['result'] = true;
                        $this->data['url'] = $oMbqRdEtPc->getUrl($oMbqEtPc);
                        if($in->redirect == true)
                        {
                            $this->DoRedirect($this->data['url']);
                        }
                    }
                    else
                    {
                        if($in->redirect == true)
                        {
                            $this->DoRedirect($oMbqRdCommon->getForumUrl());
                            exit;
                        }
                        MbqError::alert('', "Need valid pcmsg id!", '', MBQ_ERR_APP);
                    }
                    break;
                }
            default:
                {
                    if($in->redirect == true)
                    {
                        $this->DoRedirect($oMbqRdCommon->getForumUrl());
                        exit;
                    }
                    MbqError::alert('', "Need valid mode. Valid mode values are 'forum','topic','post','pm','conv'", '', MBQ_ERR_APP);
                }
        }
    }
    protected function DoRedirect($url)
    {
        header('Location: ' . $url, true, 302);
        exit;
    }
}