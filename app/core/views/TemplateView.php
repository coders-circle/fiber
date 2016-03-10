<?php

require_once "app/core/View.php";

class TemplateView extends View {

    public function __construct($template_file_name, $data=array())
    {
        $template = new Template($template_file_name);
        parent::__construct($template->process($data));
    }
}

 ?>
