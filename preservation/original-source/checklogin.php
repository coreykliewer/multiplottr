<?php 
# Check User Authentication
if( isset($_COOKIE['uid']) && isset($_COOKIE['uhash']) )
{
    if(md5($_COOKIE['uid']) == $_COOKIE['uhash']) {
        $res = $pltr->getUserById($_COOKIE['uid']);
        if ($res && $res['user_id']) {
            $_user = $res;
        }
     } else {
       $_user = null;
     }
} else {
     $_user = null;
}      
?>