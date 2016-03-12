<?php

require_once 'views/TemplateView.php';
require_once 'Controller.php';
require_once 'utils.php';

class Exception404 extends Exception {
}

class Page
{
    private $view;
    private $controller;
    private $method;
    private $method_name;
    private $arguments;

    public function __construct()
    {
    }

    public function set_template($template_file_name)
    {
        $this->view = new TemplateView($template_file_name);
    }

    public function set_controller($controller)
    {
        $this->controller = $controller;
    }

    public function set_method($method)
    {
        $this->method = strtolower($method);
    }

    public function set_method_name($method_name)
    {
        $this->method_name = $method_name;
    }

    public function set_arguments($arguments)
    {
        $this->arguments = $arguments;
    }

    public function generate()
    {
        if($this->controller)
        {

            if (!$this->method)
                $this->method = "get";
            $action = $this->method;
            if ($this->method_name)
                $action = $action . "_" . $this->method_name;

            if (!method_exists($this->controller, $action)) {
                if ($this->method != "get") {

                    $this->method = "get";
                    $action = $this->method;
                    if ($this->method_name)
                        $action = $action . "_" . $this->method_name;
                }

                if (!method_exists($this->controller, $action)) {
                    throw new Exception404("No method <b>$action</b>" .
                        " exists in controller <b>" .
                        get_class($this->controller) . "</b>");
                }
            }

            if (! $this->arguments )
                $this->view = $this->controller->$action();
            else {
                $this->view = call_user_func_array(array($this->controller, $action), $this->arguments);
            }
        }
        else if (!$this->view)
        {
            $this->set_template('fiber.html');
            // TODO Actually throw exception here
        }
        $this->view->render();
    }
}

 ?>
