<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtAtt');

/**
 * attachment read class
 */
Class MbqRdEtAtt extends MbqBaseRdEtAtt {

    public function __construct() {
    }

    public function makeProperty(&$oMbqEtAtt, $pName, $mbqOpt = array()) {
        switch ($pName) {
            default:
                MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_PNAME . ':' . $pName . '.');
                break;
        }
    }


    public function initOMbqEtAtt($var = null, $mbqOpt = array()) {
        global $db,$phpbb_home,$phpEx,$config,$phpbb_root_path,$auth;
        if ($mbqOpt['case'] == 'byAttId') {
            $oMbqAttr = false;
            $sql = 'SELECT *
			    FROM ' . ATTACHMENTS_TABLE . '
			    WHERE attach_id=' . $var;
            $result = $db->sql_query($sql);

            $row = $db->sql_fetchrow($result);
            if(isset($row))
            {
                $oMbqAttr = $this->initOMbqEtAtt($row, array('case' => 'byRow'));
            }
            $db->sql_freeresult($result);
            return $oMbqAttr;
        }
        else if ($mbqOpt['case'] == 'byRow') {
            $attachment = $var;
            $attach_id = $attachment['attach_id'];

            $file_url = basic_clean($phpbb_home.'download/file.'.$phpEx.'?id='.$attach_id);
            $thumbnail_url = '';

            if ($config['img_create_thumbnail'])
            {
                $thumbnail_url = preg_replace('/file\.php\?/is', 'file.php?t=1&', $file_url);
            }

            if (strpos($attachment['mimetype'], 'image') === 0)
                $contentType = MbqBaseFdt::getFdt('MbqFdtAtt.MbqEtAtt.contentType.range.image');
            else if (strpos($attachment['mimetype'], 'pdf') === 0)
                $contentType = MbqBaseFdt::getFdt('MbqFdtAtt.MbqEtAtt.contentType.range.pdf');
            else
                $contentType = MbqBaseFdt::getFdt('MbqFdtAtt.MbqEtAtt.contentType.range.other');

            $oMbqEtAtt = MbqMain::$oClk->newObj('MbqEtAtt');
	        $downloadAllowed = true;
	        if(file_exists($phpbb_root_path.'includes/functions_download.php'))
	        {
                require_once($phpbb_root_path.'includes/functions_download.php');
		        $downloadAllowed = download_allowed();
            }
            $oMbqEtAtt->attId->setOriValue($attach_id);
            $oMbqEtAtt->filtersSize->setOriValue($attachment['filesize']);
            $oMbqEtAtt->uploadFileName->setOriValue($attachment['real_filename']);
            if($attachment['in_message'] == 0)
            {
                $oMbqEtAtt->postId->setOriValue($attachment['post_msg_id']);
            }
            $oMbqEtAtt->contentType->setOriValue($contentType);
            $oMbqEtAtt->url->setOriValue($file_url);
            $oMbqEtAtt->thumbnailUrl->setOriValue($thumbnail_url);
            $oMbqEtAtt->userId->setOriValue($attachment['poster_id']);
            $downloadAllowed = $auth->acl_get('u_download') && (!isset($attachment['forum_id']) || $auth->acl_get('f_download', $attachment['forum_id']));
            $oMbqEtAtt->canViewUrl->setOriValue($downloadAllowed);
            $oMbqEtAtt->canViewThumbnailUrl->setOriValue($downloadAllowed);
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            $oMbqEtAtt->oMbqEtUser = $oMbqRdEtUser->initOMbqEtUSer($attachment['poster_id'], array('case' => 'byUserId'));;
            $oMbqEtAtt->mbqBind = $attachment;
            return $oMbqEtAtt;
        }
    }
}