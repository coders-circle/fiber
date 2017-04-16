<?php

session_start();

require_once 'Page.php';

class RouterBase
{
    protected $routing_rules = array();

    private function get_path($php_self, $request_uri)
    {
        $basepath = implode('/', array_slice(explode('/', $php_self), 0, -1)) . '/';
        if (strpos($request_uri, $basepath) !== false) {
            $uri = substr($request_uri, strlen($basepath));
        } else {
            $uri = $request_uri;
        }
    	if (strstr($uri, '?'))
            $uri = substr($uri, 0, strpos($uri, '?'));
    	$uri = trim($uri, '/');
    	return $uri;
    }

    public function route($php_self, $request_uri, $method)
    {
        $path = $this->get_path($php_self, $request_uri);
        $routes = array();
        $temp = explode('/', $path);
        foreach($temp as $route)
        {
        	if(trim($route) != "")
        		array_push($routes, $route);
        }

        $page = new Page();
        $page->set_method($method);

        try {
            $controller = null;

            // find rule matching the path
            foreach ($this->routing_rules as $pattern => $rule) {
                $new_pattern = str_replace("/", "\/", $pattern);
                $new_pattern = "/$new_pattern/";
                if (preg_match($new_pattern, $path, $matches)) {

                    if ($rule[0] == "template") {
                        $page->set_template($rule[1]);
                    }
                    else if ($rule[0] == "controller") {
                        $controller_info = explode(":", $rule[1]);
                        $this->load_controller($controller_info[0]);
                        $controller = new $controller_info[0]();
                        $page->set_controller($controller);

                        if (count($controller_info) > 1) {
                            $page->set_method_name($controller_info[1]);
                        }

                        if (count($matches) > 1) {
                            $arguments = array();
                            $i = 1;
                            while (array_key_exists($i, $matches)) {
                                $arguments[] = $matches[$i];
                                $i++;
                            }
                            $page->set_arguments($arguments);
                        }
                    }
                    $page->generate();
                    return;
                }
            }
            throw new Exception404("No rule for path <b>$path</b> is defined");
        }
        catch (Exception404 $e) {
            if (DEBUG) {
                echo "<h1>404 Page Not Found</h1>";
                echo "<div class='container'>";
                echo "<p>Error message: <br>".$e->getMessage()."</p>";
                // echo "<p>Stack trace: <br>".$e->getTraceAsString()."</p>";
                echo "</div>";
                echo "<p> You are seeing this message because <b>DEBUG</b> is";
                echo " enabled in your <b>config.php</b>. Set it to";
                echo " <i>false</i> to see standard 404 page.</p>";
            }
            else {
                $page->set_controller(null);
                $page->set_template('404.html');
                $page->generate();
            }
        }
        catch (Exception $e) {
            if (DEBUG) {
                echo "<h1>500 Server Error</h1>";
                echo "<div class='container'>";
                echo "<p>Error message: <pre>".$e->getMessage()."</pre></p>";
                echo "<p>Stack trace: <pre>".$e->getTraceAsString()."</pre></p>";
                echo "</div>";
                echo "<p> You are seeing this message because <b>DEBUG</b> is";
                echo " enabled in your <b>config.php</b>. Set it to";
                echo " <i>false</i> to see standard 500 page.</p>";
            }
            else {
                $page->set_controller(null);
                $page->set_template('500.html');
                $page->generate();
            }
        }
    }


    private function load_controller($classname) {
        $file = 'app/controllers/' . $classname . '.php';
        if (!file_exists(ROOTDIR."/".$file)) {
            throw new Exception404("Couldn't load file <b>" . $file . "</b>");
        }
        include_once ROOTDIR."/".$file;
    }
}

?>
