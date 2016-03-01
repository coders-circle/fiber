<?php

class ExampleController extends Controller
{
    public function get()
    {
        return new View($this->model, 'example.html');
    }
}

?>
