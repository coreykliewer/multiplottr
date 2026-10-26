<?php
// this code was updated to PHP 7.4 via CHATGPT
// addNewMap() function debugged and fixed by Luis Rivera [REDACTED_EMAIL]
// updateMap() function debugged and fixed by Luis Rivera [REDACTED_EMAIL]

class Plotter {
    public $_db;

    public function __construct() {
        $this->_db = db::getInstance();
    }

    public function login($user, $subscription = false) {
        setcookie("uid", $user['user_id'], null, '', '', true, true); // Secure and HttpOnly flags added
        setcookie("uhash", md5($user['user_id']), null, '', '', true, true); // Secure and HttpOnly flags added

        /*
        Description: Auto login user redirected from WordPress login
        Author: Luis Rivera [REDACTED_EMAIL]
        */
        if ($subscription == true) {
            setcookie("subscription", "MP_valid_subscription_true", null, '', '', true, true); // Secure and HttpOnly flags added
        }
    }

    /*
    Description: Auto login user redirected from WordPress login
    Author: Luis Rivera [REDACTED_EMAIL]
    */
    public function autoWpLogin($user_id, $user_hash) {
        global $_GET;

        $result = array();
        $user_id = $this->_db->prepare($user_id);
        $user_hash = $this->_db->prepare($user_hash);

        $sql = "SELECT user_id, user_email, user_username, user_profile, user_status FROM users WHERE user_id = '$user_id' AND user_hash = '$user_hash'";
        if ($result = $this->_db->fetch_one($sql, true)) {
            setcookie("wplogin", "true", null, '', '', true, true); // Secure and HttpOnly flags added

            $sql = "UPDATE users set user_hash = '" . md5(time() . rand()) . "' WHERE user_id = '$user_id'";
            if ($this->_db->query($sql)) {
                return $result;
            }
        } else {
            if (isset($_GET['mp_test']) && $_GET['mp_test'] == "true") {
                return 'Execution Failed!';
            }
        }
    }

    public function checkLogin($username, $password) {
        $result = array();
        $username = $this->_db->prepare($username);
        $sql = "SELECT user_id, user_email, user_username, user_profile, user_status FROM users WHERE user_email = '$username' AND user_pass = "[REDACTED_CONFIG_VALUE]"";
        if ($result = $this->_db->fetch_one($sql, true)) {
            return $result;
        }
    }

    public function isUsernameExists($username) {
        $result = array();
        $username = $this->_db->prepare($username);
        $sql = "SELECT * FROM users WHERE user_username = "[REDACTED_CONFIG_VALUE]"";
        $res = $this->_db->query($sql);
        if ($result = $this->_db->num_rows($res)) {
            return true;
        }
        return false;
    }

    public function getUserById($userId) {
        $result = array();
        $sql = "SELECT user_id, user_email, user_username, user_profile, user_status FROM users WHERE user_id = '$userId'";
        $res = $this->_db->query($sql);
        if ($result = $this->_db->fetch_one($sql, true)) {
            return $result;
        }
    }

    public function getUserByEmail($email) {
        $result = array();
        $sql = "SELECT user_id, user_email, user_username, user_profile, user_status FROM users WHERE user_email = '$email'";
        $res = $this->_db->query($sql);
        if ($result = $this->_db->fetch_one($sql, true)) {
            return $result;
        }
    }

    public function isEmailExists($email) {
        $result = array();
        $email = $this->_db->prepare($email);
        $sql = "SELECT * FROM users WHERE user_email = '$email'";
        $res = $this->_db->query($sql);
        if ($result = $this->_db->num_rows($res)) {
            return true;
        }
        return false;
    }

    public function getMapsOfUser($userId) {
        $sql = "SELECT map_id, map_name, map_description, map_link, map_date FROM map WHERE user_id = '$userId' ORDER BY map_date DESC";
        $result = $this->_db->fetch_all($sql);
        return $result;
    }

