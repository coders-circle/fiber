<?php

require_once '../classes/Migration.php';

class ExampleController extends Controller
{
    public function get()
    {
        $version = Migration::check_migration_file("example");
        return new View($this->model, 'example.html');
    }
}

?>
