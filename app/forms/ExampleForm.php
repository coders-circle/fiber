<?php

require_once "app/core/Form.php";


class UserForm extends Form {
    public function get_schema() {
        return array(
            array("username", "string"),
            array("password", "password"),
        );
    }
}


class ExampleForm extends Form {
    public function get_schema() {
        return array(
            array("example_string", "string"),
            array("example_integer", "integer"),
            array("example_text", "text"),
            array("example_datetime", "datetime"),
            array("example_boolean", "boolean"),
            array("example_users", "children", "user", new UserForm()),
        );
    }
}

 ?>
