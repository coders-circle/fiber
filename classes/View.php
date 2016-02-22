<?php

require_once 'Model.php';
require_once 'Template.php';

class View
{
    private $model;
    private $template;

    public function __construct($model, $template_file_name)
    {
        $this->model = $model;
        $this->template = new Template($template_file_name);
    }

    public function render()
    {
        $this->template->process($this->model);
    }
}

?>
