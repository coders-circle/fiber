<?php

require_once 'Query.php';

function to_snake_case($input) {
    preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
    $ret = $matches[0];
    foreach ($ret as &$match) {
        $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
    }
    return implode('_', $ret);
}

function get_sql_type($type, $max_length=null) {
    switch (strtolower($type)) {
        case 'integer':
            if (!$max_length)
                $max_length = 11;
            return "INT($max_length)";
        case 'string':
            if (!$max_length)
                $max_length = 30;
            return "VARCHAR($max_length)";
        case 'boolean':
            return "BOOL";
        default:
            return null;
    }
}

function get_item($array, $key, $default=null) {
    if ($array && key_exists($key, $array))
        return $array[$key];
    return $default;
}

class ModelObject
{
    public function get_schema() {
        return null;
    }

    public static function get_table_name() {
        return to_snake_case(get_called_class());
    }

    public static function exists() {
        $db = Database::get_instance();
        $res = $db->query_with_error("SHOW TABLES LIKE '" . self::get_table_name() . "'");
        return $res->num_rows > 0;
    }

    public function create_table() {
        if ($this->exists())
            return;

        $sql = "CREATE TABLE IF NOT EXISTS " . $this->get_table_name() . " (";

        $primary_key = "id";
        $sql .= " $primary_key INT AUTO_INCREMENT PRIMARY KEY";

        $schema = $this->get_schema();

        // Either use schema
        // and if isn't defined, use properties defined
        // for this object to deduce field names and types.
        if ($schema) {
            foreach ($schema as $item) {
                $name = $item[0];
                $len = get_item($item, "max_length", null);
                $type = get_sql_type($item[1], $len);

                if ($type && $name) {
                    $sql .= ", $name $type NOT NULL";
                }
            }
        } else {
            $reflect = new ReflectionObject($this);
            $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
            foreach ($props as $prop) {
                $name = $prop->getName();
                $val = $prop->getValue($this);
                $type = get_sql_type(gettype($val));

                if ($type && $name) {
                    $sql .= ", $name $type NOT NULL";
                }
            }
        }

        $sql .= ")";

        Database::get_instance()->query_with_error($sql);
    }

    public function get_schema_fields() {
        $schema = $this->get_schema();
        if ($schema) {
            $fields = array('id');
            foreach ($schema as $item) {
                $fields[] = $item[0];
            }
            return $fields;
        }
        return null;
    }

    public function save() {
        $this->create_table();

        $keys = "";
        $values = "";
        $update_string = "";

        $reflect = new ReflectionObject($this);
        $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
        $fields = $this->get_schema_fields();

        $db = Database::get_instance();

        foreach ($props as $prop) {
            $name = $prop->getName();
            if ($fields && !in_array($name, $fields))
                continue;

            $val = $db->real_escape_string($prop->getValue($this));

            if ($keys!="")
                $keys .= ",";
            $keys .= "$name";

            if ($values!="")
                $values .= ",";
            $values .= "'$val'";

            if ($update_string!="")
                $update_string .= ",";
            $update_string .= "$name='$val'";
        }

        $sql = "INSERT INTO " . $this->get_table_name() . " ($keys) VALUES($values) "
            . " ON DUPLICATE KEY UPDATE $update_string";

        $db->query_with_error($sql);
    }

    public static function get_from_query_result($result) {
        $objects = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $class = get_called_class();
                $obj = new $class();

                foreach ($obj as $key=>$val) {
                    unset($obj->$key);
                }

                foreach ($row as $key=>$val) {
                    $obj->$key = $val;
                }

                $objects[] = $obj;
            }
        }
        return $objects;
    }

    public static function get_all() {
        $db = Database::get_instance();
        $table = self::get_table_name();
        $sql = "SELECT * FROM $table";
        $result = $db->query_with_error($sql);
        return self::get_from_query_result($result);
    }

    public static function raw_query($sql) {
        $db = Database::get_instance();
        $result = $db->query_with_error($sql);
        return self::get_from_query_result($result);
    }

    public static function get_db() {
        return Database::get_instance();
    }

    public static function query() {
        return new Query(get_called_class());
    }
}

?>
