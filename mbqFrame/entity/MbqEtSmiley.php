<?php

defined('MBQ_IN_IT') or exit;

/**
 * Smiley class
 */
Class MbqEtSmiley extends MbqBaseEntity {

    public $catagory; /* smiliy category, if not exists, set as 'default'*/
    public $code;    /* smiley code like ':D' */
    public $url; /* smiley url(it may be relative path and need add forum root path to complete the url) */
    public $title;   /* smiley title (optional)*/
    public $width;   /* smiley width (optional) */
    public $height; /* smiley height (optional) */

    public function __construct() {
        parent::__construct();
        $this->category = clone MbqMain::$simpleV;
        $this->code = clone MbqMain::$simpleV;
        $this->url = clone MbqMain::$simpleV;
        $this->title = clone MbqMain::$simpleV;
        $this->width = clone MbqMain::$simpleV;
        $this->height = clone MbqMain::$simpleV;
    }

}
