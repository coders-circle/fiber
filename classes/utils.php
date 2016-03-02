<?php

function to_camel_case($str) {
    // Split string in words.
    $words = explode('_', strtolower($str));

    $return = '';
    foreach ($words as $word) {
        $return .= ucfirst(trim($word));
    }
    return $return;
}

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

?>
