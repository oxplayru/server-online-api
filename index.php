<?php
$f3 = require('./vendor/bcosca/fatfree-core/base.php');

$f3->route('GET /',
    function($f3) {
        echo 'done';
    }
);
$f3->run();