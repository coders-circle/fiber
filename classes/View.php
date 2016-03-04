<?php

require_once 'Template.php';

class View
{
    private $data;
    private $template;

    public function __construct($template_file_name, $data=array())
    {
        $this->data = $data;
        $this->template = new Template($template_file_name);
    }

    public function render()
    {
        $this->template->process($this->data);
    }
}

?>