    public function getMapsListingsModule($userId) {
        if (!$userId) return '';
        $result = $this->getMapsOfUser($userId);
        $items = '';
        if (is_array($result)) {
            foreach ($result as $res) {
                $urlAttrib = '';
                if ($res['map_link']) {
                    $urlAttrib .= '&' . $res['map_link'];
                }
                $items .= '<tr><td class="title"><a href="/?map_id=' . $res['map_id'] . $urlAttrib . '">' . stripslashes($res['map_name']) . '</a></td><td class="date">' . date("m/d/y", (int) $res['map_date']) . '</td><td class="delete"><form id="mapForm' . $res['map_id'] . '" method="post"><input type="hidden" name="action" value="deletemap"><input type="hidden" name="mapid" value="' . $res['map_id'] . '"><!--input type="submit" value="Delete" --><a href="javascript:void(0)" onclick=" currentMap = $(this).closest(\'form\').attr(\'id\'); mapName=$(this).closest(\'tr\').find(\'.title\').text(); deleteThisMap(); return false;">Delete</a></form></td></tr>';
            }
        }
        return $items;
    }

    public function registerUser($username, $email, $password) {
        if ($username && $password && $email) {
            $username = $this->_db->prepare($username);
            $email = $this->_db->prepare($email);
            $password = md5($password);
            $sql = "INSERT INTO users (user_username, user_email, user_pass, user_profile, user_status) VALUES ('$username', '$email', '$password', 'user', 1)";
            if ($this->_db->query($sql)) {
                return $this->_db->insert_id();
            } else {
                return false;
            }
        }
    }

    public function getMapBasicInfo($mapId) {
        $sql = "SELECT * FROM map WHERE map_id = '$mapId'";
        $res = $this->_db->fetch_one($sql);
        return array($res['map_name'], $res['map_info'], $res['map_description'], $res['user_username']);
    }

    /**
     * reset user password
     * @param type $userId
     * @param type $newPassword
     * @return boolean
     */
    public function resetPassword($userId, $newPassword) {
        $sql = "UPDATE users set user_pass = "[REDACTED_CONFIG_VALUE]" . md5($newPassword) . "' WHERE user_id = '$userId'";
        if ($this->_db->query($sql)) {
            $user = $this->getUserById($userId);
            list($subject, $message) = $this->getEmailFromTemplate('resetpassword', array('name' => $user['user_username']));
            $this->sendMail($user['user_email'], $subject, $message);
            return true;
        } else {
            return false;
        }
    }

    /**
     * change user email
     * @param type $userId
     * @param type $newEmail
     * @return boolean
     */
    public function changeEmail($userId, $newEmail) {
        $user = $this->getUserById($userId);
        if ($user['user_email'] == $newEmail) {
            return "The new email that you provided is same as your old email"; //old and new email same
        }
        if ($this->getUserByEmail($newEmail)) {
            return "The new email already exist";
        }
        $oldEmail = $user['user_email'];
        $sql = "UPDATE users set user_email = '$newEmail' WHERE user_id = '$userId'";
        if ($this->_db->query($sql)) {
            list($subject, $message) = $this->getEmailFromTemplate('changeemail', array('name' => $user['user_username'], 'oldemail' => $oldEmail, 'newemail' => $newEmail));

            $this->sendMail($newEmail, $subject, $message); //to, message,
            list($subject, $message) = $this->getEmailFromTemplate('changeemailold', array('name' => $user['user_username'], 'oldemail' => $oldEmail, 'newemail' => $newEmail));
            $this->sendMail($oldEmail, $subject, $message);
            return true;
        } else {
            return "Error!! email not changed";
        }
    }

    /**
     * email is sent to user where they click a link to reset their password
     * @param type $email
     * @return boolean
     */
    public function forgotPasswordConventional($email) {
        if (!$email || !isValidEmail($email)) {
            return "Please provide a valid email";
        }
        if ($this->isEmailExists($email)) {
            $token = $this->generateForgotPasswordToken();
            $user = $this->getUserByEmail($email);
            $this->trackForgotPassword($token, $user['user_id'], time());

            list($subject, $message) = $this->getEmailFromTemplate('forgotpassword-con', array('name' => $user['user_username'], 'passtoken' => $token));
            $this->sendMail($email, $subject, $message);
            return true;
        } else {
            return 'The email you provide does not exist';
        }
    }

    /**
     * track forgot password request by assigning token
     * @param type $token
     * @param type $user_id
     * @param type $timestamp
     * @return boolean
     */
    public function trackForgotPassword($token, $user_id, $timestamp) {
        $sql = "INSERT INTO user_fp(token, user_id, updatedon) values ('$token', '$user_id', $timestamp)";
        if ($this->_db->query($sql)) {
            return true;
        } else {
            return false;
        }
    }

