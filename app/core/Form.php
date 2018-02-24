<?php

class Form {
    public function __construct() {
        $this->obj = null;
        $this->prefix = '';
        $this->suffix = '';
        $this->disabled = false;
        $this->array_id = null;
    }

    public function get_schema() {
        return null;
    }

    public function set_prefix($prefix) {
        $this->prefix = $prefix;
    }

    public function set_suffix($suffix) {
        $this->suffix = $suffix;
    }

    public function set_array_id($id) {
        $this->array_id = $id;
    }

    public function set_disabled($disabled=true) {
        $this->disabled = $disabled;
    }

    public function generate() {
        $form = '';
        // First get schema and generate input elements based on data type
        $schema = $this->get_schema();

        if (!$this->obj) {
            $model_class = $this->get_model_class();
            $this->obj = new $model_class();
        }

        if ($this->obj && $this->obj->id) {
            $form .= '<input name="' . $this->prefix . 'pk' . $this->suffix . '" type="hidden" value="' . $this->obj->id . '" ' . ($this->disabled ? 'disabled' : '') . '>';
        } else {
            $form .= '<input name="' . $this->prefix . 'pk' . $this->suffix . '" type="hidden" value="-1" ' . ($this->disabled ? 'disabled' : '') . '>';
        }

        if ($schema) {
            foreach ($schema as $item) {
                $name = $item[0];
                $type = $item[1];

                $form .= '<div class="field">';

                $label_attrs = ($item['null'] ? '' : 'class="required"');
                $form .= "<label $label_attrs>";
                $form .= split_snake_case($name);
                $form .= "</label>";

                if ($type == 'string') {
                    $form .= '<input type="text" ' . $this->get_form_attrs($item, true) . '>';
                } elseif ($type == 'integer') {
                    $form .= '<input type="number" ' . $this->get_form_attrs($item, true) . '>';
                } elseif ($type == 'float') {
                    $form .= '<input type="number" step="any" ' . $this->get_form_attrs($item, true) . '>';
                } elseif ($type == 'boolean') {
                    $form .= '<input type="checkbox" ' . $this->get_form_attrs($item, true) . '>';
                } elseif ($type == 'datetime') {
                    $form .= '<input type="datetime-local" ' . $this->get_form_attrs($item, true) . '>';
                } elseif ($type == 'time') {
                    $form .= '<input type="time" ' . $this->get_form_attrs($item, true) . '>';
                } elseif ($type == 'password') {
                    $form .= '<input type="password" ' . $this->get_form_attrs($item, true) . '>';
                } elseif ($type == 'text') {
                    $form .= '<textarea ' . $this->get_form_attrs($item) . '>' . $this->obj->$name . '</textarea>';
                } elseif ($type == 'file') {   // make sure  enctype="multipart/form-data"
                    $was_null = (bool)$item['null'];
                    if ($this->obj->$name) {
                        $item['null'] = true;
                    }
                    $form .= '<input type="file" ' . $this->get_form_attrs($item, false) . '>';
                    $item['null'] = $was_null;
                    if ($this->obj->$name) {
                        $form .= 'Current: <a href="/' . $this->obj->$name . '">' . $this->obj->$name . '</a>';

                        if ($item['null']) {
                            $form .= '<input type="checkbox" name="remove_' . $this->prefix . $name . $this->suffix . '"> Remove';
                        }
                    }
                } elseif ($type == 'children') {
                    $form .= $this->generate_children_form($item);
                } elseif ($type == 'foreign') {
                    $form .= $this->generate_foreign($item);
                } elseif ($type == 'child') {
                    $form .= $this->generate_child($item);
                }

                $form .= '</div>';
            }
        }
        return $form;
    }

    private function get_form_attrs($item, $add_value=false) {
        $attrs = '';
        $name = $item[0];
        $type = $item[1];

        $id = str_replace('_', '-', $name);
        $attrs .= 'id="' . $id . '" ';
        $attrs .= 'name="' . $this->prefix . $name . $this->suffix . '" ';

        if ($item['max_length']) {
            $attrs .= 'maxlength="' . $item['max_length'] . '" ';
        }

        if (!$item['null']) {
            $attrs .= 'required ';
        }

        if ($add_value) {
            $attrs .= 'value="' . $this->obj->$name . '" ';
        }

        if ($this->disabled) {
            $attrs .= 'disabled ';
        }

        if ($item['custom_class']) {
            $attrs .= ' class="' . $item['custom_class'] . '"';
        }

        return $attrs;
    }

