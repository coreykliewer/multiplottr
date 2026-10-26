<?php
//set or clear local session - hack
if ($logoutFlag) {
    echo "
    <script type='text/javascript'>
    sessionStorage.loggedIn = '';
    sessionStorage.userEmail ='';
	sessionStorage.userName = "[REDACTED_CONFIG_VALUE]";
    localStorage['loggedIn']='false';
	sessionStorage.hideIntro='';
	window.location.href = window.location.pathname;
    </script>
    ";
}

if($loginFlag) {
    echo "
    <script type='text/javascript'>
	sessionStorage.userName = "[REDACTED_CONFIG_VALUE]".$_user['user_username']."';
	sessionStorage.userEmail = '".$_user['user_email']."';
	sessionStorage.loggedIn = 'yes';
    </script>
    ";
}

if($_newEmail) {
      
     echo "
    <script type='text/javascript'>
        //alert('".$_newEmail."')
	sessionStorage.userEmail = '".$_newEmail."';
    </script>
    ";
}