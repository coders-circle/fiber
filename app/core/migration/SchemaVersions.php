<?php
require_once "app/core/Model.php";

class SchemaVersions extends Model
{
    public function get_schema() {
        return array(
            array("table_name", "string"),
            array("version", "integer")
        );
    }
}

?>
