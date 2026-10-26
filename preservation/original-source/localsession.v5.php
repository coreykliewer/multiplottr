<?php
// this code was updated to PHP 7.4 via CHATGPT

// Set or clear local session - hack
if ($logoutFlag) {
    echo "
    <script type='text/javascript'>
    sessionStorage.loggedIn = '';
    sessionStorage.userEmail = '';
    sessionStorage.userName = "[REDACTED_CONFIG_VALUE]";
    localStorage['loggedIn'] = 'false';
    sessionStorage.hideIntro = '';
    window.location.href = window.location.pathname;
    </script>
    ";
}

if ($loginFlag) {
    echo "
    <script type='text/javascript'>
    sessionStorage.userName = "[REDACTED_CONFIG_VALUE]" . htmlspecialchars($_user['user_username'], ENT_QUOTES, 'UTF-8') . "';
    sessionStorage.userEmail = '" . htmlspecialchars($_user['user_email'], ENT_QUOTES, 'UTF-8') . "';
    sessionStorage.loggedIn = 'yes';
    </script>
    ";
}

if ($_newEmail) {
    echo "
    <script type='text/javascript'>
    // alert('" . htmlspecialchars($_newEmail, ENT_QUOTES, 'UTF-8') . "')
    sessionStorage.userEmail = '" . htmlspecialchars($_newEmail, ENT_QUOTES, 'UTF-8') . "';
    </script>
    ";
}
?>
