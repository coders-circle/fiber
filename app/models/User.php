<?php

class User extends Model {
    public function get_schema() {
        return array(
            array("username", "string", "unique"=>true),
            array("password", "string", "max_length"=>255),
            array("created_at", "datetime")
        );
    }

    public function presave() {
        if (!isset($this->created_at))
            $this->created_at = new DateTime();
    }
}

 ?>
