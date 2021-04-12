--TEST--
Required option set correctly
--ARGS--
-x KEKEKE
--FILE--
<?php

include __DIR__ . '/../lib/Cliff/Cliff.php';
use Cliff\Cliff;

Cliff::run(
    Cliff::config()
    ->option('-x', array(
        'Whatever',
        'is_required' => true,
    ))
);

var_dump($_REQUEST);

?>
--EXPECT--
array(1) {
  ["x"]=>
  string(6) "KEKEKE"
}
