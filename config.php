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
    $baseurl = str_replace('index.php', '', $baseurl);
    return $baseurl.$route;
}

?>