    public function validPassToken($passToken) {
        $result = $this->getPassTokenData($passToken);
        if ($result && (time() - $result['updatedon']) < 24 * 60 * 60 * 7) {
            return true;
        }
        return false;
    }

    public function getPassTokenData($passToken) {
        $sql = "SELECT * from user_fp where token = '$passToken'";
        if ($result = $this->_db->fetch_one($sql, true)) {
            return $result;
        }
        return false;
    }

    public function resetFPPassword($passToken, $newPassword) {
        $data = $this->getPassTokenData($passToken);
        if ($this->resetPassword($data['user_id'], $newPassword)) {
            return "Your password has been changed successfully. A confirmation email is sent to your registered email";
        }
        return false;
    }

    /**
     * generate a unique token
     */
    public function generateForgotPasswordToken() {
        return uniqid(rand(), true);
    }

    /**
     * password is generated and sent to user in email
     * @param type $email
     * @return boolean
     */
    public function forgotPassword($email) {
        if (!$email || !isValidEmail($email)) {
            return "Please provide a valid email";
        }
        if ($this->isEmailExists($email)) {
            $newPassword = $this->generateSimplePassword();
            $user = $this->getUserByEmail($email);
            list($subject, $message) = $this->getEmailFromTemplate('forgotpassword', array('name' => $user['user_username'], 'newpassword' => $newPassword));
            $this->sendMail($email, $subject, $message);
            return true;
        } else {
            return 'The email you provide does not exist';
        }
    }

    public function generateSimplePassword($length = 6) {
        $password = "[REDACTED_CONFIG_VALUE]";
        $possible = '12346789$#!bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ';
        $maxlength = strlen($possible);
        if ($length > $maxlength) {
            $length = $maxlength;
        }
        $i = 0;
        while ($i < $length) {
            $char = substr($possible, mt_rand(0, $maxlength - 1), 1);
            if (!strstr($password, $char)) {
                $password .= $char;
                $i++;
            }
        }
        return $password;
    }

    public function isOwnerOfMap($mapId, $userId) {
        $sql = "SELECT * FROM map WHERE user_id = '$userId' AND map_id = '$mapId'";
        $res = $this->_db->query($sql);
        if ($this->_db->num_rows($res)) {
            return true;
        }
        return false;
    }

