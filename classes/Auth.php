<?php

require_once 'Session.php';

class Auth {

    public static function get_hash($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verify_password($password, $hash) {
        return password_verify($password, $hash);
    }

    // Assumes that the user object has a password field
    public static function set_password($user, $password) {
        $user->password = self::get_hash($password);
    }

    // Assumes that the user object has a password field
    public static function verify_user($user, $password) {
        return self::verify_password($password, $user->password);
    }

    // Assumes that the user object has password and id fields
    public static function authenticate($user, $password) {
        self::logout();
        if (self::verify_user($user, $password)) {
            self::login($user);
            return true;
        }
        return false;
    }

    // Assumes that the user object has an id field
    public static function login($user) {
        Session::store("user", $user->id);
    }

    public static function logout() {
        Session::remove("user");
    }

    // Assumes user_model_class exists and inherits from Model
    public static function get_user($user_model_class) {
        $userid = Session::get("user");
        if (!$userid)
            return null;

        return $user_model_class::query()->where("id=?", $userid)->first();
    }
}
 ?>
