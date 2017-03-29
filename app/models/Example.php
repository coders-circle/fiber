<?php

class Example extends Model
{
    // Define the current schema
    // Make sure to migrate:
    // * once at first, to create the table
    // * whenever the schema is changed, to alter the table

    public function get_schema() {
        return array(
            array("example_string", "string"),
            array("example_integer", "integer"),
            array("example_text", "text"),
            array("example_datetime", "datetime"),
            array("example_boolean", "boolean"),
        );
    }
}

?>
