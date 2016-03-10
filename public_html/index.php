<?php
set_include_path("../");

require_once 'app/core/RouterBase.php';
require_once 'app/Router.php';

try {
    $router = new Router();
    $router->route($_SERVER['PHP_SELF'], $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
}
catch (Exception $e) {
    echo "<br><b>Fatal error</b>: " . $e->getMessage() . " in <b>"
        . $e->getFile() . "</b> on line <b>" . $e->getLine() . "</b>";
}

?>
