<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseWrEtAtt');

/**
 * attachment write class
 */
Class MbqWrEtAtt extends MbqBaseWrEtAtt {

    public function __construct() {
    }
    public function uploadAttachment($oMbqEtForumOrConvPm, $groupId, $type) {
        global $phpbb_root_path, $phpEx, $user, $request;
        include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
        include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);

        // Start session management

        $user->setup('posting');


        $forum_id = $oMbqEtForumOrConvPm->forumId->oriValue;
        $attachment_data = !empty($groupId) ? unserialize(base64_decode($groupId)) : array();
        overwriteRequestParam('attachment_data' , $attachment_data, \phpbb\request\request_interface::POST);

        $_POST['add_file'] = 'Add the file';

        $message_parser = new parse_message();
        $message_parser->get_submitted_attachment_data();
        $message_parser->parse_attachments('fileupload', 'post', $forum_id, false, false, true);
        $new_attach_position = 0;
        $maxId = 0;
        foreach($message_parser->attachment_data as $key => $attach)
        {
            if($maxId < $attach['attach_id'])
            {
                $maxId = $attach['attach_id'];
                $new_attach_position = $key;
            }
        }
        $attachment_id = isset($message_parser->attachment_data[$new_attach_position]) ?  $message_parser->attachment_data[$new_attach_position]['attach_id'] : '';
        $filesize = isset($message_parser->attachment_data[$new_attach_position]) && isset($message_parser->attachment_data[$new_attach_position]['filesize']) ? $message_parser->attachment_data[$new_attach_position]['filesize'] : '';
        $filename = isset($message_parser->attachment_data[$new_attach_position]) && isset($message_parser->attachment_data[$new_attach_position]['real_filename']) ? $message_parser->attachment_data[$new_attach_position]['real_filename'] : '';
        if($attachment_id != '')
        {
            $groupId = base64_encode(serialize($message_parser->attachment_data));
            $warn_msg = join("\n", $message_parser->warn_msg);
            $oMbqEtAtt = MbqMain::$oClk->newObj('MbqEtAtt');
            $oMbqEtAtt->attId->setOriValue($attachment_id);
            $oMbqEtAtt->groupId->setOriValue($groupId);
            $oMbqEtAtt->filtersSize->setOriValue($filesize);
            $oMbqEtAtt->uploadFileName->setOriValue($filename);
            return $oMbqEtAtt;
        }
        return false;
    }
    public function deleteAttachment($oMbqEtAtt) {
        global $warn_msg, $phpbb_root_path, $phpEx, $request;
        $attachment_data = $oMbqEtAtt->groupId->hasSetOriValue() ? unserialize(base64_decode($oMbqEtAtt->groupId->oriValue)) : array();
        overwriteRequestParam('attachment_data' , $attachment_data, \phpbb\request\request_interface::POST);

        $position = '';
        foreach($_POST['attachment_data'] as $pos => $data) {
            if ($data['attach_id'] == $oMbqEtAtt->attId->oriValue) {
                $position = $pos;
                break;
            }
        }
        $groupId = null;
        if ($position === '') {
            $warn_msg = 'Attachment not exists';
        } else {
            include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
            include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);
            $_POST['delete_file'][$position] = 'Delete file';
            $_REQUEST['delete_file'][$position] = 'Delete file';

            $message_parser = new parse_message();
//            $message_parser->get_submitted_attachment_data();
            $message_parser->parse_attachments('fileupload', 'post', $oMbqEtAtt->forumId->oriValue, false, false, true);
            unset($attachment_data[$position]);
            $groupId = base64_encode(serialize($attachment_data));
            $warn_msg = join("\n", $message_parser->warn_msg);
        }
        return $groupId;
    }
}
