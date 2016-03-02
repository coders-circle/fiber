<?php
require_once "../classes/ModelObject.php";

class Example extends ModelObject
{
    // When schema is not defined explicitly, it is automatically
    // deduced from the properties of the first object that is saved.

    public $example_data = "example";

    // Optionally, one may define the schema as follows.
    // Explicitly defining the schema has certain advantages:
    //
    // * One can specify further attributes for SQL fields like max_length
    // * One can set properties for a Model which are not fields in schema
    //
    public function get_schema() {
        return array(
            array("example_data", "string")
        );
    }
}

?>
