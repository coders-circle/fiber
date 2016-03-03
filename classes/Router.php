<?php
require_once 'RouterBase.php';

class Router extends RouterBase
{
    public function __construct()
    {

        $this->routing_rules = array(
            // Default rule
            "default" => "fiber",

            // Template redirection rules
            "fiber" => array("template", "fiber.html"),

            // Controller redirection rules
            // This one is optional as controller name matches the rule
            "example" => array('controller', 'ExampleController'),
        );
    }
}

?>
