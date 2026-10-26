<?php
/**
* The logout file
* destroys the session
* expires the cookie
* redirects to index.php
*/
session_start();
session_destroy();
setcookie('uid', '', time() - 1*24*60*60);
setcookie('uhash', '', time() - 1*24*60*60);
header("location: prousers.php?logout"); // for production change it to index.php, 
//header("location: /?logout");
?>
