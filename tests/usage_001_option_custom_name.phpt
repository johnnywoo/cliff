--TEST--
Custom name for an option
--ARGS--
-x 'Homer Simpson'
--FILE--
<?php

include __DIR__ . '/../lib/Cliff/Cliff.php';
use Cliff\Cliff;

Cliff::run(
    Cliff::config()
    ->option('-x', array(
        'Whatever',
        'name' => 'mister_x',
    ))
);

var_dump($_REQUEST);

?>
--EXPECT--
array(1) {
  ["mister_x"]=>
  string(13) "Homer Simpson"
}
