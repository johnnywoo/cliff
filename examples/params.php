<?php

/**
 * Example config: a script that outputs each of its arguments on a new line
 *
 * Usage: script 1 2 3
 */

require_once __DIR__ . '/../lib/Cliff/Cliff.php';
use \Cliff\Cliff;

Cliff::run(
    Cliff::config()
    ->manyParams('lines', array(
        'Strings to be echoed',
    ))
);

foreach ($_REQUEST['lines'] as $arg) {
    echo "$arg\n";
}
