<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseActMGetInactiveUsers');


class MbqActMGetInactiveUsers extends MbqBaseActMGetInactiveUsers
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * action implement
     *
     * @param $in
     */
    public function actionImplement($in)
    {
        parent::actionImplement($in);
    }

}
