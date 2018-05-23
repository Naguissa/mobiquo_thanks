<?php

defined('MBQ_IN_IT') or exit;

/**
 * poll class
 */
Class MbqEtPoll extends MbqBaseEntity {
    
    public $topicId;
    public $pollTitle;
    public $pollLength; /* time interval of the poll activity, in seconds. */
    public $pollOptions;
    public $pollMaxOptions; /* max selectable options */
    
    public $canRevoting;
    public $canViewBeforeVote; /* user can view the result before voting. XF only, TTG sent false. */
    public $canPublic; /* voting list is public. XF only, TTG sent false. */

    public $myVotes;
    public $voteOptions;
    public $newOptions;

    public $oMbqEtForumTopic;
    
    public function __construct() {
        parent::__construct();
        $this->topicId = clone MbqMain::$simpleV;
        $this->pollTitle = clone MbqMain::$simpleV;
        $this->pollLength = clone MbqMain::$simpleV;
        $this->pollOptions = clone MbqMain::$simpleV;
        $this->pollMaxOptions = clone MbqMain::$simpleV;
        $this->canRevoting = clone MbqMain::$simpleV;
        $this->canViewBeforeVote = clone MbqMain::$simpleV;
        $this->canPublic = clone MbqMain::$simpleV;
        
        $this->myVotes = clone MbqMain::$simpleV;
        $this->voteOptions = clone MbqMain::$simpleV;
        $this->newOptions = clone MbqMain::$simpleV;

        $this->oMbqEtForumTopic = NULL;
    }
  
}