    /**
     * update an existing map
     * @param type $map_id
     * @param type $mapName
     * @param type $mapDescription
     * @param type $mapType
     * @param type $mapLink
     * @param type $mapInfo
     * @return boolean 
     */
    public function updateMap($mapId, $mapName, $mapDescription, $mapType, $mapLink, $mapInfo) {
        $mapDescription = str_replace(array("\n", "\r\n", "\r"), '', $mapDescription);
        if ($mapName && $mapInfo && $mapId) {
            $sql = sprintf("UPDATE map SET map_name = %s, map_description = %s, map_profile = %s, map_info = %s, map_show = %s WHERE map_id = %s", 
                $this->_db->filter(trim($mapName), "text"), 
                $this->_db->filter(trim($mapDescription), "text"), 
                $this->_db->filter($mapType, "text"), 
                $this->_db->filter($mapInfo, "text"), 
                $this->_db->filter(1, "int"), 
                $this->_db->filter($mapId, "int"));
            if ($this->_db->query($sql)) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * Add a new map
     * @param type $userId
     * @param type $mapName
     * @param type $mapDescription
     * @param type $mapType
     * @param type $mapLink
     * @param type $mapInfo
     * @return boolean 
     */
    public function addNewMap($userId, $mapName, $mapDescription, $mapType, $mapLink, $mapInfo) {
        
        /** LRivera DEBUGGING */
        /*$lrv_process = "first try";
        $sql_test = sprintf("INSERT INTO test (process) VALUES(%s)", $lrv_process);
        $this->_db->query($sql_test);*/
        
        $mapDescription = str_replace(array("\n", "\r\n", "\r"), '', $mapDescription);
        if ($userId && $mapName && $mapInfo) {
            $userData = $this->getUserById($userId);
            
            /** Code by Luis Rivera */
            /** Prepare variables for SQL statement */
            $p_mapName = $this->_db->filter(trim($mapName), "text");
            $p_mapDescription = $this->_db->filter(trim($mapDescription), "text");
            $p_userId = $this->_db->filter($userId, "int");
            $p_userEmail = $this->_db->filter($userData['user_email'], "text");
            $p_userName = $this->_db->filter($userData['user_username'], "text");
            $p_time = $this->_db->filter(time(), "int");
            $p_mapInfo = $this->_db->filter($mapInfo, "text");
            $p_mapType = $this->_db->filter('private', "text");;
            $p_mapShow = $this->_db->filter(1, "int");
            $p_mapLink = $this->_db->filter($mapLink, "text");
            
            $sql = sprintf("INSERT INTO map (map_name, map_description, user_id, user_email, user_username, map_date, map_profile, map_info, map_show, map_link) values (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)", 
                $p_mapName, 
                $p_mapDescription, 
                $p_userId, 
                $p_userEmail, 
                $p_userName, 
                $p_time, 
                $p_mapType,  
                $p_mapInfo, 
                $p_mapShow, 
                $p_mapLink);

                /** LRivera DEBUGGING */
                /*$time = time();
                $private = 'private';
                $uno = 1;
                $lrv_process = "mapname: ".trim($mapName);
                $lrv_process .= "\nmapdescription: ".trim($mapDescription);
                $lrv_process .= "\nID: ".$userId;
                $lrv_process .= "\nEMAIL: ".$userData['user_email'];
                $lrv_process .= "\nUSERNAME: "[REDACTED_CONFIG_VALUE]"user_username'];
                $lrv_process .= "\nTime: ".$time;
                $lrv_process .= "\nPriv: ".$private;
                $lrv_process .= "\nMapinfo: ".$mapInfo;
                $lrv_process .= "\nUno: ".$uno;
                $lrv_process .= "\nLink: ".$mapLink;
                
                $sql_test = sprintf("INSERT INTO test (process) VALUES(%s)", $lrv_process);
                $this->_db->query($sql_test);*/

            if ($this->_db->query($sql)) {
                /** LRivera DEBUGGING */
                /*$lrv_process = "true try";
                $sql_test = sprintf("INSERT INTO test (process) VALUES(%s)", $lrv_process);
                $this->_db->query($sql_test);*/
                
                return $this->_db->insert_id();
            } else {
                /** LRivera DEBUGGING */
                /*$lrv_process = "false try";
                $sql_test = sprintf("INSERT INTO test (process) VALUES(%s)", $lrv_process);
                $this->_db->query($sql_test);*/

                return false;
            }
        }
        return false;
    }

    /**
     * delete an account, it also delete related maps
     * @param type $userId
     * @return boolean 
     */
    public function deleteAccount($userId) {
        if (!$userId) return;
        if ($this->deleteMapsOfUser($userId)) {
            $sql = sprintf("DELETE FROM users WHERE user_id = '%s'", $userId);
            if ($this->_db->query($sql)) {
                return true;
            } else {
                return true;
            }
        }
        return true;
    }

    /**
     * delete map
     * @param type $mapId
     * @return boolean 
     */
    public function deleteMap($mapId) {
        if ($mapId) {
            $sql = sprintf("DELETE FROM map WHERE map_id = '%s'", $mapId);
            if ($this->_db->query($sql)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * delete all maps of a user
     * @param type $userId
     * @return boolean 
     */
    public function deleteMapsOfUser($userId) {
        if ($userId) {
            $sql = sprintf("DELETE FROM map WHERE user_id = '%s'", $userId);
            if ($this->_db->query($sql)) {
                return true;
            } else {
                return true;
            }
        }
    }

    /**
     * delete a map of a user
     * @param type $mapId
     * @param type $userId
     * @return boolean 
     */
    public function deleteMapOfUser($mapId, $userId) {
        if ($userId && $mapId) {
            $sql = sprintf("DELETE FROM map WHERE user_id = '%s' AND map_id = '%s'", $userId, $mapId);
            if ($this->_db->query($sql)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * save url attributes
     * @param type $mapId
     * @param type $urlAttributes
     * @return boolean 
     */
    public function saveUrlAttribs($mapId, $urlAttributes) {
        if ($mapId && $urlAttributes) {
            $sql = sprintf("UPDATE map set map_link ='%s' WHERE map_id = '%s'", $urlAttributes, $mapId);
            if ($this->_db->query($sql)) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function getEmailFromTemplate($id, $params) {
        $mailHeader = '';
        $mailFooter = '';
        $message = $subject = '';
        switch ($id) {
            case 'forgotpassword':
                $subject = 'Multiplottr - your new password.';
                $message = 'Dear ' . $params['name'] . '<br>';
                $message .= 'Please find your new password : "[REDACTED_CONFIG_VALUE]"newpassword'] . '<br> 
                If you did not request this change or believe an error has been made, please contact at [REDACTED_EMAIL]<br><br>';
                $message .= 'Regards,<br>';
                $message .= 'Multiplottr Team';
                break;
            case 'forgotpassword-con':
                $subject = 'Multiplottr - password change request.';
                $message = 'Dear ' . $params['name'] . '<br>';
                $message .= 'We have received a password change request <br> 
                If you did not request this change or believe an error has been made, please contact at [REDACTED_EMAIL]<br><br>
                <a href=http://' . $_SERVER['HTTP_HOST'] . '/resetpassword.php?passtoken=' . $params['passtoken'] . '>Change your password</a><br>';
                $message .= 'Regards,<br>';
                $message .= 'Multiplottr Team';
                break;
            case 'resetpassword':
                $subject = 'Multiplottr - your password is changed.';
                $message = 'Dear ' . $params['name'] . '<br>';
                $message .= '
                If you did not request this change or believe an error has been made, please contact at [REDACTED_EMAIL]<br><br>';
                $message .= 'Regards,<br>';
                $message .= 'Multiplottr Team';
                break;
            case 'changeemail':
                $subject = 'Multiplottr - email change.';
                $message = 'Dear ' . $params['name'] . '<br>';
                $message .= 'As per your request your email has been changed<br>';
                $message .= 'Old email : <strong>' . $params['oldemail'] . '</strong><br>';
                $message .= 'New email : <strong>' . $params['newemail'] . '</strong><br>';
                $message .= 'If you did not request this change or believe an error has been made, please contact at [REDACTED_EMAIL]<br><br>';
                $message .= 'Regards,<br>';
                $message .= 'Multiplottr Team';
                break;
            case 'changeemailold':
                $subject = 'Multiplottr - email change.';
                $message = 'Dear ' . $params['name'] . '<br>';
                $message .= 'As per your request your email has been changed<br>';
                $message .= 'Old email : <strong>' . $params['oldemail'] . '</strong><br>';
                $message .= 'New email : <strong>' . $params['newemail'] . '</strong><br>';
                $message .= 'If you did not request this change or believe an error has been made, please contact at [REDACTED_EMAIL]<br><br>';
                $message .= 'Regards,<br>';
                $message .= 'Multiplottr Team';
                break;
        }
        if (!$message) return;
        $message = $mailHeader . $message . $mailFooter;
        return array($subject, $message);
    }

    public function sendMail($to, $subject, $message) {
        require_once 'PHPMailer/class.phpmailer.php';
        $mail = new PHPMailer;
        $mail->IsSMTP();
        $mail->Host = 'secure132.inmotionhosting.com';
        $mail->SMTPAuth = true;
        $mail->Username = "[REDACTED_CONFIG_VALUE]";
        $mail->Password = "[REDACTED_CONFIG_VALUE]";
        $mail->SMTPSecure = 'tls';
        $mail->From = '[REDACTED_EMAIL]';
        $mail->FromName = 'Multiplottr';
        $mail->AddAddress($to);
        $mail->IsHTML(true);

        $mail->Subject = $subject;
        $mail->Body = $message;

        if (!$mail->Send()) {
            //echo 'Message could not be sent.';
            //echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isUserLoggedIn() {
    global $_user;
    return !empty($_user['user_id']);
}

function isAdminLoggedIn() {
    global $_user;
    return isUserLoggedIn() && $_user['user_profile'] == 'admin';
}

function getMapLink() {
    $attribs = [];
    foreach ($_GET as $key => $val) {
        $attribs[] = $key . '=' . urlencode($val);
    }
    return implode('&', $attribs);
}
?>