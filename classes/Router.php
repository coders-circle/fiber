<?php
require_once 'RouterBase.php';

class Router extends RouterBase
{
    public function __construct()
    {

        $this->routing_rules = array(
            // Default rule
            "default" => "fiber",

            "fiber" => array("template", "fiber.html"),
        );
    }
}

?>
