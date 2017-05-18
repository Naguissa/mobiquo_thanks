<?php
/*
 * this class make a fake $request like phpbb31 so we can use the same code on both forum systems
 * 
 */
namespace phpbb\request;

/**
* An interface through which all application input can be accessed.
*/
interface request_interface
{
    /**#@+
    * Constant identifying the super global with the same name.
    */
    const POST = 0;
    const GET = 1;
    const REQUEST = 2;
    const COOKIE = 3;
    const SERVER = 4;
    const FILES = 5;
}
class fake_request
{
    function overwrite($varName, $value, $super_global = \phpbb\request\request_interface::REQUEST)
    {
        if($super_global == \phpbb\request\request_interface::POST)
        {
            $_POST[$varName] = $value;
        }
        else if($super_global == \phpbb\request\request_interface::GET)
        {
            $_GET[$varName] = $value;
        }
        else if($super_global == \phpbb\request\request_interface::REQUEST)
        {
            $_REQUEST[$varName] = $value;
        }
        else if($super_global == \phpbb\request\request_interface::COOKIE)
        {
            $_COOKIE[$varName] = $value;
        }
        else if($super_global == \phpbb\request\request_interface::SERVER)
        {
            $_SERVER[$varName] = $value;
        }
        else if($super_global == \phpbb\request\request_interface::FILES)
        {
            $_FILES[$varName] = $value;
        }
        
        foreach($_GET  as $key => $value) $_REQUEST[$key] = $value;
        foreach($_POST as $key => $value) $_REQUEST[$key] = $value;
    }
    function enable_super_globals()
    {}
}

$request = new fake_request();
?>