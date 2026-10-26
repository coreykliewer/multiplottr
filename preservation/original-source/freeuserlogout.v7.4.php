<?php
// this code was updated to PHP 7.4 via CHATGPT

/**
 * The logout file
 * destroys the session
 * expires the cookie
 * redirects to index.php
 */
session_start();
session_destroy();

// Expire the cookies
setcookie('uid', '', time() - 86400, '/');  // 1 day in seconds
setcookie('uhash', '', time() - 86400, '/');

// Redirect to freeusers.php
header("Location: freeusers.php?logout"); // for production change it to index.php
// header("Location: /?logout");
exit(); // Ensure the script stops executing after the redirect
?>
