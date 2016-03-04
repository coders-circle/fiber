<?php

require_once 'Page.php';

// Autoload controller classes
spl_autoload_register('autoLoadClass');

function autoLoadClass($classname)
{
    if (preg_match('/[a-zA-Z]+Controller$/', $classname)) {
        $file = '../controls/' . $classname . '.php';
        if (!file_exists($file)) {
            throw new Exception404("Couldn't load file <b>controls/" . $classname . ".php</b>");
        }

        include $file;
        return true;
    }
    return false;
}


class RouterBase
{
    protected $routing_rules = array();

    private function get_args($php_self, $request_uri)
    {
    	$basepath = implode('/', array_slice(explode('/', $php_self), 0, -1)) . '/';
    	$uri = substr($request_uri, strlen($basepath));
    	if (strstr($uri, '?')) $uri = substr($uri, 0, strpos($uri, '?'));
    	$uri = '/' . trim($uri, '/');
    	return $uri;
    }

    public function route($php_self, $request_uri, $method)
    {
        $args = $this->get_args($php_self, $request_uri);
        $routes = array();
        $temp = explode('/', $args);
        foreach($temp as $route)
        {
        	if(trim($route) != "")
        		array_push($routes, $route);
        }

        $page = new Page();
        $page->set_method($method);

        try {
            if(count($routes) > 0)
            {
                $controller = null;
                if (key_exists($routes[0], $this->routing_rules))
                {
                    $rule = $this->routing_rules[$routes[0]];

                    if ($rule[0] == "template") {
                        $page->set_template($rule[1]);
                    }
                    else if ($rule[0] == "controller") {
                        $controller = new $rule[1]();
                    }
                }
                else {
                    $class_name = to_camel_case($routes[0]) . 'Controller';
                    $controller = new $class_name();
                }

                if ($controller) {
                    $page->set_controller($controller);
                    if (count($routes) > 1)
                    {
                        if (count($routes) > 2)
                            $page->set_arguments(array_slice($routes, 2));
                        $page->set_method_name($routes[1]);
                    }
                }
            }
            else
            {
                if (key_exists("default", $this->routing_rules))
                    header("Location: " .get_url($this->routing_rules["default"]));
                exit();
            }
            $page->generate();
        }
        catch (Exception404 $e) {
            $page->set_controller(null);
            $page->set_template('404.html');
            $page->generate();
        }
    }
}

?>
