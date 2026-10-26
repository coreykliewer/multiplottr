<?php

class PlotterDB {

    public $_db;

    public function __construct() {
        $this->_db = db::getInstance();
    }

    public function login($username, $password) {
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

    public function isEmailExists($email) {
        $result = array();
        $email = $this->_db->prepare($email);
        $sql = "SELECT * FROM users WHERE user_username = "[REDACTED_CONFIG_VALUE]"";
        $res = $this->_db->query($sql);
        if ($result = $this->_db->num_rows($res)) {
            return true;
        }
        return false;
    }

    public function getMapsOfUser($userId) {
        $sql = "SELECT map_id, map_name, map_description, map_date from map where user_id = '$userId'";
        $result = $this->_db->fetch_all($sql);
        return $result;
    }

    public function getMapsListingsModule($userId) {
        if (!$userId)
            return '';
        $result = $this->getMapsOfUser($userId);
        $items = '';
        if (is_array($result)) {
            foreach ($result as $res) {
                $items .= '<li class="title"><a href="/' . $res['map_id'] . '">' . $res['map_name'] . '</a></li><li class="date">' . date("F j, Y", (int) $res['map_date']) . '</li><li class="delete"><a href="javascript:void(0)">Delete</a></li>';
            }
        }
        return $items;
    }

}


function isUserLoggedIn() {
    global $_user;
    if($_user && $_user['user_id']) {
        return true;
    }
    return false;
}