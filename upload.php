<?php
if(isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'],'json') !== false)
{
    require_once('tapatalk.php');
}
else
{
    $_POST['format'] = 'xmlrpc';
    require_once('mobiquo.php');
}
