<?php
/**
* The logout file
* destroys the session
* expires the cookie
* redirects to index.php
*/

if (isset($_COOKIE['wplogin']) && $_COOKIE['wplogin']== "true" ) { 
$wpLoggedIn = true;}



session_start();
session_destroy();
setcookie('uid', '', time() - 1*24*60*60);
setcookie('uhash', '', time() - 1*24*60*60);

/*
Description: end user subscription cookie from WP Autologin
Author:	Luis Rivera [REDACTED_EMAIL]
*/
setcookie('subscription', '', time() - 1*24*60*60);
setcookie("wplogin", "", time() - 1*24*60*60);
//header("location: index_test.php?logout"); // for production change it to index.php, 
//header("location: /?logout");




if ($wpLoggedIn) {
	
   /*echo "<script> sessionStorage.clear(); </script>";*/
	
	header( "Location: https://system.multiplottr.com/wp-login.php?action=logout" );} else {header("location: /?logout");}
?>