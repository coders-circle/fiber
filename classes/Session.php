<?php

class Session {

    public static function reset() {
        session_unset();
    }

    public static function destroy() {
        self::reset();
        session_destroy();
    }

    public static function get($key) {
        if (!isset($_SESSION[$key]))
            return null;
        return $_SESSION[$key];
    }

    public static function store($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function remove($key) {
        unset($_SESSION[$key]);
    }
}

 ?>
