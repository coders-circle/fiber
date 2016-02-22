<?php

require_once 'Model.php';

class Controller
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function get()
    {
    }
}

?>