    private function generate_foreign($item) {
        $name = $item[0];
        $type = $item[1];

        $select = '<select ' . $this->get_form_attrs($item) . '>';
        $foreign_items = $item['model']::get_all();

        if ($item['null']) {
            $select .= '<option value="">-------</option>';
        }

        $i = 0;
        $name_field = $item['name_field'];
        foreach ($foreign_items as $foreign_item) {
            if ($name_field) {
                $option_text = $foreign_item->$name_field;
            } else {
                $option_text = $item['model'] . ' ' . (++$i);
            }
            $select .= '<option value="' . $foreign_item->id . '" ' . ($this->obj->$name == $foreign_item->id ? 'selected' : '') . '>' . $option_text . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    private function generate_child($item) {
        $name = $item[0];
        $type = $item[1];

        $id = $this->prefix . str_replace('_', '-', $item[0]);
        $container = '<div class="child-container ' . ($item['custom_class'] ? $item['custom_class'] : '') . '" id="' . $id . '">';

        $form = new $item['form']();

        $form->set_prefix($this->prefix . $name . '_');
        if ($this->obj->$name) {
            $model_class = $form->get_model_class();
            $form->set_object($model_class::query()->where('id=?', $this->obj->$name)->first());
        }
        $container .= $form->generate();

        $container .= '</div>';
        return $container;
    }

    private function generate_children_form($item) {
        $name = $item[0];
        $type = $item[1];

        $id = $this->prefix . str_replace('_', '-', $item[0]);
        $container = '<div class="children-container ' . ($item['custom_class'] ? $item['custom_class'] : '') . '" id="' . $id . '">';

        $form = new $item['form']();
        $form->set_prefix($this->prefix . $name . '_');
        $form->set_suffix('[]');

        $form->set_disabled();
        $container .= '<div class="child-template" hidden>' . $form->generate() .  '<a class="delete-child">Delete</a></div>';

        // Get existing children and fill them in
        if ($this->obj && $this->obj->id) {
            $model_class = $form->get_model_class();
            $foreign_key = ($item['foreign_key']) ? $item['foreign_key'] : to_snake_case($this->get_model_class());
            $children = $model_class::query()->where($foreign_key . '=?', $this->obj->id)->get();

            $form->set_disabled(false);
            foreach ($children as $child) {
                $form->set_object($child);
                $container .= '<div class="child">' . $form->generate() .  '<a class="delete-child" onclick="this.parentNode.parentNode.removeChild(this.parentNode);">Delete</a></div>';
            }
        }

        $singular_name = $this->prefix . $item['singular'];
        $container .= '<a class="add-child" onclick="add_' . $singular_name . '(this)">Add ' . split_snake_case($item['singular']) .'</a>';
        $container .= <<<SCRIPT
        <script>
            function add_$singular_name(btn) {
                let container = document.querySelector('#$id');

                let item = document.querySelector('#$id .child-template').cloneNode(true);
                item.classList.remove('child-template');
                item.classList.add('child');
                item.removeAttribute('hidden');

                let disabled = item.querySelectorAll('[disabled=""]');
                for (let i=0; i<disabled.length; i++) {
                    disabled[i].removeAttribute('disabled');
                }

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

    public function set_object($obj) {
        $this->obj = $obj;
    }

    private function get_post($name) {
        if ($this->array_id !== null) {
            return $_POST[$name][$this->array_id];
        } else {
            return $_POST[$name];
        }
    }

    private function get_file($name) {
        if ($this->array_id !== null) {
            $tmp = [];
            foreach ($_FILES[$name] as $key => $value) {
                $tmp[$key] = $value[$this->array_id];
            }
            return $tmp;
        } else {
            return $_FILES[$name];
        }
    }

    public function post() {
        if (!$this->obj) {
            $model_class = $this->get_model_class();
            $this->obj = new $model_class();
        }

        $schema = $this->get_schema();
        if ($schema) {
            foreach ($schema as $item) {
                $name = $item[0];
                $type = $item[1];

                $post_name = $this->prefix . $name;

                if (in_array($type, ['string', 'text', 'integer', 'datetime', 'boolean', 'foreign'])) {
                    if ($item['null'] && $this->get_post($post_name) == '') {
                        $this->obj->$name = null;
                    } else {
                        $this->obj->$name = $this->get_post($post_name);
                    }
                }
                elseif ($type == 'time') {
                    if ($item['null'] && $this->get_post($post_name) == '') {
                        $this->obj->$name = null;
                    } else {
                        $this->obj->$name = strtotime($this->get_post($post_name));
                    }
                }
                elseif ($type == 'file') {
                    $file = $this->get_file($post_name);
                    $remove = $this->get_post('remove_' . $post_name);

                    if ($remove) {
                        // Delete existing if any file exists
                        if ($this->obj->$name && file_exists(ROOTDIR . '/public_html/' . $this->obj->$name)) {
                            unlink(ROOTDIR . '/public_html/' . $this->obj->$name);
                        }
                        $this->obj->$name = null;
                    }

                    if($file && !$file['error']) {

                        // Delete existing if any file exists
                        if ($this->obj->$name && file_exists(ROOTDIR . '/public_html/' . $this->obj->$name)) {
                            unlink(ROOTDIR . '/public_html/' . $this->obj->$name);
                        }

                        // Create path
                        $path = ROOTDIR . '/public_html/' . $item['dir'];
                        mkdir($path, 0777, true);

                        //  Generate valid file name
                        $i = 1;
                        $filename =  $item['dir'] . '/' . $file['name'];
                        $original = $filename;
                        while (file_exists(ROOTDIR . '/public_html/' . $filename)) {
                            $filename = $original . '_' . $i;
                        }
                        // Copy file to upload
                        move_uploaded_file($file['tmp_name'], ROOTDIR . '/public_html/' . $filename);

                        // Set path
                        $this->obj->$name = $filename;
                    } else {
                        // ERROR
                    }
                }
                elseif ($type == 'child') {
                    $form = new $item['form']();
                    $form->set_prefix($this->prefix . $name . '_');
                    $model_class = $form->get_model_class();
                    // If exists, override it, otherwise create new
                    if ($this->obj->$name) {
                        $form->set_object($model_class::query()->where('id=?', $this->obj->$name)->first());
                    }
                    $new = $form->post();
                    // Then set id
                    $this->obj->$name = $new->id;
                }
            }
        }
        $this->obj->save();

        // For type children, first we save the model and change foreign key of children models
        if ($this->obj->id && $schema) {
            foreach ($schema as $item) {
                $name = $item[0];
                $type = $item[1];

                if ($type == 'children') {
                    $foreign_key = ($item['foreign_key']) ? $item['foreign_key'] : to_snake_case($this->get_model_class());
                    $form = new $item['form']();
                    $form->set_prefix($this->prefix . $name . '_');
                    $model_class = $form->get_model_class();

                    $new_pks = [];
                    for ($i=0; $i<count($_POST[$this->prefix . $name . '_pk']); $i++) {
                        $form->set_array_id($i);
                        $pk = (int)$_POST[$this->prefix . $name . '_pk'][$i];

                        $new_obj = null;
                        if ($pk >= 0) {
                            $new_obj = $model_class::query()->where('id=?', $pk)->first();
                        } else {
                            $new_obj = new $model_class();
                        }

                        $new_obj->$foreign_key = $this->obj->id;
                        $form->set_object($new_obj);
                        $new = $form->post();

                        $new_pks[] = $new->id;
                    }

                    // Delete all children that wasn't added/updated this time
                    $new_pks = join("', '", $new_pks);
                    $model_class::query()->where("$foreign_key=? AND id NOT IN ('$new_pks')", $this->obj->id)->delete();
                }
            }
        }

        return $this->obj;
    }
}

 ?>
