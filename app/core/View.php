<?php

require_once 'Template.php';

class View {
    protected $text;

    public function __construct($text) {
        $this->text = $text;
    }

    public function render() {
        echo $this->text;
    }
}

?>
