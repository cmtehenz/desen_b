<?php
    spl_autoload_register(function ($class) {
        $name = $_SERVER['DOCUMENT_ROOT'] . "\\" . $class . '.class.php';

        require_once(str_replace('\\', '/', $name));
    });
?>