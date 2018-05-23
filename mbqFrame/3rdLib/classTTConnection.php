<?php
if(!class_exists('classTTConnection'))
{
	class classTTConnection
	{
	    /**
	     * Use sockets flag
	     *
	     * @var     integer
	     */
	    public $use_sockets = 1;

        /**
         * Use sockets flag
         *
         * @var     integer
         */
	    public $user_curl = 1;

	    /**
	     * Error array
	     *
	     * @var     array
	     */
	    public $errors      = array();

        /**
         * warning array
         *
         * @var     array
         */
	    public $warnings     = array();

	    /**
	     * flag to check if connection sucess despite errors or fallback
	     * @var mixed
	     */
	    public $success = false;

	    /**
	     * Key prefix to identify communication strings
	     *
	     * @var     string
	     */
	    public $key_prefix  = '';

	    /**
	     * HTTP Status Code
	     *
	     * @var     integer
	     */
	    public $http_status_code    = 0;

	    /**
	     * HTTP Status Text
	     *
	     * @var     string
	     */
	    public $http_status_text    = "";

	    /**
	     * Raw HTTP header output
	     *
	     * @var     string
	     */
	    public $raw_headers         = '';

	    /**#@+
	     * Set HTTP authentication parameters
	     *
	     * @var     string
	     */
	    public $auth_req        = 0;
	    public $auth_user       = '';
	    public $auth_pass       = '';
	    public $auth_raw        = '';
	    public $userAgent       = '';
	    /**#@-*/

	    /**
	     * Timeout setting
	     *
	     * @var     int
	     */
	    public $timeout         = 5;

	    /**
	     * Number of redirects we've followed so far with sockets
	     *
	     * @var     int
	     */
	    protected $_redirectsFollowed   = 0;

	    /**
	     * Retrieve file contents from either a local file or URL, returning those contents
	     *
	     * @param   string      URI / File path
	     * @param   string      HTTP User
	     * @param   string      HTTP Pass
	     * @return  @e string
	     */
	    public function getFileContents( $file_location, $http_user='', $http_pass='' )
	    {
	        //-------------------------------
	        // INIT
	        //-------------------------------

	        $contents       = "";
	        $file_location  = str_replace( '&amp;', '&', $file_location );

	        //-----------------------------------------
	        // Inline user/pass?
	        //-----------------------------------------

	        if ( $http_user and $http_pass )
	        {
	            $this->auth_req  = 1;
	            $this->auth_user = $http_user;
	            $this->auth_pass = $http_pass;
	        }

	        //-------------------------------
	        // Hello
	        //-------------------------------

	        if ( ! $file_location )
	        {
	            return FALSE;
	        }

	        if ( ! stristr( $file_location, 'http://' ) AND ! stristr( $file_location, 'https://' ) )
	        {
	            //-------------------------------
	            // It's a path!
	            //-------------------------------

	            if ( ! file_exists( $file_location ) )
	            {
	                $this->errors[] = "FOPEN: File '{$file_location}' does not exist, please check the path.";
	                return;
	            }

	            $contents = $this->_getContentsWithFopen( $file_location );
	        }
	        else
	        {
	            //-------------------------------
	            // Is URL, try curl and then fall back
	            //-------------------------------

	            if($this->user_curl)
                {
                    if(($contents = $this->_getContentsWithCurl( $file_location ) ) === false )
                    {
                        if ( $this->use_sockets )
                        {
                            $contents = $this->_getContentsWithSocket( $file_location );
                        }
                        else
                        {
                            $contents = $this->_getContentsWithFopen( $file_location );
                        }
                    }
                }
                else
                {
                    if ( $this->use_sockets )
                    {
                        $contents = $this->_getContentsWithSocket( $file_location );
                    }
                    else
                    {
                        $contents = $this->_getContentsWithFopen( $file_location );
                    }
                }
	        }

	        return $contents;
	    }

	    public function getContentFromSever($url,$data = array(),$method = 'post',$retry = true)
	    {
            $this->success = false;
            $this->errors = array();
            $this->warnings = array();
	        $content = '';
	        $method = strtolower($method);
	        if($method == 'post')
	        {
	            $content = $this->postFileContents($url,$data);
	            if(empty($content) && $content !== '0' && $retry)
	            {
                    $this->errors[] = 'POST fails, doing retry with GET request';
	                $file_location = $url . '?' . $this->http_build_query($data);
	                $content = $this->getFileContents($file_location);
	            }
	        }
	        else
	        {
	            if(!empty($data))
	            {
	                if(strstr($url, '?'))
	                {
	                    $file_location = $url . '&' . $this->http_build_query($data);
	                }
	                else
	                {
	                    $file_location = $url . '?' . $this->http_build_query($data);
	                }
	            }
	            else
	            {
	                $file_location = $url;
	            }
	            $content = $this->getFileContents($file_location);
	            if(empty($content) && $content !== '0' && $retry)
	            {
	                $content = $this->postFileContents($url,$data);
	            }
	        }

	        return $content;
	    }

	    /**
	     *
	     * Enter description here ...
	     * @param array $data
	     * @param complex $push_slug
	     * @param string $board_url
	     * @param string $api_key
	     * @param string $is_test
	     * @return string push slug if fail , else return true;
	     */
	    public function push($data, $push_slug = 0, $board_url,$api_key ,$is_test)
	    {
	        $push_url = 'http://push.tapatalk.com/push.php';

	        $slug = $push_slug;
	        $slug = $this->pushSlug($slug, 'CHECK');
	        $check_res = unserialize($slug);

	        if(!isset($data[0]['id']) && is_array($data))
	        {
	            $data = array($data);
	        }
	        $post_data = array (
	           'url'  => $board_url,
	           'data' => base64_encode(serialize($data)),
	        );
	        if(!empty($api_key))
	        {
	            $post_data['key'] = $api_key;
	        }
	        if($is_test)
	        {
	            $post_data['test'] = 1;
	        }
	        //If it is valide(result = true) and it is not sticked, we try to send push
	        $res['result'] = 'Push Block';
	        if($check_res[2] && !$check_res[5])
	        {
	            //Send push
	            $push_resp = $this->getContentFromSever($push_url,$post_data,'post',false);
	            $res['result'] = $push_resp;
	            if(!is_numeric($push_resp) && !$is_test)
	            {
	                //Sending push failed, try to update push_slug to db
	                $slug = $this->pushSlug($slug, 'UPDATE');
	                $update_res = unserialize($slug);
	                if($update_res[2] && $update_res[8])
	                {
	                    $res['slug'] = $slug;
	                }
	            }
	        }
	        return $res;
	    }

	    public function pushSlug($push_v, $method = 'NEW')
	    {
	        if(empty($push_v))
	        $push_v = serialize(array());
	        $push_v_data = unserialize($push_v);
	        $current_time = time();
	        if(!is_array($push_v_data))
	        return false;
	        if($method != 'CHECK' && $method != 'UPDATE' && $method != 'NEW')
	        return false;

	        if($method != 'NEW' && !empty($push_v_data))
	        {
	            $push_v_data[8] = $method == 'UPDATE';
	            if($push_v_data[5] == 1)
	            {
	                if($push_v_data[6] + $push_v_data[7] > $current_time)
	                return $push_v;
	                else
	                $method = 'NEW';
	            }
	        }

	        if($method == 'NEW' || empty($push_v_data))
	        {
	            /*
	             * 0=> max_times
	             * 1=> max_times_in_period
	             * 2=> result
	             * 3=> result_text
	             * 4=> stick_time_queue
	             * 5=> stick
	             * 6=> stick_timestamp
	             * 7=> stick_time
	             * 8=> save
	             */
	            $push_v_data = array();                       //Slug
	            $push_v_data[] = 3;                //max push failed attempt times in period
	            $push_v_data[] = 300;      //the limitation period
	            $push_v_data[] = 1;                   //indicate if the output is valid of not
	            $push_v_data[] = '';             //invalid reason
	            $push_v_data[] = array();   //failed attempt timestamps
	            $push_v_data[] = 0;                    //indicate if push attempt is allowed
	            $push_v_data[] = 0;          //when did push be sticked
	            $push_v_data[] = 600;             //how long will it be sticked
	            $push_v_data[] = 1;                     //indicate if you need to save the slug into db
	            return serialize($push_v_data);
	        }

	        if($method == 'UPDATE')
	        {
	            $push_v_data[4][] = $current_time;
	        }
	        $sizeof_queue = count($push_v_data[4]);
	        $period_queue = $sizeof_queue > 1 ? ($push_v_data[4][$sizeof_queue - 1] - $push_v_data[4][0]) : 0;
	        $times_overflow = $sizeof_queue > $push_v_data[0];
	        $period_overflow = $period_queue > $push_v_data[1];

	        if($period_overflow)
	        {
	            if(!array_shift($push_v_data[4]))
	            $push_v_data[4] = array();
	        }

	        if($times_overflow && !$period_overflow)
	        {
	            $push_v_data[5] = 1;
	            $push_v_data[6] = $current_time;
	        }

	        return serialize($push_v_data);
	    }
	    /**
	     *
	     * Verify sign in with tapatalk id
	     * @param string $token
	     * @param string $code
	     * @param string $board_url
	     * @param string $key
	     * @return array
	     */
	    public function signinVerify($token,$code,$board_url,$api_key,$isregister)
	    {
	        $url = "http://directory.tapatalk.com/au_reg_verify.php";
	        $data = array(
	            'token' => $token,
	            'code'  => $code,
	            'key'   => $api_key,
	            'url'   => $board_url,
	            'register' => $isregister
	        );
	        $response = $this->getContentFromSever($url,$data,'post');
	        if($this->success == false)
	        {
	            $error_msg = $this->errors[0];
	        }
	        if(!empty($error_msg))
	        {
	            $response = '{"result":false,"result_text":"'.$error_msg.'"}';
	        }
	        if(empty($response))
	        {
	            $response = '{"result":false,"result_text":"This forum system cannot connect to Tapatalk server! Please contact with forum administrator."}';
	        }
	        $result = json_decode($response,true);
	        return $result;
	    }

	    public function http_build_query($data, $prefix = null, $sep = '', $key = '')
	    {
	        $ret = array();
	        foreach ((array )$data as $k => $v)
	        {
	            $k = urlencode($k);
	            if (is_int($k) && $prefix != null)
	            {
	                $k = $prefix . $k;
	            }

	            if (!empty($key))
	            {
	                $k = $key . "[" . $k . "]";
	            }

	            if (is_array($v) || is_object($v))
	            {
	                array_push($ret, $this->http_build_query($v, "", $sep, $k));
	            } else {
	                array_push($ret, $k . "=" . urlencode($v));
	            }
	        }

	        if (empty($sep))
	        {
	            $sep = ini_get("arg_separator.output");
	        }

	        return implode($sep, $ret);
	    }

	    /**
	     * Sends a POST request to specified URL and returns the result
	     *
	     * @param   string          URI to post to
	     * @param   array |string   Array of post fields (key => value) OR raw post data
	     * @param   string          HTTP User
	     * @param   string          HTTP Pass
	     * @return  @e string
	     */
	    public function postFileContents( $file_location='', $post_array=array(), $http_user='', $http_pass='')
	    {
	        //-----------------------------------------
	        // Inline user/pass?
	        //-----------------------------------------

	        if ( $http_user and $http_pass )
	        {
	            $this->auth_req  = 1;
	            $this->auth_user = $http_user;
	            $this->auth_pass = $http_pass;
	        }

	        //-------------------------------
	        // INIT
	        //-------------------------------

	        $contents       = "";
	        $file_location  = str_replace( '&amp;', '&', $file_location );

	        if ( ! $file_location )
	        {
	            return false;
	        }

	        if($this->user_curl)
            {
                if(( $contents = $this->_getContentsWithCurl( $file_location, $post_array ) ) === false )
                {
                    if($this->use_sockets)
                    {
                        $contents = $this->_getContentsWithSocket( $file_location, $post_array );
                    }
                }
            }
            else
            {
                if($this->use_sockets)
                {
                    $contents = $this->_getContentsWithSocket( $file_location, $post_array );
                }
            }

	        return $contents;
	    }

	    /**
	     * Get file contents (with PHP's fopen)
	     *
	     * @param   string      File location
	     * @return  @e string
	     */
	    protected function _getContentsWithFopen( $file_location )
	    {
	        //-------------------------------
	        // INIT
	        //-------------------------------

	        $buffer = "";
	        $data   = '';

	        @clearstatcache();

	        if ( $FILE = fopen( $file_location, "r" ) )
	        {
	            @stream_set_timeout( $FILE, $this->timeout );
	            $status = @stream_get_meta_data($FILE);

	            while ( ! feof( $FILE ) && ! $status['timed_out'] )
	            {
	                $buffer .= fgets( $FILE, 4096 );

	                $status = stream_get_meta_data($FILE);
	            }

	            fclose($FILE);
	        }

	        if ( $buffer )
	        {
	            $this->http_status_code = 200;

	            $tmp  = preg_split("/\r\n\r\n/", $buffer, 2);
	            $data = trim($tmp[1]);

	            $this->raw_headers  = trim($tmp[0]);
	        }
	        $this->success = true;
	        return $buffer;
	    }

	    /**
	     * Get file contents (with sockets)
	     *
	     * @param   string      File location
	     * @param   array       Data to post (automatically converts to POST request)
	     * @return  @e string
	     */
	    protected function _getContentsWithSocket( $file_location, $post_array=array() )
	    {
	        //-------------------------------
	        // INIT
	        //-------------------------------

	        $data = null;

	        //-------------------------------
	        // Parse URL
	        //-------------------------------

	        $url_parts = @parse_url($file_location);

	        if ( ! $url_parts['host'] )
	        {
	            $this->errors[] = "SOCKETS: No host found in the URL '{$file_location}'!";
	            return FALSE;
	        }

	        //-------------------------------
	        // Finalize
	        //-------------------------------

	        $host = $url_parts['host'];
	        $port = ( isset($url_parts['port']) ) ? $url_parts['port'] : ( $url_parts['scheme'] == 'https' ? 443 : 80 );

	        //-------------------------------
	        // Tidy up path
	        //-------------------------------

	        if ( !empty( $url_parts["path"] ) )
	        {
	            $path = $url_parts["path"];
	        }
	        else
	        {
	            $path = "/";
	        }

	        if ( !empty( $url_parts["query"] ) )
	        {
	            $path .= "?" . $url_parts["query"];
	        }

	        //-------------------------------
	        // Open connection
	        //-------------------------------
	        if ( ! $fp = @fsockopen( $url_parts['scheme'] == 'https' ? "ssl://" . $host : $host, $port, $errno, $errstr, $this->timeout ) )
	        {
	            $this->errors[] = "SOCKETS: Could not establish a connection with {$host}";
	            return FALSE;

	        }
	        else
	        {
	            $final_carriage = ( $this->auth_req or $this->auth_raw ) ? "" : "\r\n";

	            $userAgent = ( $this->userAgent ) ? "\r\nUser-Agent: " . $this->userAgent : '';

	            //-----------------------------------------
	            // Are we posting?
	            //-----------------------------------------

	            if( $post_array )
	            {
	                if ( is_array( $post_array ) )
	                {
	                    $post_back  = array();
	                    foreach ( $post_array as $key => $val )
	                    {
	                        $post_back[] = $this->key_prefix . $key . '=' . urlencode($val);
	                    }
	                    $post_back_str  = implode( '&', $post_back);
	                }
	                else
	                {
	                    $post_back_str = $post_array;
	                }

	                $header = "POST {$path} HTTP/1.0\r\nHost: {$host}\r\nContent-Type: application/x-www-form-urlencoded\r\nConnection: close{$userAgent}\r\nContent-Length: " . strlen($post_back_str) . "\r\n{$final_carriage}{$post_back_str}";
	            }
	            else
	            {
	                $header = "GET {$path} HTTP/1.0\r\nHost: {$host}\r\nConnection: close{$userAgent}\r\n{$final_carriage}";
	            }

	            if ( ! fputs( $fp, $header ) )
	            {
	                $this->errors[] = "SOCKETS: Unable to send request to {$host}!";
	                return FALSE;
	            }

	            if ( $this->auth_req )
	            {
	                if ( $this->auth_user && $this->auth_pass )
	                {
	                    $header = "Authorization: Basic ".base64_encode("{$this->auth_user}:{$this->auth_pass}")."\r\n\r\n";

	                    if ( ! fputs( $fp, $header ) )
	                    {
	                        $this->errors[] = "SOCKETS: Authorization Failed!";
	                        return FALSE;
	                    }
	                }
	            }
	            elseif ( $this->auth_raw )
	            {
	                $header = $this->auth_raw."\r\n\r\n";

	                if ( ! fputs( $fp, $header ) )
	                {
	                    $this->errors[] = "SOCKETS: Authorization Failed!";
	                    return FALSE;
	                }
	            }
	        }

	        @stream_set_timeout( $fp, $this->timeout );
	        $status = @stream_get_meta_data($fp);
	        while( ! feof($fp) && ! $status['timed_out'] )
	        {
	            $data   .= fgets( $fp, 8192 );
	            $status = stream_get_meta_data($fp);
	        }

	        fclose ($fp);

	        //-------------------------------
	        // Strip headers
	        //-------------------------------

	        // HTTP/1.1 ### ABCD
	        $this->http_status_code = substr( $data, 9, 3 );
	        $this->http_status_text = substr( $data, 13, ( strpos( $data, "\r\n" ) - 13 ) );

	        //-----------------------------------------
	        // Redirect?  Try to fetch the 'location' value then.
	        //-----------------------------------------

	        if( ( $this->http_status_code == 301 OR $this->http_status_code == 302 ) AND preg_match( "/Location:\s*(.+?)(\r|\n)/i", $data, $matches ) )
	        {
	            if( $this->_redirectsFollowed < 5 )
	            {
	                $this->_redirectsFollowed++;

	                return $this->_getContentsWithSocket( $matches[1], $post_array );
	            }
	        }

	        //-----------------------------------------
	        // Try to deal with chunked..
	        //-----------------------------------------

	        $_chunked   = false;

	        if( preg_match( '/Transfer\-Encoding:\s*chunked/i', $data ) )
	        {
	            $_chunked   = true;
	        }

	        $tmp = preg_split("/\r\n\r\n/", $data, 2);
	        if(empty($tmp) || !isset($tmp[1]))
	        {
	            $this->success = false;
	            return false;
	        }

	        $data   = trim($tmp[1]);

	        $this->raw_headers  = trim($tmp[0]);

	        //-----------------------------------------
	        // Easy way out :P
	        //-----------------------------------------

	        if( $_chunked )
	        {
	            $lines  = explode( "\n", $data );
	            array_pop($lines);
	            array_shift($lines);
	            $data   = implode( "\n", $lines );
	        }
	        $this->success = true;
	        return $data;
	    }

	    /**
	     * Get file contents (with cURL)
	     *
	     * @param   string      File location
	     * @param   array       Data to post (automatically converts to POST request)
	     * @return  @e string
	     */
	    protected function _getContentsWithCurl( $file_location, $post_array=array() )
	    {
	        if ( function_exists( 'curl_init' ) AND function_exists("curl_exec") )
	        {
	            //-----------------------------------------
	            // Are we posting?
	            //-----------------------------------------

                $ch = curl_init();
                curl_setopt( $ch, CURLOPT_URL, $file_location);
	            curl_setopt( $ch, CURLOPT_HEADER            , 1 );
	            curl_setopt( $ch, CURLOPT_TIMEOUT           , $this->timeout );
	            curl_setopt( $ch, CURLOPT_RETURNTRANSFER    , 1 );
	            curl_setopt( $ch, CURLOPT_FAILONERROR       , 1 );
	            curl_setopt( $ch, CURLOPT_MAXREDIRS         , 10 );
	            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER    , false );
	            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST    , 2 );

	            if ( $this->userAgent )
	            {
	                curl_setopt( $ch, CURLOPT_USERAGENT, $this->userAgent );
	            }
	            else
	            {
	                /* Some sites, like Facebook, specifically sniff the user agent and complain if it's not as expected */
	                curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13' );
	            }

	            /**
	             * Cannot set this when safe_mode or open_basedir is enabled
	             * @link http://forums.invisionpower.com/index.php?autocom=tracker&showissue=11334
	             */
	            if ( ! ini_get('open_basedir') AND ! ini_get('safe_mode') )
	            {
	                curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	            }

	            if( $this->auth_req )
	            {
	                curl_setopt( $ch, CURLOPT_USERPWD   , "{$this->auth_user}:{$this->auth_pass}" );
	            }
	            elseif ( $this->auth_raw )
	            {
	                curl_setopt( $ch, CURLOPT_HTTPHEADER, array( $this->auth_raw ) );
	            }

	            //-----------------------------------------
	            // Are we posting?
	            //-----------------------------------------

	            if( is_array($post_array) AND count($post_array) )
	            {
	                $post_back  = array();

	                foreach ( $post_array as $key => $val )
	                {
	                    $post_back[] = $this->key_prefix . $key . '=' . urlencode($val);
	                }

	                $post_back_str  = implode( '&', $post_back);

	                curl_setopt( $ch, CURLOPT_POST          , true );
	                curl_setopt( $ch, CURLOPT_POSTFIELDS    , $post_back_str );
	            }
	            elseif ( $post_array )
	            {
	                curl_setopt( $ch, CURLOPT_POST          , true );
	                curl_setopt( $ch, CURLOPT_POSTFIELDS    , $post_array );
	            }
	            else
	            {
	                curl_setopt( $ch, CURLOPT_POST          , false );
	            }

	            $data = curl_exec($ch);

	            //-----------------------------------------
	            // Handle some errors we can handle
	            //-----------------------------------------

	            if ( curl_errno($ch) == 60 ||  curl_errno($ch) == 77 )
	            {
                    $this->warnings[] = "CURL: fallback due error " . curl_errno($ch);
	                // CURL_SSL_CACERT
	                if ( defined('CA_BUNDLE_PATH') )
	                {
	                    curl_setopt( $ch, CURLOPT_CAINFO, CA_BUNDLE_PATH );
	                }
	                else
	                {
	                    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
	                }

	                $data = curl_exec($ch);
	            }
	            else if ( curl_errno($ch) == 6 )
	            {
                    $this->warnings[] = "CURL: fallback due error " . curl_errno($ch);
	                // Name lookup failed / DNS issues
	                $url_parts  = @parse_url( $file_location );
	                $ip         = @gethostbyname( $url_parts['host'] );

	                if ( $ip and preg_match( '#^\d{1,3}\.#', $ip ) )
	                {
	                    // Turn of verify SSL as the IP will not match domain of cert
	                    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
	                    curl_setopt( $ch, CURLOPT_URL, $url_parts['scheme'] . '://' . $ip . '/' . $url_parts['path'] );
	                    $data = curl_exec($ch);
	                }
	            }
	            $error = curl_error($ch);
	            if(!empty($error))
	            {
	                $this->errors[] = "CURL:" . $error;
	            }
	            curl_close($ch);

	            if ( stristr( $data, 'HTTP/1.0 100 Continue' ) or stristr( $data, 'HTTP/1.1 100 Continue' ) )
	            {
	                $data = preg_replace( '#^HTTP/1.\d 100 Continue\r\n\r\n(HTTP)/#s', '\1', $data );
	            }
	            if ( stristr( $data, 'HTTP/1.0 200 Connection established' ) or stristr( $data, 'HTTP/1.1 200 Connection established' ) )
	            {
	                $data = preg_replace( '#^HTTP/1.\d 200 Connection established\r\n\r\n(HTTP)/#s', '\1', $data );
	            }

	            if ( $data )
	            {
	                $tmp  = preg_split("/\r\n\r\n/", $data, 2);
	                $data = trim($tmp[1]);

	                $this->raw_headers  = trim($tmp[0]);
	            }

	            $this->http_status_code = substr( $this->raw_headers, 9, 3 );
	            $this->http_status_text = substr( $this->raw_headers, 13, ( strpos( $this->raw_headers, "\r\n" ) - 13 ) );

                if($this->http_status_code == 301)
                {
                    if ( $data )
                    {
                        $tmp  = preg_split("/\r\n\r\n/", $data, 2);
                        $data = trim($tmp[1]);

                        $this->raw_headers  = trim($tmp[0]);
                    }
                }

	            if ( $data AND !$this->http_status_code )
	            {
	                $this->http_status_code = 200;
	            }
                if(empty($this->errors))
	            {
                    $this->success = true;
                }
	            return $data;
	        }
	        else
	        {
	            $this->errors[] = 'CURL: The cURL extension is not installed.';
	            return false;
	        }
	    }
        /**
         * This constant define the time to ask again about banner_control
         */
        const VALID_BANNER_CONTROL_CACHE_TIME = 86400;

        /**
         * Summary of bannerControlAllowedByPlugin
         * This function check for the ability of forum to do is how control of the smartbanner
         * @param mixed $bannerControl this is the current value of bannercontrol,also outputs the new value. if true, forum can control if smartbanner will be shown or not. if false, plugin should force the smartbanner to be showed
         * @param mixed $banner_last_check this is the last time we checked about bannercontrol, also outputs the new value.
         * @param mixed $boardUrl the board url
         * @param mixed $apiKey the api key
         * @return bool returns true if $bannerControl and $bannerLastCheck values changed so we will need to store these on forum system for following calls.
         */
        public function bannerControlAllowedByPlugin(&$bannerControl, &$bannerLastCheck, $boardUrl, $apiKey)
        {
            try
            {
                if (empty($bannerLastCheck) || time() - $bannerLastCheck > self::VALID_BANNER_CONTROL_CACHE_TIME){
                    $url = "https://verify.tapatalk.com/get_forum_info.php";
                    $data = array(
                        'key' => md5($apiKey),
                        'url' => $boardUrl,
                    );
                    $response = $this->getContentFromSever($url,$data,'post',true);
                    if (!empty($response)){
                        $forum_info = array();
                        if (!empty($response)){
                            $infos = preg_split('/\s*?\n\s*?/', $response);
                            foreach ($infos as $info){
                                $value = preg_split('/\s*:\s*/', $info, 2);
                                $forum_info[trim($value[0])] = isset($value[1]) ? $value[1] : '';
                            }

                        }
                        if (isset($forum_info['banner_control'])){
                            $banner_control = (bool)$forum_info['banner_control'];
                            $bannerControl = $banner_control;
                        }
                    }
                    $bannerLastCheck = time();
                    return true;
                }
                return false;
            }
            catch(Exception $ex)
            {
                //if fail we allow forum to do control
                $bannerControl = true;
                $bannerLastCheck = time();
            }
        }

        /**
         * Summary of getForumInfo
         * This function check for the ability of forum to do is how control of the smartbanner
         * @param mixed $boardUrl the board url
         * @param mixed $apiKey the api key
         * @return bool returns $bannerControlData.
         */
        public function getForumInfo($boardUrl, $apiKey)
        {
            try
            {
                $url = "http://search.tapatalk.com/api/plugin/forum_info";
                $data = array(
                    'key' => $apiKey,
                    'url' => $boardUrl,
                );
                $bannerControlData = array('banner_enable' => -1);
                $response = $this->getContentFromSever($url,$data,'post',true);
                if (!empty($response)){
                    $response = json_decode($response, true);
                    if(isset($response['data']))
                    {
                        $bannerControlData = $response['data'];
                        if(isset($bannerControlData['image_redirct']))
                        {
                            $bannerControlData['image_redirect'] = $bannerControlData['image_redirct'];
                            unset($bannerControlData['image_redirct']);
                        }
                        if(isset($bannerControlData['file_redirct']))
                        {
                            $bannerControlData['file_redirect'] = $bannerControlData['file_redirct'];
                            unset($bannerControlData['file_redirct']);
                        }
                    }
                }
            }
            catch(Exception $ex)
            {
                //if fail we allow forum to do control
                $bannerControlData = array('banner_enable' => -1);
            }
            return $bannerControlData;
        }
        public function calcSwitchOptions($TT_bannerControlData, &$app_banner_enable, &$google_indexing_enabled)
        {
            if(is_array($TT_bannerControlData)== false){
             	$TT_bannerControlData = array('banner_enable' => -1);
            }
            if(isset($TT_bannerControlData['banner_enable']) && $TT_bannerControlData['banner_enable'] != -1) //can connect to tt server and not get empty data
            {
                $app_banner_enable = isset($TT_bannerControlData['banner_enable']) ? $TT_bannerControlData['banner_enable'] : 1;
            }

            if(isset($TT_bannerControlData['google_enable']) && $TT_bannerControlData['google_enable'] != -1) //can connect to tt server and not get empty data
            {
                $google_indexing_enabled = isset($TT_bannerControlData['google_enable']) ? $TT_bannerControlData['google_enable'] : 1;
            }
            return $TT_bannerControlData;
        }
	    public function actionVerification($code,$method, $useSecureConnection = true)
	    {
	        $url = $useSecureConnection ? "https://verify.tapatalk.com/plugin_verify.php" : "http://verify.tapatalk.com/plugin_verify.php";
	        $this->errors = array();
            $this->warnings = array();
	        $data['code'] = $code;
	        $data['method'] = $method;
	        $data['ip'] = $_SERVER['REMOTE_ADDR'];
	        $response = $this->getContentFromSever($url,$data,'post',true);
            if($method == "login" && $this->success == false)
            {
                return true;
            }
	        if($response === '1')
	        {
	            return true;
	        }
	        return false;
	    }

	    public function verify_connection($type = 'both', $code) {
	        $type = strtolower($type);
	        $sso = false;
	        $push = false;
	        $tapatalk = false;
	        $verify = false;
	        $push_error = array();
	        $sso_error = array();
	        $tapatalk_error = array();
	        $verify_error = array();
		$push_warning = array();
	        $sso_warning = array();
	        $tapatalk_warning = array();
	        $verify_warning = array();
	        if ($type == 'sso' || $type == 'both'){
	            $url = 'https://directory.tapatalk.com/au_reg_verify.php';
	            $data['test'] = true;
	            $response = $this->getContentFromSever($url, $data, 'post', false);
	            if ($this->success && $response === '1') {
	                $sso = true;
	                $sso_error = $this->errors;
	            }else{
	                $this->errors[] = $response;
	                $sso_error = $this->errors;
	            }
	             $sso_warning = $this->warnings;
	        }
	        $this->errors = array();
	        $this->warnings = array();
	        if ($type == 'push' || $type == 'both'){
	            $url = 'http://push.tapatalk.com/push.php';
	            $data['test'] = true;
	            $response = $this->getContentFromSever($url, $data, 'post', false);
	            if ($this->success && $response === '1'){
	                $push = true;
	                $push_error = $this->errors;
	            }else{
	                $this->errors[] = $response;
	                $push_error = $this->errors;
	            }
		       $push_warning = $this->warnings;
	        }
	        $this->errors = array();
            $this->warnings = array();
            if ($type == 'tapatalk' || $type == 'both'){
	            $url = 'https://tapatalk.com/plugin_verify.php';
	            $data['method'] = 'verify_connection';
	            $data['code'] = $code;
	            $response = $this->getContentFromSever($url, $data, 'post', true);
	            if ($this->success && $response === '1'){
	                $tapatalk = true;
	                $tapatalk_error = $this->errors;
	            }else{
	                $this->errors[] = $response;
	                $tapatalk_error = $this->errors;
	            }
		     $tapatalk_warning = $this->warnings;
	        }
	        $this->errors = array();
	        $this->warnings = array();
	        if ($type == 'verify' || $type == 'both'){
	            $url = 'https://verify.tapatalk.com/plugin_verify.php';
	            $data['method'] = 'verify_connection';
	            $data['code'] = $code;
	            $response = $this->getContentFromSever($url, $data, 'post', true);
	            if ($this->success && $response === '1'){
	                $verify = true;
	                $verify_error = $this->errors;
	            }else{
	                $this->errors[] = $response;
	                $verify_error = $this->errors;
	            }
	     	     $verify_warning = $this->warnings;
	        }

	        switch ($type){
	            case 'sso':
	                return array(
	                    'sso' => $sso,
	                    'sso_error' => $sso_error,
	                );
	            case 'push':
	                return array(
	                    'push' => $push,
	                    'push_error' => $push_error,
	                );
	            case 'tapatalk':
	                return array(
	                    'tapatalk' => $tapatalk,
	                    'tapatalk_error' => $tapatalk_error,
	                );
	            case 'verify':
	                return array(
	                    'verify' => $verify,
	                    'verify_error' => $verify_error,
	                );
	            case 'both':
	                return array(
	                    'push' => $push,
	                    'sso' => $sso,
	                    'tapatalk' => $tapatalk,
	                    'verify' => $verify,
	                    'push_error' => $push_error,
	                    'sso_error' => $sso_error,
	                    'tapatalk_error' => $tapatalk_error,
	                    'verify_error' => $verify_error,
			    'push_warning' => $push_warning,
	                    'sso_warning' => $sso_warning,
	                    'tapatalk_warning' => $tapatalk_warning,
	                    'verify_warning' => $verify_warning,
	                );
	            default:
	                return false;
	        }
	    }
	}
}