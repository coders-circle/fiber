<?php

class Router extends RouterBase
{
    public function __construct()
    {
        $this->routing_rules = array(
            "^$" => array("template", "fiber.html"),

            "^example$"
                => array('controller', 'ExampleController'),

            "^example/create_user/(?<username>\w+)/(?<password>\w+)$"
                => array('controller', 'ExampleController:create_user'),

            "^example/test(?:/(?<username>\w+)/(?<password>\w+))?$"
                => array('controller', 'ExampleController:test'),

            "^example/logout$"
                => array('controller', 'ExampleController:logout'),

            "^example/form$"
                => array('controller', 'ExampleController:form'),
        );
    }
}

?>
