<?php

if(isset($_GET['fid']))
{
    $_GET['method_name'] = 'get_url_by_id';
    $_GET['mode'] = 'forum';
    $_GET['id'] = $_GET['fid'];
    $_GET['redirect'] = true;
}
else if(isset($_GET['tid']))
{
    $_GET['method_name'] = 'get_url_by_id';
    $_GET['mode'] = 'topic';
    $_GET['id'] = $_GET['tid'];
    $_GET['redirect'] = true;
}
else if(isset($_GET['pid']))
{
    $_GET['method_name'] = 'get_url_by_id';
    $_GET['mode'] = 'post';
    $_GET['id'] = $_GET['pid'];
    $_GET['redirect'] = true;
}
require_once('mobiquo.php');
