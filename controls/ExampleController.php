<?php

class ExampleController extends Controller
{
    public function get()
    {
        $data = array(
            "message" => "hello world!"
        );
        return new View('example.html', $data);
    }
}

?>
