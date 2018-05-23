<?php

defined('MBQ_IN_IT') or exit;

Abstract Class MbqBaseRdCommon extends MbqBaseRd {

    public function __construct() {
    }

    public function getApiKey()
    {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    public function getTapatalkForumId()
    {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    public function getForumUrl()
    {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    public function getCheckSpam()
    {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    public function get_id_by_url($url)
    {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    public function getPushSlug()
    {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    public function getSmartbannerInfo()
    {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    public function returnApiDataSmiley($oMbqDataSmilie)
    {
        $data = array();
        if ($oMbqDataSmilie->code->hasSetOriValue()) {
            $data['code'] = (string) $oMbqDataSmilie->code->oriValue;
        }
        if ($oMbqDataSmilie->url->hasSetOriValue()) {
            $data['url'] = (string) $oMbqDataSmilie->url->oriValue;
        }
        if ($oMbqDataSmilie->title->hasSetOriValue()) {
            $data['title'] = (string) $oMbqDataSmilie->title->oriValue;
        }
        if ($oMbqDataSmilie->height->hasSetOriValue()) {
            $data['height'] = (int) $oMbqDataSmilie->height->oriValue;
        }
        if ($oMbqDataSmilie->width->hasSetOriValue()) {
            $data['width'] = (int) $oMbqDataSmilie->width->oriValue;
        }
        return $data;
    }
    public function returnApiArrDataSmilies($arrMbqEtSimilies)
    {
        $data = array();
        foreach($arrMbqEtSimilies as $oMbqDataSmilie)
        {
            if($oMbqDataSmilie->category->hasSetOriValue() == false)
            {
                $oMbqDataSmilie->category->setOriValue('default');
            }
            $data[$oMbqDataSmilie->category->oriValue][] = $this->returnApiDataSmiley($oMbqDataSmilie);
        }
        return $data;
    }
}