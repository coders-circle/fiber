<?php

// config.php
// contains global configurations

define("HOST",      "localhost");
define("USER",      "root");
define("PASSWORD",  "noob");
define("DATABASE",  "fiber-db");

define("ROOTDIR", __DIR__);

define("DEBUG", true);

// function to get url from route
function get_url($route)
{
    $baseurl = $_SERVER['PHP_SELF'];
    $baseurl = substr($baseurl, 0, strpos($baseurl, 'index.php')); // str_replace('index.php', '', $baseurl);
    return $baseurl.$route;
}

?>
