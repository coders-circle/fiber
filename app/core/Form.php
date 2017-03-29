<?php

class Form {
    public function get_model() {
        return null;
    }

    public function generate() {
        $form = '';
        // First get schema and generate input elements based on data type
        $schema = $this->get_schema();
        if ($schema) {
            foreach ($schema as $item) {
                $name = $item[0];
                $type = $item[1];

                $form .= '<div class="field">';

                $form .= "<label>";
                $form .= split_snake_case($name);
                $form .= "</label>";

                if ($type == 'string') {
                    $form .= '<input ' . $this->get_form_attrs($item) . '>';
                } else if ($type == 'integer') {
                    $form .= '<input type="number" ' . $this->get_form_attrs($item) . '>';
                } else if ($type == 'boolean') {
                    $form .= '<input type="checkbox" ' . $this->get_form_attrs($item) . '>';
                } else if ($type == 'datetime') {
                    $form .= '<input type="datetime-local" ' . $this->get_form_attrs($item) . '>';
                } else if ($type == 'password') {
                    $form .= '<input type="password" ' . $this->get_form_attrs($item) . '>';
                } else if ($type == 'text') {
                    $form .= '<textarea ' . $this->get_form_attrs($item) . '></textarea>';
                } else if ($type == 'children') {
                    $form .= $this->generate_children_form($item);
                }

                $form .= '</div>';
            }
        }
        return $form;
    }

    private function get_form_attrs($item) {
        $attrs = '';
        $name = $item[0];
        $type = $item[1];

        $id = str_replace('_', '-', $name);
        $attrs .= 'id="' . $id . '" ';
        $attrs .= 'name="' . $name . '" ';
        return $attrs;
    }

    private function generate_children_form($item) {
        $id = str_replace('_', '-', $item[0]);
        $container = '<div class="children-container" id="' . $id . '">';

        $container .= '<div class="child-template" hidden>' . $item[3]->generate() .  '<a href="#" class="delete-child">Delete</a></div>';
        $container .= '<a href="#" class="add-child" onclick="add_' . $item[2] . '(this)">Add ' . split_snake_case($item[2]) .'</a>';
        $container .= <<<SCRIPT
        <script>
            function add_$item[2](btn) {
                let container = document.querySelector('#$id');

                let item = document.querySelector('#$id .child-template').cloneNode(true);
                item.classList.remove('child-template');
                item.classList.add('child');
                item.removeAttribute('hidden');

                container.insertBefore(item, btn);
                item.querySelector('.delete-child').onclick = function() {
                    container.removeChild(item);
                };
            }
        </script>
SCRIPT;

        $container .= '</div>';
        return $container;
    }
}

 ?>
