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

function get_item($array, $key, $default=null) {
    if ($array && key_exists($key, $array))
        return $array[$key];
    return $default;
}

function redirect($url, $status=303) {
    $url = get_url($url);
    header("Location: $url", true, $status);
    die();
}

function split_camel_case($str) {
    return preg_replace('/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]/', ' $0', $str);
}

function split_snake_case($str) {
    return split_camel_case(to_camel_case($str));
}


function slugify($string) {
    return strtolower(trim(preg_replace('~[^\pL\d]+~u', '-', $string)));
}


?>
