<?php

defined('MBQ_IN_IT') or exit;

/**
 * common method class
 */
Class MbqCm extends MbqBaseCm {
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * transform timestamp to iso8601 format
     *
     * @param  Integer  $timeStamp
     * TODO:need to be made more general.
     */
    public function datetimeIso8601Encode($timeStamp) {
    	if(getPHPBBVersion() == '3.0')
	{
	 	global $user;
	        $zone_offset = $user->timezone + $user->dst;
	        $timezone = $user->data['user_timezone'];
	        if(MbqMain::isJsonProtocol())
	        {		
	            $time = @gmdate('Y-m-d\TH:i:s', $timeStamp + $zone_offset);
	        }
	        else
	        {
	            $time = @gmdate('Ymd\TH:i:s', $timeStamp + $zone_offset);
	        }
	        $time .= sprintf("%+03d:%02d", intval($timezone), abs($timezone - intval($timezone)) * 60);
	        return $time;
	}
	else
	{		
		global $user;
		static $zone_offset;
		if (empty($zone_offset))
		{
		$zone_offset = $user->create_datetime()->getOffset();
		}
		return parent::datetimeIso8601Encode($timeStamp + $zone_offset);
	}
    }
}

