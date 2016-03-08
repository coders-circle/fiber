<?php

require_once "../classes/Model.php";

class User extends Model {
    public function get_schema() {
        return array(
            array("username", "string", "extra"=>"UNIQUE"),
            array("password", "string", "max_length"=>255),
        );
    }
}

 ?>
