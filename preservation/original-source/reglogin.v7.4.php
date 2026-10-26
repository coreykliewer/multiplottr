<?php
// this code was updated to PHP 7.4 via CHATGPT

/**
 * login register
 */
$loginFlag = $logoutFlag = false;
$noerr = true;

// added on 20200718 by LRivera for testing purposes - discard
if (isset($_GET['mp_test']) && $_GET['mp_test'] == "true") {
    echo "Login test = OK.<br>";
    var_dump($pltr);
    var_dump([$_GET['mp_uid'], $_GET['mp_login']]);

    $enlace = mysqli_connect($dbconfig['host'], $dbconfig['user'], $dbconfig['pass']);
    if (!$enlace) {
        die('No pudo conectarse: ' . mysqli_connect_error());
    }
    echo 'Conectado satisfactoriamente';
    mysqli_close($enlace);

    $res = $pltr->autoWpLogin($_GET['mp_uid'], $_GET['mp_login']);
    var_dump($res);
    exit();
}

/*
Description: Auto login user redirected from WordPress login
Author: Luis Rivera [REDACTED_EMAIL]
*/
if (isset($_GET['mp_uid'], $_GET['mp_login'])) {

    // added on 2019-05-12 21:19 by LRivera for testing purposes - discard
    if (isset($_GET['mp_test']) && $_GET['mp_test'] == "true") {
        echo "Login test = OK.<br>";
        var_dump($pltr);
        var_dump([$_GET['mp_uid'], $_GET['mp_login']]);
        exit();
    }

    $res = $pltr->autoWpLogin($_GET['mp_uid'], $_GET['mp_login']);

    // added on 2019-05-12 21:19 by LRivera for testing purposes - discard
    if (isset($_GET['mp_test']) && $_GET['mp_test'] == "true") {
        echo "getting class...";
        print_r($res);
    }

    if ($res && $res['user_id']) {
        $_user = $res;
        $flag_valid_subscription = isset($_GET['mp_subscription']) && $_GET['mp_subscription'] == 'true' ? true : false;
        $pltr->login($_user, $flag_valid_subscription);
        $loginFlag = true;

        // header("Location: /");
        // exit(0);
    }
}

if (isset($_POST['login']) && $_POST['login'] == 'Login') {
    $email = $_POST['username'];
    $password = md5($_POST['password']);

    $res = $pltr->checkLogin($email, $password);
    if ($res && $res['user_id']) {
        $_user = $res;
        $pltr->login($_user);
        $loginFlag = true;

    } else {
        $_user = null;
        echo "
        <script type='text/javascript'>
            alert('Wrong User Email or Password. If you feel you have reached this message in error please check out or Blog for further details.');
        </script>
        ";
    }
} else if (isset($_POST['register']) && $_POST['register'] == 'Register') {
    $username = $_POST['regUsername'];
    $password = $_POST['regPassword'];
    $email = $_POST['email'];
    $err = false;
    if ($username && $password && $email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err = "Invalid email";
        }

        if (!$err && $pltr->isUsernameExists($username)) {
            $err = "Username already exists";
        }
        if (!$err && $pltr->isEmailExists($email)) {
            if (!$err) {
                $err = "Email already exists";
            } else {
                $err = "Username and Email already exist";
            }
        }
        if (!$err) {
            if (($newUserId = $pltr->registerUser($username, $email, $password)) != true) {
                $err = "Error: User registration failed";
            }
        }
    } else {
        $err = "Username, email and password is mandatory";
    }

    if (!$err) {
        $_user = $pltr->getUserById($newUserId);
        $pltr->login($_user);
        echo "
        <script type='text/javascript'>
        sessionStorage.loggingIn ='registered';
        </script>";
    } else {
        echo "
        <script type='text/javascript'>
        alert('".$err."');
        sessionStorage.loggingIn = '".$err."';
        </script>";
    }
} else if (isset($_POST['forgotCredentialSubmit']) && $_POST['forgotCredentialSubmit'] == 'Submit') {
    $noerr = $pltr->forgotPasswordConventional($_POST['email']);
} else if (isset($_POST['submitPw']) && $_POST['submitPw'] == 'Submit') {
    if ($_user) {
        $noerr = $pltr->resetPassword($_user['user_id'], $_POST['regPassword']);
    }
} else if (isset($_POST['submitEml']) && $_POST['submitEml'] == 'Submit') {
    if ($_user) {
        $noerr = $pltr->changeEmail($_user['user_id'], $_POST['chngEml']);
        if ($noerr === true) {
            $_newEmail = $_POST['chngEml'];
        }
    }
} else if (isset($_POST['action']) && $_POST['action'] == 'deleteaccount' && isUserLoggedIn() && (isAdminLoggedIn() == false)) { // delete account other than admin
    if ($_user['user_id']) {
        if ($pltr->deleteAccount($_user['user_id'])) {
            // account deleted fire logout/
            header('location: logout.php');
        } else {
            return false;
        }
    }
} else if (isset($_POST['action']) && $_POST['action'] == 'deletemap' && isUserLoggedIn()) {
    if (isset($_POST['mapid']) && $_POST['mapid']) {
        if ($pltr->isOwnerOfMap($_POST['mapid'], $_user['user_id']) || isAdminLoggedIn()) {
            if ($pltr->deleteMap($_POST['mapid'])) {
                header('location: /');
            }
        } else {
            return false;
        }
    }
} else if (isset($_POST['action']) && $_POST['action'] == 'savemap' && isUserLoggedIn()) { // save map
    $mapName = $_POST['mapTitleFieldSubmit'];
    $mapDescription = $_POST['mapDescriptionFieldSubmit'];
    $mapInfo = $_POST['markerLocationField'];
    $mapType = '';
    $mapLink = '';
    if (isset($_GET['map_id']) && $_GET['map_id']) { // existing map
        $mapId = $_GET['map_id'];
        if (isset($_POST['saveOverwriteMapOption']) && $_POST['saveOverwriteMapOption'] == 'newmap') {
            $res = $pltr->addNewMap($_user['user_id'], $mapName, $mapDescription, $mapType, $mapLink, $mapInfo);
            if ($res) {
                header('location: ?map_id=' . $res);
            }
        } else {
            if ($pltr->isOwnerOfMap($mapId, $_user['user_id']) || isAdminLoggedIn()) {
                $res = $pltr->updateMap($mapId, $mapName, $mapDescription, $mapType, $mapLink, $mapInfo);
            }
        }
        if ($res) {
            return true;
        } else {
            return false;
        }
    } else { // save a new map
        $res = $pltr->addNewMap($_user['user_id'], $mapName, $mapDescription, $mapType, $mapLink, $mapInfo);
        if ($res) {
            header('location: ?map_id=' . $res);
        }
    }
}

if ($noerr !== true) {
    echo "
        <script type='text/javascript'>
        alert('".$noerr."');
        </script>";
}

// logout fix
if (isset($_GET['logout'])) $logoutFlag = true;
?>
