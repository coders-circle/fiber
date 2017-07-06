<?php

require_once "app/core/View.php";

class JsonView extends View {

    public function __construct($data=array())
    {
        header('Content-Type: application/json');
        parent::__construct(json_encode($data));
    }
}

 ?>
